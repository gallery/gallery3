<div class="gBlock">
  <a href="" class="gClose">X</a>
  <h2>User Administration</h2>
  <div class="gBlockContent">
    <p>These are the users in your system</p>
    <table>
    <? foreach ($users as $i => $user): ?>
      <tr><td><?= $user->name ?></td></tr>
    <? endforeach ?>
    </table>
  </div>
</div>
