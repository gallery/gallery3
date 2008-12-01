<? defined("SYSPATH") or die("No direct script access."); ?>
<html>
  <head>
    <title>Gallery3 Scaffold</title>
    <style>
      body {
        background: #999;
        font-family: Trebuchet MS;
      }

      div.outer {
        width: 600px;
        background: white;
        border: 1px solid black;
        margin: 0 auto;
        padding: -10px;
      }

      div.inner {
        padding: 0 1em 0 1em;
        margin: 0px;
      }

      h1, h2, h3 {
        margin-bottom: .1em;
      }

      p {
        margin: 0 0 0 0;
        padding-left: 1em;
      }

      table {
        padding-left: 1em;
      }

      pre {
        border: 1px solid #666;
        margin: 1em 0;
        padding: .5em;
        overflow: scroll;
      }

      .error {
        color: red;
      }

      .success {
        color: green;
      }

      p.success {
        font-weight: bold;
      }

      div.block {
        padding: 0px;
        margin: 0px;
      }

      ul {
        margin-top: -.25em;
      }

      div#activities {
        margin-bottom: 1em;
      }

      div.activity {
        display: none;
      }

      ul.tabs {
        margin-left: 0;
        padding: 1em 0 2px 1em;
        white-space: nowrap;
        border-bottom: 1px solid black;
      }

      ul.tabs li {
        display: inline;
        list-style-type: none;
      }

      ul.tabs a {
        padding: 3px 10px;
        color: #fff;
        background-color: #036;
        text-decoration: none;
      }

      div#access {
        margin-top: -20px;
        padding: 0px;
        padding-left: 20px;
      }

      div#access ul.tabs a {
        background-color: #830;
        border: 1px solid white;
      }

      ul.tabs a:hover {
        background-color: #369;
      }

      fieldset {
        margin-left: 1em;
        padding-bottom: 0;
      }

      div#photo_upload_wrap {
        display: inline;
      }

      div#photo_upload_wrap {
        display: inline;
      }

      tr.core td {
        border-bottom: 1px solid black;
      }

      a {
        text-decoration: none;
      }

      a:hover {
        text-decoration: underline;
      }

      span.understate {
        font-size: 70%;
        font-style: italic;
      }

      a.allowed {
        color: green;
        font-size: 110%;
      }

      a.denied {
        color: red;
        font-size: 90%;
      }

      ul#permissions ul {
        margin-left: -1em;
        list-style-type: none;
      }
