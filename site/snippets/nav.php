<nav class="languages">
  <?php foreach($kirby->languages() as $language): ?>
    <a
      <?php e($kirby->language() == $language, ' class="active"') ?>
      href="<?php echo $language->url() ?>"
      hreflang="<?php echo $language->code() ?>"
    >
      <em>
        (<?php echo html($language->code()) ?>)
        <?php echo html($language->name()) ?>
      </em>
    </a>
  <?php endforeach ?>
</nav>
