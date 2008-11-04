<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gSidebar" class="yui-b">

  <div id="gAlbumTree">
    <h2>Album Navigation</h2>

    <div id="gTreeContainer"><!-- there might be a better way to make this accessible to the JS -->
      <ul>
	<li>Gallery
	  <ul>
	    <li>Friends &amp; Family
	      <ul>
		<li>Christmas 2006</li>
		<li>Family Reunion</li>
		<li>Christmas 2007</li>
	      </ul>
	    </li>
	    <li>Vactions
	      <ul>
		<li>Cuba</li>
		<li>Europe</li>
	      </ul>
	    </li>
	  </ul>
	</li>
      </ul>
    </div>
  </div>

  <script type="text/javascript">
    //global variable to allow console inspection of tree:
    var tree;

    //anonymous function wraps the remainder of the logic:
    (function() {

    //function to initialize the tree:
    function treeInit() {
    buildRandomTextNodeTree();
    }

    //Function  creates the tree and
    //builds between 3 and 7 children of the root node:
    function buildRandomTextNodeTree() {
    //instantiate the tree:
    tree = new YAHOO.widget.TreeView("gTreeContainer");

    // Expand and collapse happen prior to the actual expand/collapse,
    // and can be used to cancel the operation
    tree.subscribe("expand", function(node) {
    YAHOO.log(node.index + " was expanded", "info", "example");
    // return false; // return false to cancel the expand
    });

    tree.subscribe("collapse", function(node) {
    YAHOO.log(node.index + " was collapsed", "info", "example");
    });

    // Trees with TextNodes will fire an event for when the label is clicked:
    tree.subscribe("labelClick", function(node) {
    YAHOO.log(node.index + " label was clicked", "info", "example");
    });

    //The tree is not created in the DOM until this method is called:
    tree.draw();
    }

    //Add an onDOMReady handler to build the tree when the document is ready
    YAHOO.util.Event.onDOMReady(treeInit);

    })();
  </script>


  <table class="gMetadata">
    <caption><h2>Album Info</h2></caption>
    <tbody>
      <tr>
	<th>Name:</th>
	<td><strong>Christmas 2007</strong></td>
      </tr>
      <tr>
	<th>Taken:</th>
	<td><span class="date" title="January 21, 2008 8:30pm">January 21, 2008</td>
      </tr>
      <tr>
	<th>Location:</th>
	<td><a href="#" title="see the location on a map">Mountain View</a></td>
      </tr>
      <tr>
	<th>Owner:</th>
	<td><a href="#">username</a></td>
      </tr>
      <tr>
	<th>Uploaded:</th>
	<td><span class="date" title="October 23, 2008 11:37am">October 23, 2008</td>
      </tr>
    </tbody>
  </table>

  <div class="gTagCloud">
    <h2>Tag cloud</h2>
    <ul>
      <li><a href="#" class="m size0">animation</a></li>
      <li><a href="#" class="m size0">art</a></li>
      <li><a href="#" class="m size1">blind</a></li>
      <li><a href="#" class="m size3">blog</a></li>
      <li><a href="#" class="m size1">bug-tracker</a></li>
      <li><a href="#" class="m size2">bugs20</a></li>
      <li><a href="#" class="m size0">canvas</a></li>
      <li><a href="#" class="m size0">classification</a></li>
      <li><a href="#" class="m size4">cocktail</a></li>
      <li><a href="#" class="m size0">exhibtion</a></li>
      <li><a href="#" class="m size0">forum</a></li>
      <li><a href="#" class="m size1">geo-tagging</a></li>
      <li><a href="#" class="m size0">german</a></li>
      <li><a href="#" class="m size0">germany</a></li>
      <li><a href="#" class="m size0">gläser</a></li>
      <li><a href="#" class="m size0">graffiti</a></li>
      <li><a href="#" class="m size0">illustration</a></li>
      <li><a href="#" class="m size0">ITP</a></li>
      <li><a href="#" class="m size0">javascript</a></li>
      <li><a href="#" class="m size0">miami</a></li>
      <li><a href="#" class="m size0">miknow</a></li>
      <li><a href="#" class="m size0">nyc</a></li>
      <li><a href="#" class="m size0">NYU</a></li>
      <li><a href="#" class="m size0">ontology</a></li>
      <li><a href="#" class="m size0">open-source</a></li>
      <li><a href="#" class="m size0">project</a></li>
      <li><a href="#" class="m size0">school-of-information</a></li>
      <li><a href="#" class="m size0">screenshot</a></li>
      <li><a href="#" class="m size0">shiftspace</a></li>
      <li><a href="#" class="m size0">shop</a></li>
      <li><a href="#" class="m size0">tagging</a></li>
      <li><a href="#" class="m size2">talkingpoints</a></li>
      <li><a href="#" class="m size0">university-of-michigan</a></li>
      <li><a href="#" class="m size1">usability</a></li>
      <li><a href="#" class="m size0">writing</a></li>
    </ul>
  </div>
</div>
