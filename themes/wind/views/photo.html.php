<?php defined("SYSPATH") or die("No direct script access.") ?>

<?php if (access::can("view_full", $theme->item())): ?>
<!-- Use javascript to show the full size as an overlay on the current page -->
<script type="text/javascript">
  $(document).ready(function() {
    full_dims = [<?php echo $theme->item()->width ?>, <?php echo $theme->item()->height ?>];
    $(".g-fullsize-link").click(function() {
      $.gallery_show_full_size(<?php echo html::js_string($theme->item()->file_url()) ?>, full_dims[0], full_dims[1]);
      return false;
    });

    // After the image is rotated or replaced we have to reload the image dimensions
    // so that the full size view isn't distorted.
    $("#g-photo").on("gallery.change", function() {
      $.ajax({
        url: "<?php echo url::site("items/dimensions/" . $theme->item()->id) ?>",
        dataType: "json",
        success: function(data, textStatus) {
          full_dims = data.full;
        }
      });
    });
  });
</script>
<?php endif ?>

<div id="g-item">
  <?php echo $theme->photo_top() ?>

  <?php echo $theme->paginator() ?>

  <div id="g-photo">
    <?php echo $theme->resize_top($item) ?>
    <?php if (access::can("view_full", $item)): ?>
    <a href="<?php echo $item->file_url() ?>" class="g-fullsize-link" title="<?php echo t("View full size")->for_html_attr() ?>">
      <?php endif ?>
      <?php echo $item->resize_img(array("id" => "g-item-id-{$item->id}", "class" => "g-resize")) ?>
      <?php if (access::can("view_full", $item)): ?>
    </a>
    <?php endif ?>
    <?php echo $theme->resize_bottom($item) ?>
  </div>

  <div id="g-info">
    <h1><?php echo html::purify($item->title) ?></h1>
    <div><?php echo nl2br(html::purify($item->description)) ?></div>
  </div>

  <?php echo $theme->photo_bottom() ?>
</div>
