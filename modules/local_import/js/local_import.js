/**
 * Set up autocomplete on the server path list
 * 
 */
$("document").ready(function() {
  var previous_search = "";
  $("#gLocalImportAdmin input").autocomplete({
    url: base_url + "admin/local_import/autocomplete",
    mustMatch: true,
  });
  ajaxify_form({
    form: "#gLocalImportAdmin form",
    url: "admin/local_import/",
    returnCode: 200,
    callback: function(xhr, statusText) {
      $("#gImportLocalDirList").html(xhr.responseText);
      setDroppable("#gImportLocalDirList #gRemoveDir");
      setDraggable("#gImportLocalDirList li");
    }
  });

  setDroppable("#gImportLocalDirList #gRemoveDir");
  setDraggable("#gImportLocalDirList li");
});

function setDraggable(selector) {
  $(selector).draggable({
    helper: 'clone',
//    containment: "#gImportLocalDirList",
    opacity: .6,
    revert: "invalid"
  });
}

function setDroppable(selector) {
  $(selector).droppable({
    accept: "#gImportLocalDirList li",
    drop: function(ev, ui) {
      var element = ui.draggable[0];

      if (confirm("Do you really want to remove " + element.textContent)) {
        $.ajax({
          data: "path=" + element.textContent,
          url: base_url + "admin/local_import/remove",
          success: function(data, textStatus) {
            $("#gImportLocalDirList").html(data);
            setDroppable("#gImportLocalDirList #gRemoveDir");
            setDraggable("#gImportLocalDirList li");
          },
          error: function(xhr, textStatus, errorThrown) {
            alert("Text Status: " + textStatus + " Http Error Code: " + xhr.status);
          },
          type: "POST"
        });
      }
    }
  });
}

function ajaxify_form(options) {
  $(options.form).ajaxForm({
    complete:function(xhr, statusText) {
      options.callback(xhr, statusText);
      $(options.form).clearForm();
    }
  });
}
