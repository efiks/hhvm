(*
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the "hack" directory of this source tree.
 *
 *)

module Hack_bucket = Bucket
open Hh_prelude
open Hh_json
open Aast
open Typing_symbol_json_builder
module Bucket = Hack_bucket

let get_decls (ctx : Provider_context.t) (tast : Tast.program list) :
    symbol_occurrences =
  let (all_decls, all_defs) =
    List.fold tast ~init:([], []) ~f:(fun (decl_acc, def_acc) prog ->
        List.fold prog ~init:(decl_acc, def_acc) ~f:(fun (decls, defs) def ->
            match def with
            | Class _
            | Typedef _
            | Constant _ ->
              (def :: decls, def :: defs)
            | Fun _ -> (def :: decls, def :: defs)
            | _ -> (decls, def :: defs)))
  in
  let symbols = IdentifySymbolService.all_symbols ctx all_defs in
  { decls = all_decls; occurrences = symbols }

let write_json
    (ctx : Provider_context.t)
    (file_dir : string)
    (tast_lst : Tast.program list) : unit =
  try
    let symbol_occurrences = get_decls ctx tast_lst in
    let json_chunks =
      Typing_symbol_json_builder.build_json ctx symbol_occurrences
    in
    let (out_file, channel) =
      Filename.open_temp_file
        ~temp_dir:file_dir
        "glean_symbol_info_chunk_"
        ".json"
    in
    let json = JSON_Array json_chunks in
    Out_channel.output_string channel (json_to_string ~pretty:true json);
    Out_channel.close channel;
    Hh_logger.log "Wrote symbol info to: %s" out_file
  with e ->
    Printf.eprintf "WARNING: symbol write failure: \n%s" (Exn.to_string e)

let recheck_job
    (ctx : Provider_context.t)
    (out_dir : string)
    (root_path : string)
    (hhi_path : string)
    ()
    (progress : Relative_path.t list) : unit =
  let tasts =
    List.map progress ~f:(fun path ->
        let (ctx, entry) = Provider_context.add_entry ~ctx ~path in
        let { Tast_provider.Compute_tast.tast; _ } =
          Tast_provider.compute_tast_unquarantined ~ctx ~entry
        in
        tast)
  in
  Relative_path.set_path_prefix Relative_path.Root (Path.make root_path);
  Relative_path.set_path_prefix Relative_path.Hhi (Path.make hhi_path);
  write_json ctx out_dir tasts

let go
    (workers : MultiWorker.worker list option)
    (ctx : Provider_context.t)
    (out_dir : string)
    (root_path : string)
    (hhi_path : string)
    (file_tuples : Relative_path.t list) : unit =
  let num_workers =
    match workers with
    | Some w -> List.length w
    | None -> 1
  in
  MultiWorker.call
    workers
    ~job:(recheck_job ctx out_dir root_path hhi_path)
    ~merge:(fun () () -> ())
    ~next:(Bucket.make ~num_workers ~max_size:150 file_tuples)
    ~neutral:()
