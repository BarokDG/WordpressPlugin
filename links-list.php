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
    $socialsPageHook = add_submenu_page( "linkslistsettings", "Links List Social Icons", "Social icons", "manage_options", "linkslistsocialiconsettings", array($this, 'socialSettingsHTML'));
    add_action("load-{$socialsPageHook}", array($this, "socialsPageAssets"));
  }

  function socialsPageAssets() {
    wp_enqueue_script("socials-js", plugins_url("/js/llpsocials.js", __FILE__ ), array(), '', true);
    wp_enqueue_style("socialsPageStyles", plugins_url("css/llpsocials.css", __FILE__));
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

  function socialSettingsHTML() { ?>
    <div class="wrap">
      <h1>Social icons</h1>
      <form action="options.php" method="POST">
        <?php
          settings_fields("linkslistpluginsocials");
          do_settings_sections("linkslist-socials-page");
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

    // Announcements
    add_settings_field('llp_announcement', "Announcemnet", array($this, 'announcementHTML'), 'linkslist-settings-page', 'llp_first_section');
    register_setting("linkslistplugin", "llp_announcement", array('sanitize_callback' => 'sanitize_text_field'));
    register_setting("linkslistplugin", "llp_show_announcement", array('sanitize_callback' => 'sanitize_text_field'));
    
    // Second section
    add_settings_section( 'llp_second_section', null, null, 'linkslist-settings-page');

    // First link
    add_settings_field( 'llp_link_1_title', "Link Title", array($this, 'links_listHTML'), 'linkslist-settings-page', 'llp_second_section');
    register_setting('linkslistplugin', 'llp_link_1_title',  array('sanitize_callback' => 'sanitize_text_field'));
    register_setting('linkslistplugin', 'llp_link_1_url', array('sanitize_callback' => 'sanitize_text_field'));

    // Social Icons options page
    add_settings_section( "llp_socials_section", null, null, "linkslist-socials-page");

    // Social Icons
    add_settings_field('llp_social_icons', "Social Icons", array($this, 'socialIconsHTML'), 'linkslist-socials-page', 'llp_socials_section');
    register_setting('linkslistpluginsocials', "llp_facebook_url", array('sanitze_callback' => 'sanitize_text_field'));
    register_setting('linkslistpluginsocials', "llp_twitter_url", array('sanitze_callback' => 'sanitize_text_field'));
    register_setting('linkslistpluginsocials', "llp_instagram_url", array('sanitze_callback' => 'sanitize_text_field'));
    register_setting('linkslistpluginsocials', "llp_codepen_url", array('sanitze_callback' => 'sanitize_text_field'));
    register_setting('linkslistpluginsocials', "llp_email_url", array('sanitze_callback' => 'sanitize_text_field'));
    register_setting('linkslistpluginsocials', "llp_website_url", array('sanitze_callback' => 'sanitize_text_field'));
  }

  function socialIconsHTML() {?>
    <div class="llp-inner-input-container">
      <input type="text" name="llp_facebook_url" value=<?= get_option('llp_facebook_url') ?>>
      <label for="llp_facebook_url">Facebook</label>
    </div>
    <div class="llp-inner-input-container">
      <input type="text" name="llp_twitter_url" value=<?= get_option('llp_twitter_url') ?>>
      <label for="llp_twitter_url">Twitter</label>
    </div>
    <div class="llp-inner-input-container">
      <input type="text" name="llp_instagram_url" value=<?= get_option('llp_instagram_url') ?>>
      <label for="llp_instagram_url">Instagram</label>
    </div>
    <div class="llp-inner-input-container">
      <input type="text" name="llp_email_url" value=<?= get_option('llp_email_url') ?>>
      <label for="llp_email_url">Email</label>
    </div>
    <div class="llp-inner-input-container">
      <input type="text" name="llp_codepen_url"value=<?= get_option('llp_codepen_url') ?>>
      <label for="llp_codepen_url">Codepen</label>
    </div>
    <div class="llp-inner-input-container">
      <input type="text" name="llp_website_url" value=<?= get_option('llp_website_url') ?>>
      <label for="llp_website_url">Website</label>
    </div>
  
  <?php }

  function announcementHTML() { ?>
    <textarea type="text" name="llp_announcement" id=""><?= esc_html(get_option("llp_announcement")) ?></textarea>

    <label for="llp_show_announcemnet">Show banner</label>
    <input type="checkbox" name="llp_show_announcement" id="" <?= get_option("llp_show_announcement") ? "checked" : "" ?>>
  <?php }

  function links_listHTML() { ?>
    <input type="text" name="llp_link_1_title" value="<?= esc_attr(get_option('llp_link_1_title')) ?>">
    <input type="text" name="llp_link_1_url" value="<?= esc_attr(esc_url(get_option('llp_link_1_url'))) ?>">
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
    <input type="text" name="llp_profile_title" value="<?= esc_attr(get_option('llp_profile_title')) ?>">
  <?php }

  function descriptionHTML() { ?>
    <textarea type="text" name="llp_description" placeholder="Bio/description"><?= esc_html(get_option('llp_description')) ?></textarea>
  <?php }

  public static function Output() {

    $image_attributes = wp_get_attachment_image_src(get_option('llp_profile_picture'), 'full');
    $src = $image_attributes[0] ?? '';

    $background_image = wp_get_attachment_image_src(get_option('llp_background_image'), 'full');
    $bg_src = $background_image[0] ?? '';
    
    ?>
    <div class="linkslist-main" style="background-image: url(' . $bg_src . ')">
      <div class="linkslist-banner"><?php
        if (get_option( "llp_show_announcement")) {
          echo  get_option("llp_announcement"); 
        }
      ?></div>
      <img src="<?= $src ?>" alt="" />
      <h3><?= get_option('llp_profile_title') ?></h3>
      <p><?= get_option('llp_description') ?></p>

      <a href="<?= get_option('llp_link_1_url') ?>"><?= get_option('llp_link_1_title') ?></a>
    </div>
  <?php }
}

new LinkList();