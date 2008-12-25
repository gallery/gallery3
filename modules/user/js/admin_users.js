$("document").ready(function() {
  ajaxify_admin_users_form();
});

function ajaxify_admin_users_form() {
  $("#gAddUser form").ajaxForm({
    dataType: "json",
    success: function(data) {
      if (data.result == "success") {
        $(data.output).insertBefore("#gUsers li:last-child");
        $('.ui-accordion-container').accordion();
        $("#gUser li:last-child form").clearForm();
      }
    }
  });
};
