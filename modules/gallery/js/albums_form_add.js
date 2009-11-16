$("#g-add-album-form input[name=title]").change(
  function() {
    $("#g-add-album-form input[name=name]").attr(
      "value", $("#g-add-album-form input[name=title]").attr("value")
        .replace(/[\s\/]+/g, "-").replace(/\.+$/, ""));
    $("#g-add-album-form input[name=slug]").attr(
      "value", $("#g-add-album-form input[name=title]").attr("value")
        .replace(/[^A-Za-z0-9-_]+/g, "-")
	.replace(/^-+/, "")
	.replace(/-+$/, ""));
  });
$("#g-add-album-form input[name=title]").keyup(
  function() {
    $("#g-add-album-form input[name=name]").attr(
      "value", $("#g-add-album-form input[name=title]").attr("value")
        .replace(/[\s\/]+/g, "-")
	.replace(/\.+$/, ""));
    $("#g-add-album-form input[name=slug]").attr(
      "value", $("#g-add-album-form input[name=title]").attr("value")
        .replace(/[^A-Za-z0-9-_]+/g, "-")
	.replace(/^-+/, "")
	.replace(/-+$/, ""));
  });
