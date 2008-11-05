<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gSidebar" class="yui-b">
  <? foreach ($theme->blocks() as $block): ?>
    <?= $block ?>
  <? endforeach ?>

  <div class="gBlock">
    <div class="gBlockHeader">
      <h2>Item Info</h2>
      <a href="#" class="minimize">[-]</a>
    </div>
    <table class="gMetadata gBlockContent">
      <tbody>
        <tr>
          <th>Title:</th>
          <td>Christmas 2007</td>
        </tr>
        <tr>
          <th>Taken:</th>
          <td>January 21, 2008</td>
        </tr>
        <tr>
          <th>Uploaded:</th>
          <td>January 27, 2008</td>
        </tr>
        <tr>
          <th>Owner:</th>
          <td><a href="#">username</a></td>
        </tr>
        <tr>
          <td colspan="2" class="toggle">
            <a href="#">more \/</a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="gBlock">
    <div class="gBlockHeader">
      <h2>Location</h2>
      <a href="#" class="minimize">[-]</a>
    </div>
    <iframe class="gBlockContent" width="214" height="214" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?f=q&amp;hl=en&amp;geocode=&amp;q=mountain+view&amp;sll=37.0625,-95.677068&amp;sspn=50.823846,89.648437&amp;ie=UTF8&amp;z=12&amp;g=mountain+view&amp;ll=37.433704,-122.056046&amp;output=embed&amp;s=AARTsJoyjpSOFMFEv5XZbREeW_hGGS28pQ"></iframe>
    <br />
    <small class="gBlockContent"><a href="http://maps.google.com/maps?f=q&amp;hl=en&amp;geocode=&amp;q=mountain+view&amp;sll=37.0625,-95.677068&amp;sspn=50.823846,89.648437&amp;ie=UTF8&amp;z=12&amp;g=mountain+view&amp;ll=37.433704,-122.056046&amp;source=embed" style="color:#0000FF;text-align:left">View Larger Map</a></small>
  </div>

</div><!-- END #gSideBar -->
