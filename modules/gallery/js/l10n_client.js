// Fork from Drupal's l10n_client module, originally written by:
// Gbor Hojtsy http://drupal.org/user/4166 (original author)
// Young Hahn / Development Seed - http://developmentseed.org/ (friendly user interface)

var Gallery = Gallery || { 'behaviors': {} };

Gallery.attachBehaviors = function(context) {
  context = context || document;
  // Execute all of them.
  jQuery.each(Gallery.behaviors,
              function() {
                  this(context);
              });
};

$(document).ready(function() {
  Gallery.attachBehaviors(this);
});

// Store all l10n_client related data + methods in its own object
jQuery.extend(Gallery, {
  l10nClient: new (function() {
    // Set "selected" string to unselected, i.e. -1
    this.selected = -1;
    // Keybindings
    this.keys = {'toggle':'ctrl+shift+s', 'clear': 'esc'}; // Keybindings
    // Keybinding functions
    this.key = function(pressed) {
      switch(pressed) {
        case 'toggle':
          // Grab user-hilighted text & send it into the search filter
          userSelection = window.getSelection ? window.getSelection() : document.getSelection ? document.getSelection() : document.selection.createRange().text;
          userSelection = String(userSelection);
          if(userSelection.length > 0) {
            Gallery.l10nClient.filter(userSelection);
            Gallery.l10nClient.toggle(1);
            $('#l10n-client #g-l10n-search').focus();
          } else {
            if($('#l10n-client').is('.hidden')) {
              Gallery.l10nClient.toggle(1);
              if(!$.browser.safari) {
                $('#l10n-client #g-l10n-search').focus();
              }
            } else {
              Gallery.l10nClient.toggle(0);
            }
          }
        break;
        case 'clear':
          this.filter(false);
        break;
      }
    };

    // Toggle the l10nclient
    this.toggle = function(state) {
      switch(state) {
        case 1:
          $('#l10n-client-string-select, #l10n-client-string-editor, #l10n-client .labels .label').show();
          $('#l10n-client').height('22em').removeClass('hidden');
					//$('#l10n-client').slideUp();
					$('#g-minimize-l10n').text("_");
	  // This CSS clashes with Gallery's CSS, probably due to
	  // YUI's grid / floats.
	  // if(!$.browser.msie) {
	  //     $('body').css('border-bottom', '22em solid #fff');
	  // }
          $.cookie('Gallery_l10n_client', '1', {expires: 7, path: '/'});
        break;
        case 0:
          $('#l10n-client-string-select, #l10n-client-string-editor, #l10n-client .labels .label').hide();
          $('#l10n-client').height('2em').addClass('hidden');
          // TODO: Localize this message
          $('#g-minimize-l10n').text(MSG_TRANSLATE_TEXT);
          // if(!$.browser.msie) {
          //   $('body').css('border-bottom', '0px');
          // }
          $.cookie('Gallery_l10n_client', '0', {expires: 7, path: '/'});
        break;
      }
    };

    // Get a string from the DOM tree
    this.getString = function(index, type) {
      if (index < l10n_client_data.length) {
        return l10n_client_data[index][type];
      }
      return "";
    };

    // Set a string in the DOM tree
    this.setString = function(index, data) {
      l10n_client_data[index]['translation'] = data;
    };

    // Display the source message
    this.showSourceMessage = function(source, is_plural) {
      if (is_plural) {
        var pretty_source = $('#source-text-tmp-space').text('[one] - ' + source['one']).html();
        pretty_source += '<br/>';
        pretty_source += $('#source-text-tmp-space').text('[other] - ' + source['other']).html();
      } else {
        var pretty_source = $('#source-text-tmp-space').text(source).html();
      }
      $('#l10n-client-string-editor .source-text').html(pretty_source);
    };
    this.isPluralMessage = function(message) {
      return typeof(message) == 'object';
    };

    this.updateTranslationForm = function(translation, is_plural) {
      $('.translationField').addClass('hidden');
      if (is_plural) {
        if (typeof(translation) != 'object') {
          translation = {};
        }
        var num_plural_forms = plural_forms.length;
        for (var i = 0; i < num_plural_forms; i++) {
          var form = plural_forms[i];
          if (translation[form] == undefined) {
            translation[form] = '';
          }
          $("#plural-" + form + " textarea[name='l10n-edit-plural-translation-" + form + "']")
              .attr('value', translation[form]);
          $('#plural-' + form).removeClass('hidden');
        }
      } else {
        $('#l10n-edit-translation').attr('value', translation);
        $('#l10n-edit-translation').removeClass('hidden');
      }
    };

    // Filter the string list by a search string
    this.filter = function(search) {
      if(search == false || search == '') {
        $('#l10n-client #l10n-search-filter-clear').focus();
        $('#l10n-client-string-select li').show();
        $('#l10n-client #g-l10n-search').val('');
        $('#l10n-client #g-l10n-search').focus();
      } else {
        if(search.length > 0) {
          $('#l10n-client-string-select li').hide();
          $('#l10n-client-string-select li').each(function() {
            if ($(this).val().indexOf(search) != -1) {
		$(this).show();
	    }
          });
          $('#l10n-client #g-l10n-search').val(search);
        }
      }
    };

    this.copySourceText = function() {
      var index  = Gallery.l10nClient.selected;
      if (index >= 0) {
        var source = Gallery.l10nClient.getString(index, 'source');
        var is_plural = Gallery.l10nClient.isPluralMessage(source);
        if (is_plural) {
          if (typeof(translation) != 'object') {
            translation = {};
          }
          var num_plural_forms = plural_forms.length;
          for (var i = 0; i < num_plural_forms; i++) {
            var form = plural_forms[i];
            var text = source['other'];
            if (form == 'one') {
              text = source['one'];
            }
            $("#plural-" + form + " textarea[name='l10n-edit-plural-translation-" + form + "']")
                .attr('value', text);
          }
        } else {
          $('#l10n-edit-translation').attr('value', source);
        }

      }
    };
  })
});

