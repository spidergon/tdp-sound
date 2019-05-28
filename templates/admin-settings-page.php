<div class="wrap">
  <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
  <form action="options.php" method="post">
    <?php
      // output security fields for the registered setting "vigilant_sound"
      settings_fields('vigilant_sound');
      // output setting sections and their fields
      // (sections are registered for "vigilant_sound", each field is registered to a specific section)
      do_settings_sections('vigilant_sound');
      // output save settings button
      submit_button('Save');
    ?>
  </form>
</div>
