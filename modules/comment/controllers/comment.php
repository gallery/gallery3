<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
class Comment_Controller extends Controller {
  function add($count) {
    $photos = ORM::factory("item")->where("type", "photo")->find_all()->as_array();

    $sample_text = "Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium
      doloremque laudantium, totam rem aperiam eaque ipsa, quae ab illo inventore veritatis et quasi
      architecto beatae vitae dicta sunt, explicabo. Nemo enim ipsam voluptatem, quia voluptas
      sit, aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos, qui ratione
      voluptatem sequi nesciunt, neque porro quisquam est, qui dolorem ipsum, quia dolor sit,
      amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt, ut
      labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum
      exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi
      consequatur? Quis autem vel eum iure reprehenderit, qui in ea voluptate velit esse, quam
      nihil molestiae consequatur, vel illum, qui dolorem eum fugiat, quo voluptas nulla pariatur?
      At vero eos et accusamus et iusto odio dignissimos ducimus, qui blanditiis praesentium
      voluptatum deleniti atque corrupti, quos dolores et quas molestias excepturi sint, obcaecati
      cupiditate non provident, similique sunt in culpa, qui officia deserunt mollitia animi, id
      est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam
      libero tempore, cum soluta nobis est eligendi optio, cumque nihil impedit, quo minus id,
      quod maxime placeat, facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.
      Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet,
      ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic
      tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut
      perferendis doloribus asperiores repellat.";
    $seconds_in_a_year = 31556926;

    if (empty($photos)) {
      url::redirect("welcome");
    }

    for ($i = 0; $i < $count; $i++) {
      $photo = $photos[array_rand($photos)];
      comment::create("John Doe", "johndoe@example.com",
        substr($sample_text, 0, rand(30, strlen($sample_text))), $photo->id,
        time() - rand(0, 2 * $seconds_in_a_year));
    }

    url::redirect("welcome");
  }
}