// Attaches the localization editor behavior to all required fields.
Gallery.behaviors.l10nClient = function(context) {

  switch($.cookie('Gallery_l10n_client')) {
    case '1':
      Gallery.l10nClient.toggle(1);
    break;
    default:
      Gallery.l10nClient.toggle(0);
    break;
  }

  // If the selection changes, copy string values to the source and target fields.
  // Add class to indicate selected string in list widget.
  $('#l10n-client-string-select li').click(function() {
    $('#l10n-client-string-select li').removeClass('active');
    $(this).addClass('active');
    var index = $('#l10n-client-string-select li').index(this);
    var source = Gallery.l10nClient.getString(index, 'source');
    var key = Gallery.l10nClient.getString(index, 'key');
    var is_plural = Gallery.l10nClient.isPluralMessage(source);
    Gallery.l10nClient.showSourceMessage(source, is_plural);
    Gallery.l10nClient.updateTranslationForm(Gallery.l10nClient.getString(index, 'translation'), is_plural);
    $("#g-l10n-client-save-form input[name='l10n-message-key']").val(key);
    Gallery.l10nClient.selected = index;
  });

  // When l10n_client window is clicked, toggle based on current state.
  $('#g-minimize-l10n').click(function() {
    if($('#l10n-client').is('.hidden')) {
      Gallery.l10nClient.toggle(1);
    } else {
      Gallery.l10nClient.toggle(0);
    }
  });

  // Close the l10n client using an AJAX call and refreshing the page
  $('#g-close-l10n').click(function(event) {
		$.ajax({
      type: "GET",
      url: toggle_l10n_mode_url,
      data: "csrf=" + csrf,
      success: function() {
        window.location.reload(true);
      }
    });
		event.preventDefault();
  });

  // Register keybindings using jQuery hotkeys
  // TODO: Either remove hotkeys code or add query.hotkeys.js.
  if($.hotkeys) {
    $.hotkeys.add(Gallery.l10nClient.keys['toggle'], function(){Gallery.l10nClient.key('toggle');});
    $.hotkeys.add(Gallery.l10nClient.keys['clear'], {target:'#l10n-client #g-l10n-search', type:'keyup'}, function(){Gallery.l10nClient.key('clear');});
  }

  // never actually submit the form as the search is done in the browser
  $('#g-l10n-search-form').submit(function() {
    return false;
  });

  // Custom listener for l10n_client livesearch
  $('#l10n-client #g-l10n-search').keyup(function(key) {
    Gallery.l10nClient.filter($('#l10n-client #g-l10n-search').val());
  });

  // Clear search
  $('#l10n-client #l10n-search-filter-clear').click(function() {
    Gallery.l10nClient.filter(false);
    return false;
  });

  // Send AJAX POST data on form submit.
  $('#g-l10n-client-save-form').ajaxForm({
      dataType: "json",
      success: function(data) {
              var source = Gallery.l10nClient.getString(Gallery.l10nClient.selected, 'source');
              var is_plural = Gallery.l10nClient.isPluralMessage(source);
              var num_plural_forms = plural_forms.length;

              // Store translation in local js
              var translation = {};
              var is_non_empty = false;
              if (is_plural) {
                   for (var i = 0; i < num_plural_forms; i++) {
                      var form = plural_forms[i];
                      translation[form] = $("#plural-" + form + " textarea[name='l10n-edit-plural-translation-" + form + "']").attr('value');
                      is_non_empty = is_non_empty || translation[form];
                  }
              } else {
                  translation = $('#l10n-edit-translation').attr('value');
                  is_non_empty = translation;
              }
              Gallery.l10nClient.setString(Gallery.l10nClient.selected, translation);

              // Mark message as translated / untranslated.
              var source_element = $('#l10n-client-string-select li').eq(Gallery.l10nClient.selected);
              if (is_non_empty) {
                  source_element.removeClass('untranslated').removeClass('active').addClass('translated');
              } else {
                  source_element.removeClass('active').removeClass('translated').addClass('untranslated');
              }

              // Clear the translation form fields
              Gallery.l10nClient.showSourceMessage('', false);
              $('#g-l10n-client-save-form #l10n-edit-translation').val('');

              for (var i = 0; i < num_plural_forms; i++) {
                  var form = plural_forms[i];
                  $("#plural-" + form + " textarea[name='l10n-edit-plural-translation-" + form + "']").val('');
              }
              $("#g-l10n-client-save-form input[name='l10n-message-key']").val('');
          },
              error: function(xmlhttp) {
              // TODO: Localize this message
              alert('An HTTP error @status occured (or empty response).'.replace('@status', xmlhttp.status));
          }
      });

  // TODO: Add copy/clear buttons (without ajax behavior)
  /*         <input type="submit" name="l10n-edit-copy" value="<?= t("Copy source") ?>"/>
        <input type="submit" name="l10n-edit-clear" value="<?= t("Clear") ?>"/>
  */
  // TODO: Handle plurals in copy button

  // Copy source text to translation field on button click.
  $('#g-l10n-client-save-form #l10n-edit-copy').click(function() {
    $('#g-l10n-client-save-form #l10n-edit-target').val($('#l10n-client-string-editor .source-text').text());
  });

  // Clear translation field on button click.
  $('#g-l10n-client-save-form #l10n-edit-clear').click(function() {
    $('#g-l10n-client-save-form #l10n-edit-target').val('');
  });
};
