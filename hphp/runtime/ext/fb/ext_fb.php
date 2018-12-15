<?hh
// generated by idl-to-hni.php

namespace {
/* Serialize data into a compact format that can be unserialized by
 * fb_unserialize().
 * @param mixed $thing - What to serialize. Note that objects are not
 * supported.
 * @param options bitmask of options: FB_SERIALIZE_HACK_ARRAYS.
 * @return mixed - Serialized data.
 */
<<__HipHopSpecific, __Native, __IsFoldable, __Rx>>
function fb_serialize(mixed $thing, int $options = 0): mixed;

/* Unserialize previously fb_serialize()-ed data.
 * @param mixed $thing - What to unserialize.
 * @param mixed $success - Whether it was successful or not.
 * @param options bitmask of options: FB_SERIALIZE_HACK_ARRAYS.
 * @return mixed - Unserialized data.
 */
<<__HipHopSpecific, __Native>>
function fb_unserialize(mixed $thing,
                        mixed &$success,
                        int $options = 0): mixed;

/* Serialize data into a compact format that can be unserialized by
 * fb_compact_unserialize(). In general produces smaller output compared to
 * fb_serialize(). Largest savings are on arrays with sequential (or almost
 * sequential) indexes, i.e. simple arrays like array($a, $b, $c). NOTE:
 * unlike serialize(), does not preserve internal references, i.e. array(&$a,
 * &$a) will become array($a, $a).
 * @param mixed $thing - What to serialize. Note that objects are not
 * supported.
 * @return mixed - Serialized data.
 */
<<__HipHopSpecific, __Native, __IsFoldable, __Rx>>
function fb_compact_serialize(mixed $thing): mixed;

/* Unserialize a previously fb_compact_serialize()-ed data.
 * @param mixed $thing - What to unserialize.
 * @param mixed $success - Whether it was successful or not.
 * @param mixed $errcode - One of those FB_UNSERIALIZE_ constants to describe
 * what the decoding error was, if it failed.
 * @return mixed - Unserialized data.
 */
<<__HipHopSpecific, __Native>>
function fb_compact_unserialize(mixed $thing,
                                mixed &$success,
                                mixed &$errcode = null): mixed;

/* Invokes a user handler upon calling a function or a class method. If this
 * handler returns FALSE, code will continue with original function.
 * Otherwise, it will return what handler tells. The handler function looks
 * like "intercept_handler($name, $obj, $params, $data, &$done)", where $name
 * is original function's fully-qualified name ('Class::method'), $obj is $this
 * for an instance method call or null for static method call or function calls,
 * and $params are original call's parameters. $data is what's passed to
 * fb_intercept() and set $done to false to indicate function should continue its
 * execution with old function as if interception did not happen. By default $done
 * is true so it will return handler's return immediately without executing old
 * function's code. Note that built-in functions are not interceptable.
 * @param string $name - The function or class method name to intercept. Use
 * "class::method" for method name. If empty, all functions will be
 * intercepted by the specified handler and registered individual handlers
 * will be replaced. To make sure individual handlers not affected by such a
 * call, call fb_intercept() with individual names afterwards.
 * @param mixed $handler - Callback to handle the interception. Use null,
 * false or empty string to unregister a previously registered handler. If
 * name is empty, all previously registered handlers, including those that are
 * set by individual function names, will be removed.
 * @param mixed $data - Extra data to pass to the handler when intercepting
 * @return bool - TRUE if successful, FALSE otherwise
 */
<<__HipHopSpecific, __Native>>
function fb_intercept(string $name,
                      mixed $handler,
                      mixed $data = null): bool;

/* Rename a function, so that a function can be called with the new name.
 * When writing unit tests, one may want to stub out a function. To do so,
 * call fb_rename_function('func_to_stub_out', 'somename') then
 * fb_rename_function('new_func_to_replace_with', 'func_to_stub_out'). This
 * way, when calling func_to_stub_out(), it will actually execute
 * new_func_to_replace_with().
 * @param string $orig_func_name - Which function to rename.
 * @param string $new_func_name - What is the new name.
 * @return bool - TRUE if successful, FALSE otherwise.
 */
<<__HipHopSpecific, __Native("NoFCallBuiltin")>>
function fb_rename_function(string $orig_func_name,
                            string $new_func_name): bool;

/* Sanitize a string to make sure it's legal UTF-8 by stripping off any
 * characters that are not properly encoded.
 * @param mixed $input - What string to sanitize.
 * @return bool - Sanitized string.
 */
<<__HipHopSpecific, __Native, __IsFoldable, __Rx>>
function fb_utf8ize(mixed &$input): bool;

/* Count the number of UTF-8 code points in string or byte count if it's not
 * valid UTF-8.
 * @param string $input - The string.
 * @return int - Returns the count of code points if valid UTF-8 else byte
 * count.
 */
<<__HipHopSpecific, __Native, __IsFoldable, __Rx>>
function fb_utf8_strlen_deprecated(string $input): int;

/* Count the number of UTF-8 code points in string, substituting U+FFFD for
 * invalid sequences.
 * @param string $input - The string.
 * @return int - Returns the number of code points interpreting string as
 * UTF-8.
 */
<<__HipHopSpecific, __Native, __IsFoldable, __Rx>>
function fb_utf8_strlen(string $input): int;

/* Cuts a portion of str specified by the start and length parameters.
 * @param string $str - The original string.
 * @param int $start - If start is non-negative, fb_utf8_substr() cuts the
 * portion out of str beginning at start'th character, counting from zero.  If
 * start is negative, fb_utf8_substr() cuts out the portion beginning at the
 * position, start characters away from the end of str.
 * @param int $length - If length is given and is positive, the return value
 * will contain at most length characters of the portion that begins at start
 * (depending on the length of string).  If negative length is passed,
 * fb_utf8_substr() cuts the portion out of str from the start'th character up
 * to the character that is length characters away from the end of the string.
 * In case start is also negative, the start position is calculated beforehand
 * according to the rule explained above.
 * @return string - Returns the portion of str specified by the start and
 * length parameters.  If str is shorter than start characters long, the empty
 * string will be returned.
 */
<<__HipHopSpecific, __Native, __IsFoldable, __Rx>>
function fb_utf8_substr(string $str,
                        int $start,
                        int $length = PHP_INT_MAX): string;

/* Returns code coverage data collected so far. Turn on code coverage by
 * Eval.RecordCodeCoverage or by using fb_enable_code_coverage and call this
 * function periodically to get results. Eval.CodeCoverageOutputFile allows
 * you to specify an output file to store results at end of a script run from
 * command line. Use this function in server mode to collect results instead.
 * @param bool $flush - Whether to clear data after this function call.
 * @return darray<string,mixed>|false
 */
<<__HipHopSpecific, __Native>>
function fb_get_code_coverage(bool $flush): mixed;

/* Enables code coverage. The coverage information is cleared.
 */
<<__HipHopSpecific, __Native("NoFCallBuiltin")>>
function fb_enable_code_coverage(): void;

/* Disables and returns code coverage. The coverage information is cleared.
 */
<<__HipHopSpecific, __Native("NoFCallBuiltin")>>
function fb_disable_code_coverage(): darray<string, mixed>;

/* Toggles the compression status of HipHop output, if headers have already
 * been sent this may be ignored.
 * @param bool $new_value - The new value for the compression state.
 * @return bool - The old value.
 */
<<__HipHopSpecific, __Native>>
function fb_output_compression(bool $new_value): bool;

/* Set a callback function that is called when php tries to exit.
 * @param mixed $function - The callback to invoke. An exception object will
 * be passed to the function
 */
<<__HipHopSpecific, __Native>>
function fb_set_exit_callback(mixed $function): void;

/* Get stats on flushing the last data chunk from server.
 * @return int - Total number of bytes flushed since last flush
 */
<<__HipHopSpecific, __Native>>
function fb_get_last_flush_size(): int;

/* Gathers the statistics of the file named by filename, like lstat(), except
 * uses cached information from an internal inotify-based mechanism that may
 * not be updated during the duration of a request.
 * @param string $filename - Path to a file or a symbolic link.
 * @return mixed - Same format as the normal php lstat() function.
 */
<<__Native>>
function fb_lazy_lstat(string $filename): mixed;

/* Returns a canonicalized version of the input path that contains no symbolic
 * links, like realpath(), except uses cached information from an internal
 * inotify-based mechanism that may not be updated during the duration of a
 * request.
 * @param string $filename - Fake path to the file.
 * @return string - Real path of the file.
 */
<<__Native>>
function fb_lazy_realpath(string $filename): mixed;

} // namespace

