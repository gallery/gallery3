$("document").ready(function() {
  var options = {
    target: "#gTagFormContainer",
    success: function(responseText, statusText) {
      $("#gAddTag").ajaxForm(options);
    }
  };
  $("#gAddTag").ajaxForm(options);
});

function get_tag_block(url) {
  $.post(url, function(data) {
    $('#gTagFormContainer').html(data);
    $("#gAddTag").submit(function(event){
      get_tag_block($("#gAddTag").attr("action"));
      return false;
    });
  });
}
