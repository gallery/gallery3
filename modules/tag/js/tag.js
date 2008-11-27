$("document").ready(function() {
  $("#gAddTag").submit(function(event){
    get_tag_block($("#gAddTag").attr("action"));
    return false;
  });
});

function get_tag_block(url) {
    $.get(url, function(data) {
      $('#gTagFormContainer').html(data);
      $("#gAddTag").submit(function(event){
         get_tag_block($("#gAddTag").attr("action"));
        return false;
      });
   });
}
