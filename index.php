<?php
/**
 * Plugin Name:       Vigilant Sound
 * Description:       A plugin to add and manage sounds.
 * Version:           0.1.0
 * Author:            Christopher Servius
 * Author URI:        https://toucandigitalpartner.com
 * License:           GPLv2 or later
 * Text Domain:       vigilant_sound
 */

class Vigilant_Sound {

  /**
   * Initializes the plugin.
   *
   * To keep the initialization fast, only add filter and action
   * hooks in the constructor.
   */
  public function __construct() {
    add_shortcode( 'tdp-sound-playlist', array( $this, 'render_playist_shortcode' ) );
    add_action( 'wp_print_footer_scripts', array( $this, 'add_scripts_to_footer' ) );
    add_action( 'admin_init', array( $this, 'admin_settings_init' ) );
    add_action( 'admin_menu', array( $this, 'vigilant_sound_options_page') );
    add_action( 'wp_footer', array( $this, 'inject_code_to_footer' ) );
  }

  /**
   * Render the playist shortcode.
   *
   * @param  array   $attributes  Shortcode attributes.
   * @param  string  $content     The text content for shortcode. Not used.
   *
   * @return string  The shortcode output
   */
  public function render_playist_shortcode( $attributes, $content = null ) {
    return $this->get_template_html( 'playlist', $attributes );
  }

  /** Add the JavaScript files at the end of the page. */
  public function add_scripts_to_footer() {
    echo "<script src='". plugin_dir_url( __FILE__ ) . "js/vendor/howler.min.js'></script>";
    echo "<script src='". plugin_dir_url( __FILE__ ) . "build/index.js'></script>";
  }

  /** Admin custom option and settings */
  public function admin_settings_init() {
    // register a new setting for "vigilant_sound" page
    register_setting( 'vigilant_sound', 'vigilant_sound_options' );

    // register a new section in the "vigilant_sound" page
    add_settings_section(
      'vigilant_sound_section_main', // id
      __( 'Manage your sounds.', 'vigilant_sound' ), // title
      array( $this, 'vigilant_sound_section_main_cb'), // callback
      'vigilant_sound' // page
    );

    for ($i = 1; $i <= 3; $i++) {
      // register a new field in the "vigilant_sound_section_main" section, inside the "vigilant_sound" page
      add_settings_field(
        'vigilant_sound_field_sound_' . $i, // id: as of WP 4.6 this value is used only internally
        // use $args' label_for to populate the id inside the callback
        __( 'Sound #' . $i, 'vigilant_sound' ), // title
        array( $this, 'vigilant_sound_field_sound_cb'), // callback
        'vigilant_sound', // page
        'vigilant_sound_section_main', // section
        [ // args
          'label_for' => 'vigilant_sound_field_sound_' . $i,
          'class' => 'vigilant_sound_row',
          'vigilant_sound_custom_data' => 'custom',
        ]
      );
    }
  }

  /**
   * custom option and settings:
   * callback functions
   */

  // main section cb

  // section callbacks can accept an $args parameter, which is an array.
  // $args have the following keys defined: title, id, callback.
  // the values are defined at the add_settings_section() function.
  public function vigilant_sound_section_main_cb( $args ) {
    // echo '<p id="' . esc_attr( $args['id'] ) . '">' . esc_html_e( 'Follow the white rabbit.', 'vigilant_sound' ) . '</p>';
    echo '<p id="' . esc_attr( $args['id'] ) . '">' . esc_html_e( 'To add the playlist on a page, use the following shortcode:', 'vigilant_sound' ) . '<input type="text" value="[tdp-sound-playlist]" readonly></p>';
  }

  // pill field cb

  // field callbacks can accept an $args parameter, which is an array.
  // $args is defined at the add_settings_field() function.
  // wordpress has magic interaction with the following keys: label_for, class.
  // the "label_for" key value is used for the "for" attribute of the <label>.
  // the "class" key value is used for the "class" attribute of the <tr> containing the field.
  // you can add custom key value pairs to be used inside your callbacks.
  public function vigilant_sound_field_sound_cb( $args ) {
    // get the value of the setting we've registered with register_setting()
    $options = get_option( 'vigilant_sound_options' );
    // output the field
    ?>
      <input
        id="<?php echo esc_attr( $args['label_for'] ); ?>"
        type="text"
        value="<?php echo esc_attr( $options[ $args['label_for']] ); ?>"
        data-custom="<?php echo esc_attr( $args['vigilant_sound_custom_data'] ); ?>"
        name="vigilant_sound_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
        style="width: 50%;"
      >
    <?php
  }

  /** Render the admin settings page. */
  public function render_admin_settings_page() {
    if ( !current_user_can('manage_options') ) return; // check user capabilities
    // add error/update messages

    // check if the user have submitted the settings
    // wordpress will add the "settings-updated" $_GET parameter to the url
    if ( isset( $_GET['settings-updated'] ) ) {
      // add settings saved message with the class of "updated"
      add_settings_error( 'vigilant_sound_messages', 'vigilant_sound_message', __( 'Sounds Saved', 'vigilant_sound' ), 'updated' );
    }

    // show error/update messages
    settings_errors( 'vigilant_sound_messages' );

    echo $this->get_template_html( 'admin-settings-page' );
  }

  /**  */
  public function vigilant_sound_options_page() {
    global $_wp_last_object_menu;

    add_menu_page(
      'TDP Sound',
      'Sound',
      'manage_options',
      'vigilant_sound',
      array( $this, 'render_admin_settings_page' ),
      'dashicons-format-audio',
      ++$_wp_last_object_menu
    );
  }

  public function inject_code_to_footer() {
    $option = get_option( 'vigilant_sound_options' );
    ?>
      <input type="hidden" class="sound-options" value='<?php echo esc_attr(json_encode($option)); ?>'>
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

    do_action( 'vigilant_sound_before_' . $template_name );

    require( 'templates/' . $template_name . '.php');

    do_action( 'vigilant_sound_after_' . $template_name );

    $html = ob_get_contents();
    ob_end_clean();

    return $html;
  }

}

// Initialize the plugin
$vigilant_sound = new Vigilant_Sound();
