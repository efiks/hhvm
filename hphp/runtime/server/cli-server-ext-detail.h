/*
   +----------------------------------------------------------------------+
   | HipHop for PHP                                                       |
   +----------------------------------------------------------------------+
   | Copyright (c) 2010-present Facebook, Inc. (http://www.facebook.com)  |
   +----------------------------------------------------------------------+
   | This source file is subject to version 3.01 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available through the world-wide-web at the following url:           |
   | http://www.php.net/license/3_01.txt                                  |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
*/

#ifndef incl_HPHP_CLI_SERVER_EXT_DETAIL_H_
#define incl_HPHP_CLI_SERVER_EXT_DETAIL_H_

#include "hphp/runtime/base/exceptions.h"
#include "hphp/runtime/server/cli-server.h"
#include "hphp/runtime/server/cli-server-impl.h"

#include <folly/functional/ApplyTuple.h>
#include <tuple>

namespace HPHP {
namespace detail {

struct CLIServerExtensionInterface {
  ~CLIServerExtensionInterface();
protected:
  CLIServerExtensionInterface(int afdt_fd);

  CLIServerExtensionInterface(const CLIServerExtensionInterface&) = delete;
  CLIServerExtensionInterface& operator=(const CLIServerExtensionInterface&) = delete;

  int afdt_fd;

  template<typename T>
  void read(T& arg) {
    cli_read(afdt_fd, arg);
  }
  void read(FdData& arg) {
    arg.fd = cli_read_fd(afdt_fd);
  }

  template<typename T>
  void write(const T& arg) {
    cli_write(afdt_fd,arg);
  }
  void write(const FdData& arg) {
    cli_write_fd(afdt_fd, arg.fd);
  }

  template <class T, class... Ts>
  void writeMulti(const T& first, const Ts&... rest) {
    write(first);
    writeMulti(rest...);
  }

  template <class T, class... Ts>
  void readMulti(T& first, Ts&... rest) {
    read(first);
    readMulti(rest...);
  }

  void assertCount(int count);

  enum class State {
    INIT,
    DONE_ARGUMENTS,
    DONE_RESULT,
  };

#ifndef NDEBUG
  void advanceState(State oldState, State newState) {
    if (state != oldState) {
      throw Exception(
        "Invalid state - expected %d, got %d",
        (int) oldState,
        (int) state
      );
    }
    state = newState;
  }
#else
  void advanceState(State oldState, State newState) {}
#endif

private:
  // no-op recursion base cases
  void readMulti() {}
  void writeMulti() {}

#ifndef NDEBUG
  State state;
#endif
};

struct CLIServerInterface : public CLIServerExtensionInterface {
  CLIServerInterface(int afdt_fd) : CLIServerExtensionInterface(afdt_fd) {}

  template <class T, class... Ts>
  void readArguments(T& first, Ts&... rest) {
    advanceState(State::INIT, State::DONE_ARGUMENTS);
    assertCount(sizeof...(rest) + 1);
    readMulti(first, rest...);
  }

  template <class TSuccess, class TError>
  void sendResult(CLISrvResult<TSuccess, TError> result) {
    advanceState(State::DONE_ARGUMENTS, State::DONE_RESULT);
    write(result.succeeded());
    if (result.succeeded()) {
      write(result.result());
      return;
    }
    write(result.error());
  }
};

struct CLIClientInterface : public CLIServerExtensionInterface {
  // defined in cli-server.cpp so that tl_cliSock does not need to be exposed
  CLIClientInterface(const std::string& id);

  template<class... Args>
  void invoke(Args&&... args) {
    advanceState(State::INIT, State::DONE_ARGUMENTS);
    writeInvokeHeader(sizeof...(args));
    writeMulti(args...);
  }

  template<class TSuccess, class TError>
  CLISrvResult<TSuccess, TError> getResult() {
    advanceState(State::DONE_ARGUMENTS, State::DONE_RESULT);
    readResultHeader();
    bool success;
    read(success);
    if (success) {
      TSuccess ret;
      read(ret);
      return { CLISuccess {}, ret };
    }

    TError ret;
    read(ret);
    return { CLIError {}, ret };
  }
private:
  const std::string id;

  void readResultHeader();
  void writeInvokeHeader(int count);
};

void cli_register_handler(const std::string& id, void(*impl)(CLIServerInterface&));

template<class TRet, class...Args>
std::string cli_get_extension_function_id(const char* name, TRet(*impl)(Args...)) {
  return std::string(name) + "/" + typeid(impl).name();
}

template<class TSuccess, class TError, class... Args>
void cli_server_invoke_wrapped(CLIServerInterface& server, CLISrvResult<TSuccess, TError>(*func)(Args...)) {
  std::tuple<Args...> args;
  folly::apply([&] (Args&... tupleArgs) { server.readArguments(tupleArgs...); }, args);
  server.sendResult(folly::apply(func, args));
}

template<class TSuccess, class TError, class... Args>
CLISrvResult<TSuccess, TError> invoke_on_cli_client(const std::string& id, CLISrvResult<TSuccess, TError>(*func)(Args...), Args... args) {
  if (is_cli_server_mode()) {
    CLIClientInterface client(id);
    client.invoke(args...);
    return client.getResult<TSuccess, TError>();
  }
  return func(args...);
}

} // namespace HPHP::detail
} // namespace HPHP

#endif
