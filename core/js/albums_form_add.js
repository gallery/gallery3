$("#gAddAlbumForm input[name=title]").keyup(
  function() {
    $("#gAddAlbumForm input[name=name]").attr(
      "value", $("#gAddAlbumForm input[name=title]").attr("value"));
  });
