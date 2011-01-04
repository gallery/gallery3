<?php defined("SYSPATH") or die("No direct script access.") ?>
<link rel="stylesheet" type="text/css" href="<?= url::file("modules/organize/vendor/ext/css/ext-all.css") ?>" />
<link rel="stylesheet" type="text/css" href="<?= url::file("modules/organize/vendor/ext/css/ux-all.css") ?>" />
<link rel="stylesheet" type="text/css" href="<?= url::file("modules/organize/css/organize.css") ?>" />
<style type="text/css">
  .g-organize div.thumb-album div.icon {
    background-image: url(<?= url::file("modules/organize/vendor/ext/images/default/tree/folder.gif") ?>);
  }
</style>

<script type="text/javascript" src="<?= url::file("modules/organize/vendor/ext/js/ext-base.js") ?>"></script>
<script type="text/javascript" src="<?= url::file("modules/organize/vendor/ext/js/ext-all.js") ?>"></script>
<script type="text/javascript">
  Ext.BLANK_IMAGE_URL = "<?= url::file("modules/organize/vendor/ext/images/default/s.gif") ?>";
  Ext.Ajax.timeout = 1000000;  // something really large

  Ext.onReady(function() {
    /*
     * ********************************************************************************
     * Utility functions for loading data and making changes
     * ********************************************************************************
     */
    var current_album_id = null;
    var load_album_data = function(id) {
      Ext.Msg.wait(<?= t("Loading...")->for_js() ?>);
      Ext.Ajax.request({
        url: '<?= url::site("organize/album_info/__ID__") ?>'.replace("__ID__", id),
        success: function(xhr, opts) {
          Ext.Msg.hide();
          var album_info = Ext.util.JSON.decode(xhr.responseText);
          var store = new Ext.data.JsonStore({
            autoDestroy: true,
            fields: ["id", "thumb_url", "width", "height", "type", "title"],
            idProperty: "id",
            root: "children",
            data: album_info
          });
          current_album_id = id;
          thumb_data_view.bindStore(store);
          sort_column_combobox.setValue(album_info.sort_column);
          sort_order_combobox.setValue(album_info.sort_order);
        },
        failure: function() {
          Ext.Msg.hide();
          Ext.Msg.alert(
            <?= t("An error occurred.  Consult your system administrator.")->for_js() ?>);
        }
      });
    };

    var reload_album_data = function() {
      if (current_album_id) {
        load_album_data(current_album_id);
      }
    };

    var set_album_sort = function(params) {
      Ext.Msg.wait(<?= t("Changing sort...")->for_js() ?>);
      params['csrf'] = '<?= access::csrf_token() ?>';
      Ext.Ajax.request({
        url: '<?= url::site("organize/set_sort/__ID__") ?>'.replace("__ID__", current_album_id),
        method: "post",
        success: function() {
          Ext.Msg.hide();
          reload_album_data();
        },
        failure: function() {
          Ext.Msg.hide();
          Ext.Msg.alert(
            <?= t("An error occurred.  Consult your system administrator.")->for_js() ?>);
        },
        params: params
      });
    }

    /*
     * ********************************************************************************
     * JsonStore, DataView and Panel for viewing albums
     * ********************************************************************************
     */
    thumb_data_view = new Ext.DataView({
      autoScroll: true,
      enableDragDrop: true,
      itemSelector: "div.thumb",
      listeners: {
        "render": function(v) {
          v.dragZone = new Ext.dd.DragZone(v.getEl(), {
            getDragData: function(e) {
              var target = e.getTarget(v.itemSelector, 10);
              if (target) {
                if (!v.isSelected(target)) {
                  v.onClick(e);
                }
                var selected_nodes = v.getSelectedNodes();
                var drag_data = {
                  nodes: selected_nodes,
                  repair_xy: Ext.fly(target).getXY()
                };
                if (selected_nodes.length == 1) {
                  drag_data.ddel = target;
                } else {
                  var div = document.createElement("div");
                  div.className = "multi-proxy";
                  for (var i = 0; i != selected_nodes.length; i++) {
                    div.appendChild(selected_nodes[i].cloneNode(true));
                    if ((i+1) % 3 == 0) {
                      div.appendChild(document.createElement("br"));
                    }
                  }
                  drag_data.ddel = div;
                }
                return drag_data;
              }
            },
            getRepairXY: function() {
              return this.dragData.repair_xy;
            }
          });

          v.dropZone = new Ext.dd.DropZone(v.getEl(), {
            getTargetFromEvent: function(e) {
              return e.getTarget("div.thumb", 10);
            },
            onNodeEnter: function(target, dd, e, data) {
              Ext.fly(target).addClass("active");
            },
            onNodeOut: function(target, dd, e, data) {
              Ext.fly(target).removeClass("active");
            },
            onNodeOver: function(target, dd, e, data) {
              return Ext.dd.DropZone.prototype.dropAllowed;
            },
            onNodeDrop: function(target, dd, e, data) {
              var nodes = data.nodes;
              source_ids = [];
              for (var i = 0; i != nodes.length; i++) {
                source_ids.push(Ext.fly(nodes[i]).getAttribute("rel"));
              }
              Ext.Msg.wait(<?= t("Rearranging...")->for_js() ?>);
              Ext.Ajax.request({
                url: '<?= url::site("organize/move_before") ?>',
                method: "post",
                success: function() {
                  Ext.Msg.hide();
                  reload_album_data();
                },
                failure: function() {
                  Ext.Msg.hide();
                  Ext.Msg.alert(
                    <?= t("An error occurred.  Consult your system administrator.")->for_js() ?>);
                },
                params: {
                  source_ids: source_ids.join(","),
                  target_id: Ext.fly(target).getAttribute("rel"),
                  csrf: '<?= access::csrf_token() ?>'
                }
              });
              return true;
            }
          });
        }
      },
      loadingText: <?= t("Loading...")->for_js() ?>,
      multiSelect: true,
      selectedClass: "selected",
      tpl: new Ext.XTemplate(
        '<tpl for=".">',
        '<div class="thumb thumb-{type}" id="thumb-{id}" rel="{id}">',
        '<img src="{thumb_url}" width="{width}" height="{height}" title="{title}">',
        '<div class="icon"></div>',
        '</div>',
        '</tpl>')
    });

    /*
     * ********************************************************************************
     * Toolbar with sort column, sort order and a close button.
     * ********************************************************************************
     */
    var sort_column_combobox = new Ext.form.ComboBox({
      mode: "local",
      editable: false,
      allowBlank: false,
      forceSelection: true,
      triggerAction: "all",
      store: new Ext.data.ArrayStore({
        id: 0,
        fields: ["key", "value"],
        data: [
        <? foreach (album::get_sort_order_options() as $key => $value): ?>
          ["<?= $key ?>", <?= $value->for_js() ?>],
        <? endforeach ?>
        ]
      }),
      listeners: {
        "select": function(combo, record, index) {
          set_album_sort({sort_column: record.id});
        }
      },
      valueField: "key",
      displayField: "value"
    });

    var sort_order_combobox = new Ext.form.ComboBox({
      mode: "local",
      editable: false,
      allowBlank: false,
      forceSelection: true,
      triggerAction: "all",
      store: new Ext.data.ArrayStore({
        id: 0,
        fields: ["key", "value"],
        data: [
          ["ASC", <?= t("Ascending")->for_js() ?>],
          ["DESC", <?= t("Descending")->for_js() ?>]]
      }),
      listeners: {
        "select": function(combo, record, index) {
          set_album_sort({sort_order: record.id});
        }
      },
      valueField: "key",
      displayField: "value"
    });

    var button_panel = new Ext.Panel({
      layout: "hbox",
      region: "south",
      height: 24,
      layoutConfig: {
        align: "stretch"
      },
      items: [sort_column_combobox, sort_order_combobox,
        {
          xtype: "spacer",
          flex: 4,
        }, {
          xtype: "button",
          flex: 1,
          text: <?= t("Close")->for_js() ?>,
          listeners: {
            "click": function() {
              parent.done_organizing(current_album_id);
            }
          }
        },
      ]
    });

    var album_panel = new Ext.Panel({
      layout: "border",
      region: "center",
      items: [
        {
          xtype: "label",
          region: "north",
          text: <?= t("Drag and drop photos to re-order or move between albums")->for_js() ?>,
          margins: "5 5 5 10",
        },
        {
          xtype: "panel",
          layout: "fit",
          region: "center",
          items: [thumb_data_view]
        },
        button_panel
      ]
    });

    /*
     * ********************************************************************************
     *  TreeLoader and TreePanel
     * ********************************************************************************
     */
    var tree_loader = new Ext.tree.TreeLoader({
      dataUrl: '<?= url::site("organize/tree/{$album->id}") ?>',
      nodeParameter: "root_id",
      requestMethod: "post",
    });

    var tree_panel = new Ext.tree.TreePanel({
      useArrows: true,
      autoScroll: true,
      animate: true,
      border: false,
      containerScroll: true,
      enableDD: true,
      ddGroup: "organizeDD",
      listeners: {
        "click": function(node) {
          load_album_data(node.id);
        },
        "render": function(v) {
          v.dropZone = new Ext.dd.DropZone(v.getEl(), {
            getTargetFromEvent: function(e) {
              return e.getTarget("div.x-tree-node-el", 10);
            },
            onNodeDrop: function(target, dd, e, data) {
              var nodes = data.nodes;
              source_ids = [];
              for (var i = 0; i != nodes.length; i++) {
                var node = Ext.fly(nodes[i]);
                source_ids.push(node.getAttribute("rel"));
              }
              var target_id = target.getAttribute("ext:tree-node-id");
              Ext.Msg.wait(<?= t("Moving...")->for_js() ?>);
              Ext.Ajax.request({
                url: '<?= url::site("organize/reparent") ?>',
                method: "post",
                success: function() {
                  Ext.Msg.hide();
                  reload_album_data();
                  v.getNodeById(target_id).reload();

                  // If the target node contains the selected node, then the selected
                  // node just got strafed by the target's reload and no longer exists,
                  // so we can't reload it.
                  var selected_node = v.getNodeById(current_album_id);
                  if (selected_node) {
                    selected_node.reload();
                  }
                },
                failure: function() {
                  Ext.Msg.hide();
                  Ext.Msg.alert(
                    <?= t("An error occurred.  Consult your system administrator.")->for_js() ?>);
                },
                params: {
                  source_ids: source_ids.join(","),
                  target_id: target_id,
                  csrf: '<?= access::csrf_token() ?>'
                }
              });
              return true;
            }
          })
        }
      },
      loader: tree_loader,

      region: "west",
      width: 150,
      split: true,

      root: {
        nodeType: "async",
        text: "<?= item::root()->title ?>",
        draggable: false,
        id: "<?= item::root()->id ?>",
        expanded: true,
      }
    });

    var first_organize_load = true;
    tree_loader.addListener("load", function() {
      if (first_organize_load) {
        tree_panel.getNodeById(<?= $album->id ?>).select();
        load_album_data(<?= $album->id ?>);
        first_organize_load = false;
        tree_loader.doPreload = function() { return false; }
      }
    });
    tree_panel.getRootNode().expand();

    new Ext.Viewport({
      layout: "border",
      cls: "g-organize",
      items: [tree_panel, album_panel]
    });
  });
</script>
