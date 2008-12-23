$("gUploadWatermarkForm").ready(function() {
  ajaxify_watermark_add_form();
});

function ajaxify_watermark_add_form() {
  $("#gUploadWatermarkForm").ajaxForm({
    complete:function(xhr, statusText) {
      $("#gUploadWatermarkForm").replaceWith(xhr.responseText);
      ajaxify_watermark_add_form();
    }
  });
}

var locations = {
  areas: {},
  names: ["northwest", "north",   "northeast",
          "west",      "center",  "east",
          "southwest", "south",   "southeast"],
  nameIndex: function(name) {
    for (var row=0; row < 3; row++) {
      for (var col=0; col < 3; col++) {
        var index = row * 3 + col;
        if (this.names[index] == name) {
          return index;
        }
      }
    }
  },
  getArea: function(x, y) {
    for (var row=0; row < 3; row++) {
      for (var col=0; col < 3; col++) {
        var name = this.names[row * 3 + col];
        var area = this.areas[name];
        var check = area.top <= y && y < area.bottom && area.left <= x && x < area.right;
        if (check) {
          return name;
        }
      }
    }
  },
  getDimension: function (area) {
    return this.areas[area];
  }
};



locations.areas["northeast"] = {};
locations.areas["north"] = {};
locations.areas["northwest"] = {};
locations.areas["east"] = {};
locations.areas["center"] = {};
locations.areas["west"] = {};
locations.areas["southeast"] = {};
locations.areas["south"] = {};
locations.areas["southwest"] = {};

function calculateAreas(target) {
  var cell_height = $(target).attr("offsetHeight") / 3;
  var cell_width = $(target).attr("offsetWidth") / 3;

  var top = $(target).attr("offsetTop");
  for (var row=0; row < 3; row++) {
    var left = $(target).attr("offsetLeft");
    for (var col=0; col < 3; col++) {
      var name = locations.names[row * 3 + col];
      locations.areas[name] = {
        top: top,
        left: left,
        right: left + cell_width,
        bottom: top + cell_height};
      left += cell_width;
    }
    top += cell_height;
  }
}

function watermark_dialog_initialize() {
  // Adjust the size of the dialog to accomodate the image content
  var container = $("#gDialog").parent().parent();
  var container_height = $(container).attr("offsetHeight");
  var container_width = $(container).attr("offsetWidth");

  var new_height = $("#gDialog").attr("offsetHeight") +
    container.find("div.ui-dialog-titlebar").attr("offsetHeight") +
    container.find("div.ui-dialog-buttonpane").attr("offsetHeight");
  var height = Math.max(new_height, container_height);
  var width = Math.max($("#gDialog").attr("offsetWidth"), container_width);
  container.css("height", height + "px");
  container.css("width", width + "px");
  container.css("top", ((document.height - height) / 2) + "px");
  container.css("left", ((document.width - width) / 2) + "px");

  $("#gTargetImage").droppable({
    accept: "div",
    greedy: true,
    hoverClass: "droppable-hover",
    drop: function(ev, ui) {
      var areaname = locations.getArea(ui.position.left, ui.position.top);
      positionWatermark(areaname);
      $("#position").val(locations.nameIndex(areaname));
    }
  });

  $("#gWaterMark").draggable({
    helper: 'clone',
    containment: "#gTargetImage",
    opacity: .6
  });

  $("#position").change(function() {
    positionWatermark($("option:selected", this).text());
  });

  calculateAreas($("#gTargetImage"));
  var dropdown = $("#position");
  positionWatermark($("option:selected", dropdown).text());
}

function positionWatermark(area) {
  var region = locations.getDimension(area);

  $("#gWaterMark").css("top", region.top + "px");
  $("#gWaterMark").css("left", region.left + "px");
}
