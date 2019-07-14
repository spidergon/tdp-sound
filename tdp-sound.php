<?php
/*
Plugin Name:       TDP Sound
Description:       A plugin to add and manage sounds.
Version:           0.1.0
Author:            Christopher Servius
Author URI:        https://toucandigitalpartner.com
Text Domain:       tdp_sound
License:           GPLv3

This plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

This plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this plugin. If not, see https://github.com/spidergon/tdp-sound/blob/master/LICENSE.
*/

/** TDP Sound main plugin file. */
class TDP_Sound {

  /**
   * Initializes the plugin.
   *
   * To keep the initialization fast, only add filter and action
   * hooks in the constructor.
   */
  public function __construct() {
    add_shortcode( 'tdp-sound-playlist', array( $this, 'render_playlist_shortcode' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'add_css' ) );
    add_action( 'wp_print_footer_scripts', array( $this, 'add_scripts_to_footer' ) );
    add_action( 'wp_footer', array( $this, 'inject_code_to_footer' ) );
    if ( is_admin() ) {
      add_action( 'admin_menu', array( $this, 'admin_add_menu_page') );
      add_action( 'admin_init', array( $this, 'admin_settings_init' ) );
    }
  }

  /**
   * Render the playist shortcode.
   *
   * @param  array   $attributes  Shortcode attributes.
   * @param  string  $content     The text content for shortcode. Not used.
   *
   * @return string  The shortcode output
   */
  public function render_playlist_shortcode( $attributes, $content = null ) {
    return $this->get_template_html( 'playlist', $attributes );
  }

  /** Add the main css file. */
  public function add_css() {
    $plugin_url = plugin_dir_url( __FILE__ );
    wp_enqueue_style( 'tdp-sound-soudcloud', $plugin_url . 'style.css' );
  }

  /** Add the JavaScript files to the footer. */
  public function add_scripts_to_footer() {
    echo "<script src='". plugin_dir_url( __FILE__ ) . "js/vendor/howler.min.js'></script>";
    echo "<script src='". plugin_dir_url( __FILE__ ) . "build/index.js'></script>";
  }

  /** Inject custom code to the footer. */
  public function inject_code_to_footer() {
    $option = get_option( 'tdp_sound_options' );
    ?>
      <input type="hidden" class="sound-options" value='<?php echo esc_attr(json_encode($option)); ?>'>
    <?php
  }

  /** Add the menu to the admin menu. */
  public function admin_add_menu_page() {
    global $_wp_last_object_menu;
    add_menu_page(
      'TDP Sound',
      'Sound',
      'manage_options',
      'tdp_sound',
      array( $this, 'render_admin_settings_page' ),
      'dashicons-format-audio',
      ++$_wp_last_object_menu
    );
  }

  /** Render the admin settings page. */
  public function render_admin_settings_page() {
    if ( !current_user_can('manage_options') ) return; // check user capabilities
    // check if the user have submitted the settings
    // wordpress will add the "settings-updated" $_GET parameter to the url
    if ( isset( $_GET['settings-updated'] ) ) {
      // add settings saved message with the class of "updated"
      add_settings_error( 'tdp_sound_messages', 'tdp_sound_message', __( 'Sounds Saved', 'tdp_sound' ), 'updated' );
    }
    // show error/update messages
    settings_errors( 'tdp_sound_messages' );
    // render the page
    echo $this->get_template_html( 'admin-settings-page' );
  }

  /** Admin custom option and settings */
  public function admin_settings_init() {
    // register a new setting for "tdp_sound" page
    register_setting( 'tdp_sound', 'tdp_sound_options' );

    // register a new section in the "tdp_sound" page
    add_settings_section(
      'tdp_sound_section_main', // id
      __( 'Manage your sounds.', 'tdp_sound' ), // title
      array( $this, 'tdp_sound_section_main_cb'), // callback
      'tdp_sound' // page
    );

    for ($i = 1; $i <= 10; $i++) {
      // register a new field in the "tdp_sound_section_main" section, inside the "tdp_sound" page
      add_settings_field(
        'tdp_sound_field_sound_' . $i, // id: as of WP 4.6 this value is used only internally
        // use $args' label_for to populate the id inside the callback
        __( 'Sound #' . $i, 'tdp_sound' ), // title
        array( $this, 'tdp_sound_field_sound_cb'), // callback
        'tdp_sound', // page
        'tdp_sound_section_main', // section
        [ // args
          'label_for' => 'tdp_sound_field_sound_' . $i,
          'class' => 'tdp_sound_row',
          'tdp_sound_custom_data' => 'custom',
        ]
      );
    }
  }

  /** Render main section. */
  // it can accept an $args parameter, which is an array.
  // $args have the following keys defined: title, id, callback.
  // the values are defined at the add_settings_section() function.
  public function tdp_sound_section_main_cb( $args ) {
    echo '<div id="' . esc_attr( $args['id'] ) . '"><p>';
    echo esc_html_e( 'To add the playlist on a page, use the following shortcode:', 'tdp_sound' );
    echo '&nbsp;<strong>[tdp-sound-playlist]</strong></p><p>';
    echo esc_html_e( 'Add your sound urls below:', 'tdp_sound' ) . '</p></div>';
  }

  /** Render a field. */
  // it can accept an $args parameter, which is an array.
  // $args is defined at the add_settings_field() function.
  // wordpress has magic interaction with the following keys: label_for, class.
  // the "label_for" key value is used for the "for" attribute of the <label>.
  // the "class" key value is used for the "class" attribute of the <tr> containing the field.
  // you can add custom key value pairs to be used inside your callbacks.
  public function tdp_sound_field_sound_cb( $args ) {
    // get the value of the setting we've registered with register_setting()
    $options = get_option( 'tdp_sound_options' );
    // output the field
    ?>
      <input
        id="<?php echo esc_attr( $args['label_for'] ); ?>"
        type="text"
        value="<?php echo esc_attr( $options[ $args['label_for']] ); ?>"
        data-custom="<?php echo esc_attr( $args['tdp_sound_custom_data'] ); ?>"
        name="tdp_sound_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
        style="width: 100%;"
      >
    <?php
  }

  /**
   * Renders the contents of the given template to a string and returns it.
   *
   * @param string $template_name The name of the template to render (without .php)
   * @param array  $attributes    The PHP variables for the template
   *
   * @return string               The contents of the template.
   */
  private function get_template_html( $template_name, $attributes = null ) {
    if ( ! $attributes ) $attributes = array();

    ob_start();

    do_action( 'tdp_sound_before_' . $template_name );

    require( 'templates/' . $template_name . '.php');

    do_action( 'tdp_sound_after_' . $template_name );

    $html = ob_get_contents();
    ob_end_clean();

    return $html;
  }
}

// Initialize the plugin
$tdp_sound = new TDP_Sound();
