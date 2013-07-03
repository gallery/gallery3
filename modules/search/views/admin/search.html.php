<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-search-admin" class="g-block ui-helper-clearfix">
  <h1> <?= t("Search settings") ?> </h1>
  <p>
    <?= t("Gallery's search finds items based on data like their names, titles, descriptions, tags, and comments. Internally, it uses MySQL's <a href=%url>full-text search</a>.",
          array("url" => "http://dev.mysql.com/doc/refman/5.0/en/fulltext-search.html")) ?>
  </p>
  <h3> <?= t("Wildcards") ?> </h3>
  <p>
    <?= t("In addition to searching for exact matches, Gallery can add wildcards to the search terms. There are three modes:") ?><br/>
    <table>
      <tr>
        <td><b><?= t("Wildcard mode") ?></b></td>
        <td><b><?= t("Description") ?></b></td>
        <td><b><?= t("<i>%q</i> in search box becomes...",
                     array("q" => 'apple bananas "cat dogs" entries farm*')) ?></b></td>
      </tr>
      <tr>
        <td><?= t("Append wildcards to word stems") ?></td>
        <td><?= t("Remove any singular/plural characters at the end of the words, then add wildcards. This is useful for English-language searches.") ?></td>
        <td><?= str_replace(" ", "&nbsp;", 'apple* banana* "cat dogs" entr* farm*') ?></td>
      </tr>
      <tr>
        <td><?= t("Append wildcards") ?></td>
        <td><?= t("Add wildcards to the unmodified search words. This is useful if the method above causes confusion with your users' languages.") ?></td>
        <td><?= str_replace(" ", "&nbsp;", 'apple* bananas* "cat dogs" entries* farm*') ?></td>
      </tr>
      <tr>
        <td><?= t("Do not append wildcards") ?></td>
        <td><?= t("Leave the terms as-is. This is useful for ideographic languages (e.g. Chinese).") ?></td>
        <td><?= str_replace(" ", "&nbsp;", 'apple bananas "cat dogs" entries farm*') ?></td>
      </tr>
    </table>
  </p>
  <h3> <?= t("Short search fix") ?> </h3>
  <p>
    <?= t("Many MySQL installations can't search for short words. The best solution is to change MySQL's <b>ft_min_word_len</b> variable, but shared hosting accounts typically can't do this.") ?>
    <? if ($ft_min_word_len): ?>
      <?= t("Gallery has detected that on your server, <b>ft_min_word_len = %value</b>.",
            array("value" => $ft_min_word_len)) ?>
    <? endif ?>
  </p>
  <p>
    <?= t("To get around this restriction, Gallery can artificially pad all search terms with a prefix. Internally, this makes short terms look longer without affecting what your site's users see. The default prefix of <b>1Z</b> would decrease the limit by 2 characters. Since <b>ft_min_word_len</b> is commonly 4, this would enable 2-letter searches. You can modify the prefix below.") ?>
  </p>

  <?= $form ?>
</div>
