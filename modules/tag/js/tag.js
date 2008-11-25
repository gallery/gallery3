$("document").ready(function() {
  $("#gTagLess").click(function(event){
    event.preventDefault();
    get_tag_block($("#gTagLess").attr("href"));
  });
  $("#gTagMore").click(function(event){
    event.preventDefault();
    get_tag_block($("#gTagMore").attr("href"));
  });
});

function get_tag_block(url) {
    $.get(url, function(data) {
      $('#gTag').html(data);
      $("#gTagLess").click(function(event){
       event.preventDefault();
       get_tag_block($("#gTagLess").attr("href"));
      });
      $("#gTagMore").click(function(event){
        event.preventDefault();
        get_tag_block($("#gTagMore").attr("href"));
      });
   });
}
