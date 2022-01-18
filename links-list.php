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
    add_action('template_include', array($this, 'linklist_template'));
    add_action('admin_menu', array($this, 'adminMenu'));
    add_action('admin_init', array($this, 'settings'));
    add_action('admin_enqueue_scripts', 'wp_enqueue_media' );
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
    add_menu_page('Links List Settings', 'Links List', 'manage_options', 'linkslistsettings', array($this, 'settingsHTML'), 'dashicons-admin-links');
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

    add_settings_field('llp_background_image', 'Upload background image', array($this, 'background_imageHTML'), 'linkslist-settings-page', 'llp_first_section');
    register_setting("linkslistplugin", "llp_background_image");

    add_settings_section( 'llp_second_section', null, null, 'linkslist-settings-page');

    add_settings_field( 'llp_link_1_title', "Link Title", array($this, 'links_listHTML'), 'linkslist-settings-page', 'llp_second_section');
    register_setting( 'linkslistplugin', 'llp_link_1_title',  array('sanitize_callback' => 'sanitize_text_field'));

    register_setting( 'linkslistplugin', 'llp_link_1_url', array('sanitize_callback' => 'sanitize_text_field'));
  }

  function links_listHTML() { ?>
    <input type="text" name="llp_link_1_title" value="<?php echo esc_attr(get_option('llp_link_1_title')) ?>">
    <input type="text" name="llp_link_1_url" value="<?php echo esc_attr(esc_url(get_option('llp_link_1_url'))) ?>">
  <?php }

  function background_imageHTML() {
    $options = get_option('llp_background_image');
    $default_image = '';
 
    if (!empty($options)) {
        $image_attributes = wp_get_attachment_image_src($options, 'full');
        $src = $image_attributes[0];
        $value = $options;
    } else {
        $src = $default_image;
        $value = '';
    }
 
    echo '
        <div class="upload" style="max-width:150px;">
            <img data-src="' . $default_image . '" src="' . $src . '" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;" />
            <div>
                <input type="hidden" name="llp_background_image" id="llp_background_image" value="' . $value . '" />
                <button type="submit" class="upload_image_button button">' . __('Upload', 'igsosd') . '</button>
                <button type="submit" class="remove_image_button button">Delete</button>
            </div>
        </div>
    ';

  }

  function profile_picHTML() { 
    $options = get_option('llp_profile_picture');
    $default_image = '';
 
    if (!empty($options)) {
        $image_attributes = wp_get_attachment_image_src($options, 'full');
        $src = $image_attributes[0];
        $value = $options;
    } else {
        $src = $default_image;
        $value = '';
    }
 
    echo '
        <div class="upload" style="max-width:150px;">
            <img data-src="' . $default_image . '" src="' . $src . '" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;" />
            <div>
                <input type="hidden" name="llp_profile_picture" id="llp_profile_picture" value="' . $value . '" />
                <button type="submit" class="upload_image_button button">' . __('Upload', 'igsosd') . '</button>
                <button type="submit" class="remove_image_button button">Delete</button>
            </div>
        </div>
    ';
  }

  function profile_titleHTML() { ?>
    <input type="text" name="llp_profile_title" value="<?php echo esc_attr(get_option('llp_profile_title')) ?>">
  <?php }

  function descriptionHTML() { ?>
    <textarea type="text" name="llp_description" placeholder="Bio/description"><?php echo esc_html(get_option('llp_description')) ?></textarea>
  <?php }

  public static function Output() {

    $image_attributes = wp_get_attachment_image_src(get_option('llp_profile_picture'), 'full');
    $src = $image_attributes[0] ?? '';

    $background_image = wp_get_attachment_image_src(get_option('llp_background_image'), 'full');
    $bg_src = $background_image[0] ?? '';
    
    echo '<div class="linkslist-main" style="background-image: url(' . $bg_src . ')">
            <img src="' . $src . '" alt="" />
            <h3>' . get_option('llp_profile_title') . '</h3>
            <p>' . get_option('llp_description') . '</p>

            <a href="' . get_option('llp_link_1_url') . '">' . get_option('llp_link_1_title') . '</a>
          </div>
    ';
  }
}

new LinkList();