/**
 * Set up autocomplete on the server path list
 *
 */
$("document").ready(function() {
  $("#g-path").autocomplete(
    base_url.replace("__ARGS__", "admin/server_add/autocomplete"), {max: 256});
});