namespace HH {
/* Disables and returns code coverage that contains file to coverage frequency.
 * The coverage information is cleared.
 */
<<__HipHopSpecific, __Native("NoFCallBuiltin")>>
function disable_code_coverage_with_frequency(): darray<string, mixed>;

/* Returns an int for the upper (first) 64 bits of an md5 hash of a string.
 * The MD5 hash, usually presented as a hex value, is taken as big endian, and
 * this int result is the signed counterpart to the unsigned upper 64 bits.
 *
 * This function and the _lower version are generally only intended for
 * legacy use cases in which an MD5 hash is used to compute a number
 * of 64 bits or less. These functions are faster and prettier than calling
 * unpack+substr+md5/raw or hexdec+substr+md5. Note that hexdec converts
 * to floating point (information loss) for some 64-bit unsigned values.
 *
 * The faster and quite effective xxhash64 is generally recommended for
 * non-crypto hashing needs when no backward compatibility is needed.
 */
<<__Native,__IsFoldable>>
function non_crypto_md5_upper(string $str): int;

/* Returns an int for the lower (last) 64 bits of an md5 hash of a string.
 * The MD5 hash, usually presented as a hex value, is taken as big endian, and
 * this int result is the signed counterpart to the unsigned lower 64 bits.
 *
 * This function and the _upper version are generally only intended for
 * legacy use cases in which an MD5 hash is used to compute a number
 * of 64 bits or less. These functions are faster and prettier than calling
 * unpack+substr+md5/raw or hexdec+substr+md5. Note that hexdec converts
 * to floating point (information loss) for some 64-bit unsigned values.
 *
 * The faster and quite effective xxhash64 is generally recommended for
 * non-crypto hashing needs when no backward compatibility is needed.
 */
<<__Native,__IsFoldable>>
function non_crypto_md5_lower(string $str): int;
} // HH namespace
