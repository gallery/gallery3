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
            $('#l10n-client #gL10nSearch').focus();      
          } else {
            if($('#l10n-client').is('.hidden')) {
              Gallery.l10nClient.toggle(1);
              if(!$.browser.safari) {
                $('#l10n-client #gL10nSearch').focus();
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
    }
    // Toggle the l10nclient
    this.toggle = function(state) {
      switch(state) {
        case 1:
          $('#l10n-client-string-select, #l10n-client-string-editor, #l10n-client .labels .label').show();
          $('#l10n-client').height('22em').removeClass('hidden');
          $('#l10n-client .labels .toggle').text('X');
        /*
         * This CSS clashes with Gallery's CSS, probably due to
         * YUI's grid / floats. 
          if(!$.browser.msie) {
              $('body').css('border-bottom', '22em solid #fff');
          }
        */
          $.cookie('Gallery_l10n_client', '1', {expires: 7, path: '/'});
        break;
        case 0:
          $('#l10n-client-string-select, #l10n-client-string-editor, #l10n-client .labels .label').hide();
          $('#l10n-client').height('2em').addClass('hidden');
          // TODO: Localize this message
          $('#l10n-client .labels .toggle').text('Translate Text');
        /*
          if(!$.browser.msie) {
            $('body').css('border-bottom', '0px');
          }
        */
          $.cookie('Gallery_l10n_client', '0', {expires: 7, path: '/'});
        break;        
      }
    }
    // Get a string from the DOM tree
    this.getString = function(index, type) {
      return l10n_client_data[index][type];
    }
    // Set a string in the DOM tree
    this.setString = function(index, data) {
      l10n_client_data[index]['translation'] = data;
    }
    // Filter the the string list by a search string
    this.filter = function(search) {
      if(search == false || search == '') {
        $('#l10n-client #l10n-search-filter-clear').focus();
        $('#l10n-client-string-select li').show();
        $('#l10n-client #gL10nSearch').val('');
        $('#l10n-client #gL10nSearch').focus();
      } else {
        if(search.length > 0) {
          $('#l10n-client-string-select li').hide();
          $('#l10n-client-string-select li:contains('+search+')').show();
          $('#l10n-client #gL10nSearch').val(search);
        }
      }
    }
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

    $('#l10n-client-string-editor .source-text').text(Gallery.l10nClient.getString(index, 'source'));
    $('#gL10nClientSaveForm #l10n-edit-target').val(Gallery.l10nClient.getString(index, 'translation'));

    Gallery.l10nClient.selected = index;
  });

  // When l10n_client window is clicked, toggle based on current state.
  $('#l10n-client .labels .toggle').click(function() {
    if($('#l10n-client').is('.hidden')) {
      Gallery.l10nClient.toggle(1);
    } else { 
      Gallery.l10nClient.toggle(0);
    }
  });

  // Register keybindings using jQuery hotkeys
  // TODO: Either remove hotkeys code or add query.hotkeys.js.
  if($.hotkeys) {
    $.hotkeys.add(Gallery.l10nClient.keys['toggle'], function(){Gallery.l10nClient.key('toggle')});
    $.hotkeys.add(Gallery.l10nClient.keys['clear'], {target:'#l10n-client #gL10nSearch', type:'keyup'}, function(){Gallery.l10nClient.key('clear')});
  }
  
  // Custom listener for l10n_client livesearch
  $('#l10n-client #gL10nSearch').keyup(function(key) {
    Gallery.l10nClient.filter($('#l10n-client #gL10nSearch').val());
  });

  // Clear search
  $('#l10n-client #l10n-search-filter-clear').click(function() {
    Gallery.l10nClient.filter(false);
    return false;
  });

  // Send AJAX POST data on form submit.
  $('#gL10nClientSaveForm').ajaxForm({
      dataType: "json",
      success: function(data) {
        // Store string in local js
        Gallery.l10nClient.setString(Gallery.l10nClient.selected, $('#gL10nClientSaveForm #l10n-edit-target').val());

        // Mark string as translated.
        $('#l10n-client-string-select li').eq(Gallery.l10nClient.selected).removeClass('untranslated').removeClass('active').addClass('translated').text($('#gL10nClientSaveForm #l10n-edit-target').val());

        // Empty input fields.
        $('#l10n-client-string-editor .source-text').html('');
        $('#gL10nClientSaveForm #l10n-edit-target').val('');
      },
      error: function(xmlhttp) {
        // TODO: Localize this message
        alert('An HTTP error @status occured (or empty response).'.replace('@status', xmlhttp.status));
      }
  });


  // Copy source text to translation field on button click.
  $('#gL10nClientSaveForm #l10n-edit-copy').click(function() {
    $('#gL10nClientSaveForm #l10n-edit-target').val($('#l10n-client-string-editor .source-text').text());
  });

  // Clear translation field on button click.
  $('#gL10nClientSaveForm #l10n-edit-clear').click(function() {
    $('#gL10nClientSaveForm #l10n-edit-target').val('');
  });
};