</style>
    <?= html::script("lib/jquery.js") ?>
    <?= html::script("lib/jquery.cookie.js") ?>
    <?= html::script("lib/jquery.MultiFile.js") ?>
    <?= rearrange_block::head(null) ?>
  </head>
  <body>
    <div class="outer">
      <center>
        <img src="http://www.gallery2.org/gallery2.png"/>
      </center>
      <div class="inner">
        <h1>Gallery3 Scaffold</h1>
        <p>
          This is
          a <b><a href="http://www.google.com/images?q=scaffold">scaffold</a></b>:
          a <i>temporary structure built to support the developers as
          they create the real product</i>. As we flesh out Gallery 3,
          we'll make it possible for you to peer inside and see the
          application taking shape.  Eventually, this page will go
          away and you'll start in the application itself.  In the
          meantime, here are some useful links to get you started.
        </p>

        <ul class="tabs">
          <li><a href="javascript:show('config')">Configuration</a></li>
          <li><a href="javascript:show('actions')">Actions</a></li>
          <li><a href="javascript:show('access')">Access</a></li>
          <li><a href="javascript:show('info')">Info</a></li>
          <li><a href="javascript:show('benchmarks')">Benchmarks</a></li>
          <li><a href="javascript:show('docs')">Docs</a></li>
        </ul>

        <div id="activities">
          <script>
            show = function(show1, show2, immediate) {
              if (!show1) {
                show1 = "configuration";
              } else if (show1 == "access" && !show2) {
                show2 = "access_users";
              }
              var acts = $("div.activity");
              for (var i = 0; i < acts.length; i++) {
                act = acts[i];
                if (act.id != show1 && act.id != show2) {
                  if (immediate) {
                    $("#" + act.id).hide();
                  } else {
                    $("#" + act.id).slideUp();
                  }
                } else {
                  if (immediate) {
                    $("#" + act.id).show();
                  } else {
                    $("#" + act.id).slideDown();
                  }
                }
              }
              $.cookie("show1", show1);
              $.cookie("show2", show2);
            }
            $(document).ready(function(){
              show($.cookie("show1"), $.cookie("show2"), true);
              $("#photo_upload").MultiFile();
            });
          </script>

          <div id="config" class="activity">
            <?= $syscheck ?>
          </div>

          <div id="actions" class="activity">
            <p>
              <?= html::anchor("albums/1", "Browse Gallery") ?>
              <i>(<?= $album_count ?> albums, <?= $photo_count ?> photos, <?= $comment_count ?> comments, <?= $tag_count ?> tags)</i>
            </p>
            <p>
              add: [
              <? foreach (array(1, 10, 50, 100, 500, 1000) as $count): ?>
                <?= html::anchor("welcome/add_albums_and_photos/$count", "$count") ?>
              <? endforeach ?>
              ] photos and albums
            </p>
            <p>
              add: [
              <? foreach (array(1, 10, 50, 100, 500, 1000) as $count): ?>
                <?= html::anchor("welcome/add_albums_and_photos/$count/album", "$count") ?>
              <? endforeach ?>
              ] albums only
            </p>
            <p>
              add: [
              <? foreach (array(1, 10, 50, 100, 500, 1000) as $count): ?>
                <?= html::anchor("welcome/add_comments/$count", "$count") ?>
              <? endforeach ?>
              ] comments
            </p>
            <p>
              add: [
              <? foreach (array(1, 10, 50, 100, 500, 1000) as $count): ?>
                <?= html::anchor("welcome/add_tags/$count", "$count") ?>
              <? endforeach ?>
              ] tags
            </p>
            <fieldset>
              <legend>Photos</legend>
              <form method="post" action="<?= url::site("albums/1") ?>" enctype="multipart/form-data">
                <input type="submit" value="upload"/>
                <input id="photo_upload" name="file[]" type="file"/>
                <input type="hidden" name="type" value="photo"/>
              </form>
            </fieldset>
            <fieldset>
              <legend>Server Side Photos</legend>
              <form method="post" action="<?= url::site("welcome/add_photos") ?>">
                <input type="submit" value="upload"/>
                <input type="text" name="path" size="70"
                       value="<?= cookie::get("add_photos_path") ?>" />
              </form>
            </fieldset>
            <fieldset>
              <legend>Albums</legend>
              <form method="post" action="<?= url::site("albums/1") ?>">
                <input type="submit" value="create"/>
                <input type="text" name="name"/>
                <input type="hidden" name="type" value="album"/>
              </form>
            </fieldset>
            <fieldset>
              <legend>Rearrange</legend>
              <?= $rearrange_html ?>
            </fieldset>
          </div>

          <div id="access" class="activity">
            <ul class="tabs">
              <li><a href="javascript:show('access', 'access_users')">Users</a></li>
              <li><a href="javascript:show('access', 'access_groups')">Groups</a></li>
              <li><a href="javascript:show('access', 'access_permissions')">Permissions</a></li>
            </ul>

            <div id="access_users" class="activity">
              <ul>
                <? foreach ($users as $user): ?>
                <li>
                  <?= $user->name ?>
                  <? if ($user->id != user::ADMIN): ?>
                  <?= html::anchor("welcome/delete_user/$user->id", "[x]") ?>
                  <? endif ?>
                  <ul>
                    <? foreach ($user->groups as $group): ?>
                    <li>
                      <?= $group->name ?>
                      <? if ($group->id != group::REGISTERED_USERS): ?>
                      <?= html::anchor("welcome/remove_from_group/$group->id/$user->id", "[x]") ?>
                      <? endif ?>
                    </li>
                    <? endforeach ?>
                    <li>
                      <form method="post" action="<?= url::site("welcome/add_to_group/$user->id") ?>">
                        <input type="text" name="group_name"/>
                      </form>
                    </li>
                  </ul>
                </li>
                <? endforeach ?>
              </ul>
              <fieldset>
                <legend>Add User</legend>
                <form method="post" action="<?= url::site("welcome/add_user") ?>">
                  <input name="user_name" type="text"/>
                  <input type="submit" value="create"/>
                  <input type="checkbox" name="admin">Admin</input>
                </form>
              </fieldset>
            </div>

            <div id="access_groups" class="activity">
              <ul>
                <? foreach ($groups as $group): ?>
                <li>
                  <?= $group->name ?>
                  <? if ($group->id != group::REGISTERED_USERS): ?>
                  <?= html::anchor("welcome/delete_group/$group->id", "[x]") ?>
                  <? endif ?>
                </li>
                <? endforeach ?>
              </ul>
              <fieldset>
                <legend>Add Group</legend>
                <form method="post" action="<?= url::site("welcome/add_group") ?>">
                  <input name="group_name" type="text"/>
                  <input type="submit" value="create"/>
                </form>
              </fieldset>
            </div>

            <div id="access_permissions" class="activity">
              <? if ($album_tree): ?>
              <? $stack = array(1); // hardcoded to the root album ?>
              <? while ($stack): ?>
              <? $current = array_pop($stack); ?>
              <? if ($current != "CLOSE"): ?>
              <? $current = $album_tree[$current]; ?>
              <ul id="permissions">
                <li>
                  <span class="understate">(<?= $current->album->id ?>)</span>
                  <?= html::anchor("albums/{$current->album->id}", $current->album->title) ?>
                  &raquo;
                  <? foreach (array("view", "edit") as $perm): ?>
                  <? if (access::can(group::EVERYBODY, $perm, $current->album->id)): ?>
                  <?= html::anchor("welcome/deny_perm/0/$perm/{$current->album->id}", strtoupper($perm), array("class" => "allowed")) ?>
                  <? else: ?>
                  <?= html::anchor("welcome/add_perm/0/$perm/{$current->album->id}", strtolower($perm), array("class" => "denied")) ?>
                  <? endif ?>
                  <? endforeach ?>
                  <? if ($current->album->id != 1): ?>
                  <span class="understate">
                    (<?= html::anchor("welcome/reset_all_perms/0/{$current->album->id}", "reset") ?>)
                  </span>
                  <? endif; ?>
                  <? $stack[] = "CLOSE"; ?>
                  <? if ($current->children): ?>
                  <? $stack = array_merge($stack, $current->children) ?>
                  <? endif ?>
                  <? else: ?>
                </li>
              </ul>
              <? endif ?>
              <? endwhile ?>
              <? endif ?>
            </div>
          </div>

          <div id="info" class="activity">
            <ul>
              <li> <?= html::anchor("welcome/mptt?type=text", "MPTT tree (text)") ?> </li>
              <li>
                <?= html::anchor("welcome/mptt", "MPTT tree (graph)") ?>
                <i>(requires /usr/bin/dot from the graphviz package)</i>
              </li>
              <? if ($deepest_photo): ?>
              <li>
                <?= html::anchor("photos/{$deepest_photo->id}", "Deepest photo") ?>
                <i>(<?= $deepest_photo->level ?> levels deep)</i>
              </li>
              <? endif ?>
              <? if ($most_tagged): ?>
              <li>
                <?= html::anchor("items/{$most_tagged->id}", "Most tagged item") ?>
                <i>(<?= $most_tagged->count ?> tags)</i>
              </li>
              <? endif ?>
              <li> Profiling:
                <? if (Session::instance()->get("use_profiler", false)): ?>
                <b>on</b> <?= html::anchor("welcome/session/profiler?value=0", "off") ?>
                <? else: ?>
                <?= html::anchor("welcome/session/profiler?value=1", "on") ?> <b>off</b>
                <? endif ?>
              </li>
              <li> Debug:
                <? if (Session::instance()->get("debug", false)): ?>
                <b>on</b> <?= html::anchor("welcome/session/debug?value=0", "off") ?>
                <? else: ?>
                <?= html::anchor("welcome/session/debug?value=1", "on") ?> <b>off</b>
                <? endif ?>
              </li>
            </ul>
          </div>

          <div id="benchmarks" class="activity">
            <ul>
              <li>
                <?= html::anchor("welcome/i18n/build", "Make Translation") ?>
              </li>
              <li>
                <?= html::anchor("welcome/i18n/run", "Run Translation") ?>
              </li>
            </ul>
          </div>

          <div id="docs" class="activity">
            <ul>
              <li>
                <a href="http://codex.gallery2.org/Gallery3:Features">Gallery3: Features</a>
              </li>
              <li>
                <a href="http://gallery.svn.sourceforge.net/viewvc/gallery/trunk/eval/gx/ui/HTML/index.html">Mockups</a>
              </li>
              <li>
                <a href="http://www.nabble.com/Rough-Gallery-3-time-line-td20240153.html">Rough Timeline</a> (as of Oct 29, 2008)
              </li>
              <li>
                <a href="http://codex.gallery2.org/Gallery3:About">Gallery3: About Page</a>
              </li>
              <li>
                <a href="http://codex.gallery2.org/Gallery3:Coding_Standards">Gallery3: Coding Standards</a>
              </li>
              <li>
                <a href="http://docs.kohanaphp.com/">Kohana Documentation</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
