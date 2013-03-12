$("#g-add-album-form input[name='title']").on("input keyup", function() {
  $("#g-add-album-form input[name='name']").val(
    $(this).val().replace(/[\s\/\\]+/g, "-").replace(/\.+$/, ""));
  $("#g-add-album-form input[name='slug']").val(
    $(this).val().replace(/[^A-Za-z0-9-_]+/g, "-").replace(/^-+/, "").replace(/-+$/, ""));
});
