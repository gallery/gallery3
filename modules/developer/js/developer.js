$("#gDeveloperTools").ready(function() {
  $("#gDeveloperTools").tabs();
});

var module_success = function(data) {
  $("#gGenerateModule").after('<div id="moduleProgress" style="margin-left: 5em;"></div>');
  $("#moduleProgress").progressbar();

  var task = data.task;
  var url = data.url;
  var done = false;
  while (!done) {
    $.ajax({async: false,
      success: function(data, textStatus) {
        $("#moduleProgress").progressbar("value", data.task.percent_complete);
        done = data.task.done;
      },
      dataType: "json",
      type: "POST",
      url: url
    });
  }
  document.location.reload();
};

function ajaxify_developer_form(selector, success) {
  $(selector).ajaxForm({
    dataType: "json",
    success: function(data) {
      if (data.form && data.reseult != "started") {
        $(selector).replaceWith(data.form);
        ajaxify_developer_form(selector, success);
      }
      if (data.result == "started") {
        success(data);
      }
    }
  });
}
