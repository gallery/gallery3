$("#gAddAlbumForm input[name=title]").change(
  function() {
    $("#gAddAlbumForm input[name=name]").attr(
      "value", $("#gAddAlbumForm input[name=title]").attr("value").
        replace(/[\s\/]+/g, "-").replace(/\.+$/, ""));
    $("#gAddAlbumForm input[name=slug]").attr(
      "value", $("#gAddAlbumForm input[name=title]").attr("value").
        replace(/[^A-Za-z0-9-_]+/g, "-"));
  });
$("#gAddAlbumForm input[name=title]").keyup(
  function() {
    $("#gAddAlbumForm input[name=name]").attr(
      "value", $("#gAddAlbumForm input[name=title]").attr("value").
        replace(/[\s\/]+/g, "-").replace(/\.+$/, ""));
    $("#gAddAlbumForm input[name=slug]").attr(
      "value", $("#gAddAlbumForm input[name=title]").attr("value").
        replace(/[^A-Za-z0-9-_]+/g, "-"));
  });
