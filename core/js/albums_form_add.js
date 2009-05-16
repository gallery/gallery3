$("#gAddAlbumForm input[name=title]").change(
  function() {
    $("#gAddAlbumForm input[name=name]").attr(
      "value", $("#gAddAlbumForm input[name=title]").attr("value").
        replace(/\s+/g, "_").replace(/\.+$/, ""));
  });
$("#gAddAlbumForm input[name=title]").keyup(
  function() {
    $("#gAddAlbumForm input[name=name]").attr(
      "value", $("#gAddAlbumForm input[name=title]").attr("value").
        replace(/\s+/g, "_").replace(/\.+$/, ""));
  });
