<?php

/**
 *
 * @wordpress-plugin
 * Plugin Name:       Links List
 * Description:       The plugin helps to point followers and subscribers to social profiles, eCommerce store, or content you want to share.
 * Version:           1.0.0
 * Author:            Barok Dagim
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-link-list-activator.php
 */

function activate_link_list() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-link-list-activator.php';
	Link_List_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-link-list-deactivator.php
 */
function deactivate_link_list() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-link-list-deactivator.php';
	Link_List_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_link_list' );
register_deactivation_hook( __FILE__, 'deactivate_link_list' );

/**
 * Main plugin file
 */

class LinkList {
  public function __construct() {

    add_action('template_include', [$this, 'linklist_template']);
    add_action('admin_menu', array($this, 'adminMenu'));
    add_action('admin_init', array($this, 'settings'));
    add_action( 'admin_enqueue_scripts', 'wp_enqueue_media' );
    add_action('admin_enqueue_scripts', array($this, 'media_library_script'));
  }
  
  function media_library_script() {
    wp_enqueue_script("media-js", plugins_url("/js/media.js", __FILE__ ), array('jquery'), '', true);
  }

  function linklist_template($template) {

    global $wp;

    $current_slug = $wp->request;

    $custom_template = WP_PLUGIN_DIR . '/plugin-name/linkslist-template.php';

    if ($current_slug == 'links' and $custom_template != '') {
      status_header(200);
      return $custom_template;
    }

    return $template;
  }

  function adminMenu() {

    add_menu_page('Links List Settings', 'Links List', 'manage_options', 'linkslistsettings', array($this, 'settingsHTML'), 'dashicons-smiley', 100);
  }
	
  function settingsHTML() { ?>
    <div class="wrap">
      <h1>Links list settings</h1>
      <form action="options.php" method="POST">
        <?php 
          settings_fields('linkslistplugin');
          do_settings_sections('linkslist-settings-page');
          submit_button();
        ?>
      </form>
    </div>
  <?php }

  function settings() {

    add_settings_section('llp_first_section', null, null, 'linkslist-settings-page');

    add_settings_field('llp_profile_picture', 'Upload Image', array($this, 'profile_picHTML'), 'linkslist-settings-page', 'llp_first_section');  
    register_setting("linkslistplugin", "llp_profile_picture");

    add_settings_field('llp_profile_title', 'Profile title', array($this, 'profile_titleHTML'), 'linkslist-settings-page', 'llp_first_section');
    register_setting( 'linkslistplugin', 'llp_profile_title', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Profile Title'));
    
    add_settings_field('llp_description', 'Short description', array($this, 'descriptionHTML'), 'linkslist-settings-page', 'llp_first_section');
    register_setting('linkslistplugin', 'llp_description', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'You will see your bio/description here'));
  }

  function profile_picHTML() { 
    $options = get_option('llp_profile_picture');
    $default_image = 'https://www.placehold.it/115x115';
 
    if (!empty($options)) {
        $image_attributes = wp_get_attachment_image_src($options, 'full');
        $src = $image_attributes[0];
        $value = $options;
    } else {
        $src = $default_image;
        $value = '';
    }
 
    // Print HTML field
    echo '
        <div class="upload" style="max-width:400px;">
            <img data-src="' . $default_image . '" src="' . $src . '" style="max-width:100%; height:auto;" />
            <div>
                <input type="hidden" name="llp_profile_picture" id="llp_profile_picture" value="' . $value . '" />
                <button type="submit" class="upload_image_button button">' . __('Upload', 'igsosd') . '</button>
                <button type="submit" class="remove_image_button button">&times;</button>
            </div>
        </div>
    ';
  }

  function profile_titleHTML() { ?>
    <input type="text" name="llp_profile_title" placeholder="<?php echo esc_attr(get_option('llp_profile_title')) ?>">
  <?php }

  function descriptionHTML() { ?>
    <input type="text" name="llp_description">
  <?php }

  public static function Foo() {

    $output = '<h3>' . esc_html(get_option('llp_profile_title')) . '</h3><p>';

    return $output;
  }

  public static function Bar() {

    $output = '<img src="' . get_option('llp_profile_picture'). '" alt="" />';

    return $output;
  }

}

new LinkList();