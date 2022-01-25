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
    add_action('admin_enqueue_scripts', array($this, 'load_admin_assets'));    
  }
  
  function load_admin_assets() {
    wp_enqueue_script("media-js", plugins_url("/js/media.js", __FILE__ ), array('jquery'), '', true);
    wp_enqueue_style("admin-css", plugins_url("/css/admin-styles.css", __FILE__));
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
    $mainSettingsHook = add_menu_page('Links List Settings', 'Links List', 'manage_options', 'linkslistsettings', array($this, 'settingsHTML'), 'dashicons-admin-links');
    $socialsPageHook = add_submenu_page( "linkslistsettings", "Links List Social Icons", "Social icons", "manage_options", "linkslistsocialiconsettings", array($this, 'socialSettingsHTML'));
    $appearancePageHook = add_submenu_page( "linkslistsettings", "Links List Appearance", "Appearance", "manage_options", "linkslistappearancesettings", array($this, 'appearanceSettingsHTML'), 1);
    
    add_action("load-{$mainSettingsHook}", array($this, "mainSettingsAssets"));
    add_action("load-{$socialsPageHook}", array($this, "socialsPageAssets"));
    add_action("load-{$appearancePageHook}", array($this, "appearancePageAssets"));
  }

  function mainSettingsAssets() {
    wp_enqueue_script("links-js", plugins_url("/js/dynamicLinks.js", __FILE__ ), array('jquery'), '', true);
    wp_localize_script('links-js', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')), array('we_value' => 1000005));
    
    add_action("wp_ajax_do_something", 'do_something');
  }

  function appearancePageAssets() {
    wp_enqueue_style("appearancePageStyles", plugins_url("css/llpappearance.css", __FILE__));
  }

  function socialsPageAssets() {
    wp_enqueue_script("socials-js", plugins_url("/js/llpSocials.js", __FILE__ ), array('jquery'), '', true);
    wp_enqueue_style("socialsPageStyles", plugins_url("css/llpsocials.css", __FILE__));
  }

  function settingsHTML() { 
    if (isset($_GET['settings-updated']) and empty(get_settings_errors('validation_messages'))) {
      add_settings_error( "validation_messages", 'validation_message', 'Settings Saved', 'updated');
    }

    settings_errors('validation_messages'); ?>

    <div class="wrap">
      <h1>Links list settings</h1>
      <form id="main-form" action="options.php" method="POST">
        <?php 
          settings_fields('linkslistplugin');
          do_settings_sections('linkslist-settings-page');
          submit_button();
        ?>
      </form>
    </div>
  <?php }

  function socialSettingsHTML() {
    if (isset($_GET['settings-updated']) and empty(get_settings_errors('validation_messages'))) {
      add_settings_error( "validation_messages", 'validation_message', 'Settings Saved', 'updated');
    }

    settings_errors('validation_messages'); ?>

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

  function appearanceSettingsHTML() {
    if (isset($_GET['settings-updated']) and empty(get_settings_errors('validation_messages'))) {
      add_settings_error( "validation_messages", 'validation_message', 'Settings Saved', 'updated');
    }

    settings_errors('validation_messages'); ?>

    <div class="wrap">
      <form action="options.php" method="POST">
        <?php
          settings_fields("linkslistappearance");
          do_settings_sections("linkslist-appearance-page");
          submit_button();
        ?>
      </form>
    </div>
  <?php }

  function settings() {
    add_settings_section('llp_first_section', null, null, 'linkslist-settings-page');

    // Profile picture
    add_settings_field('llp_profile_picture', 'Upload Image', array($this, 'profile_picHTML'), 'linkslist-settings-page', 'llp_first_section');  
    register_setting("linkslistplugin", "llp_profile_picture");

    // Proifle title
    add_settings_field('llp_profile_title', 'Profile title', array($this, 'profile_titleHTML'), 'linkslist-settings-page', 'llp_first_section');
    register_setting('linkslistplugin', 'llp_profile_title', array('sanitize_callback' => array($this, 'sanitize_profile_title'), 'default' => 'John Cena'));
    
    // Description
    add_settings_field('llp_description', 'Short description', array($this, 'descriptionHTML'), 'linkslist-settings-page', 'llp_first_section');
    register_setting('linkslistplugin', 'llp_description', array('sanitize_callback' => 'sanitize_text_field', "default" => "You can't see me!"));

    // Background Image
    add_settings_field('llp_background_image', 'Upload background image', array($this, 'background_imageHTML'), 'linkslist-settings-page', 'llp_first_section');
    register_setting("linkslistplugin", "llp_background_image");

    // Announcements
    add_settings_field('llp_announcement', "Announcemnet", array($this, 'announcementHTML'), 'linkslist-settings-page', 'llp_first_section');
    register_setting("linkslistplugin", "llp_announcement", array('sanitize_callback' => 'sanitize_text_field'));
    register_setting("linkslistplugin", "llp_show_announcement", array('sanitize_callback' => 'sanitize_text_field'));

    // Colors
    add_settings_field('llp_brand_color', 'Brand Color', array($this, 'colorsHTML'), 'linkslist-settings-page', 'llp_first_section');
    register_setting('linkslistplugin', 'llp_link_background_color');
    register_setting('linkslistplugin', 'llp_link_text_color');
    register_setting('linkslistplugin', 'llp_main_text_color');
    
    // Second section
    add_settings_section( 'llp_second_section', null, null, 'linkslist-settings-page');
    
    // Links V2
    add_settings_field('llp_links', "Link Title", array($this, 'links_listV2HTML'), 'linkslist-settings-page', 'llp_second_section');    
    register_setting('linkslistplugin', "llp_added_links", array("sanitize_callback" => array($this, 'serialize_data')));

    // Social Icons options page
    add_settings_section("llp_socials_section", null, null, "linkslist-socials-page");

    // Social Icons
    add_settings_field('llp_social_icons', "Social Icons", array($this, 'socialIconsHTML'), 'linkslist-socials-page', 'llp_socials_section');
    register_setting('linkslistpluginsocials', "llp_facebook_url", array('sanitize_callback', array($this, 'format_social_link')));
    register_setting('linkslistpluginsocials', "llp_twitter_url", array('sanitize_callback', array($this, 'format_social_link')));
    register_setting('linkslistpluginsocials', "llp_instagram_url", array('sanitize_callback', array($this, 'format_social_link')));
    register_setting('linkslistpluginsocials', "llp_codepen_url", array('sanitize_callback', array($this, 'format_social_link')));
    register_setting('linkslistpluginsocials', "llp_email_url", array('sanitize_callback' => array($this, 'sanitize_email_field')));
    register_setting('linkslistpluginsocials', "llp_website_url", array('sanitize_callback' => array($this, 'sanitize_url')));


    // Appearance options page
    add_settings_section('llp_appearance_section', null, null, "linkslist-appearance-page");

    add_settings_field('llp_button_styles', "Choose style", array($this, 'appearanceHTML'), 'linkslist-appearance-page', 'llp_appearance_section');
    register_setting('linkslistappearance', "llp_appearance");
  }

  /**
   * 
   * Validation callbacks
   * 
   */

  function format_social_link($data) {
    if (!empty($data)) {

    }
  }

  function serialize_data($data) {
    if (empty($data) or $data === "=>") {
      return '';
    }

    $lines = explode("=>", $data);
    $keys = explode(',', $lines[0]);
    $vals = explode(',', $lines[1]);

    $result = array_combine($keys, $vals);
    
    return serialize($result);
  }

  function sanitize_profile_title($data) {
    $db_data = get_option("llp_profile_title");
    $has_errors = false;

    if (empty($data)) {
      add_settings_error("validation_messages", "validation_message", "Profile title is required", "error");
      $has_errors = true;
    }

    if ($has_errors) {
      return $db_data;
    }

    return strip_tags($data);
  }

  function sanitize_email_field($email) {
    $db_data = get_option("llp_email_url");
    $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

    if (filter_var($email, FILTER_SANITIZE_EMAIL) and preg_match($pattern, $email)) {
      return strpos($email, "mailto:") === 0 ? $email : strtolower("mailto:$email");
    } else {
      add_settings_error("validation_messages", "validation_message", "Invalid format: Please check $email", "error");
      return $db_data;
    }
  }

  function sanitize_url($url) {
    $url = strtolower($url);
    $pattern = "/\b(?:(?:https?):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i";

    if (empty($url)) return;

    if (filter_var($url, FILTER_SANITIZE_URL) and preg_match($pattern, $url)) {
      return (strpos($url, "https://") === 0 or strpos($url, "http://")) ? $url : "https://$url";
    } else {
      add_settings_error("validation_messages", "validation_message", "Invalid format: Please check $url.", "error");
      return '';
    }
  }

  /**
   * 
   * Settings fields display
   * 
   *  */ 

  function colorsHTML() { ?>
    <div class="llp-color-option">
      <label for="llp_main_text_color">Text color</label>
      <input type="color" name="llp_main_text_color" value="<?= get_option("llp_main_text_color") ?>">
    </div>

    <div class="llp-color-option">
      <label for="llp_link_background_color">Link background</label>
      <input type="color" name="llp_link_background_color" value="<?= get_option("llp_link_background_color") ?>">
    </div>

    <div class="llp-color-option">
      <label for="llp_link_text_color">Link text</label>
      <input type="color" name="llp_link_text_color" value="<?= get_option("llp_link_text_color") ?>">
    </div>
  <?php }

  function appearanceHTML() {
    $options = ["default", "classy", "retro", "modern", "bubbly", "cool"];
    ?>
    <div class="appearance-wrapper">
      <?php 
        foreach ($options as $option) {?>
          <div class="llp-appearance">
            <input type="radio" name="llp_appearance" id="" value="<?= $option ?>" <?= get_option("llp_appearance") === $option ? "checked" : "" ?>>
            <div class="llp-preview">
              <?= str_repeat(
                "<a href='#' class=$option></a>", 3
              )?>

              <a href="#" class="<?= $option ?> <?= $option ?>-hover">Hover</a>
            </div>
          </div>
        <?php }
      ?>    
    </div>
    <?php }

  function socialIconsHTML() {
    $options = ["facebook", "twitter", "instagram", "email", "codepen", "website"];

    foreach ($options as $option) { 
      $username = end(explode("/", get_option("llp_${option}_url")));
      ?>
      <div class="llp-inner-input-container">
        <?php if (in_array($option, ["website", "email"])) { ?>
          <label for="llp_<?= $option ?>_url"><?= ucFirst($option) ?></label>
        <?php } else if ($option === "codepen") {?> 
          <label for="llp_<?= $option ?>_url"><?="$option.io/" ?></label>
        <?php } else {?> 
          <label for="llp_<?= $option ?>_url"><?="$option.com/" ?></label>
        <?php } ?>


        <input type="text" name="llp_<?= $option ?>_url"
          id="<?= $option ?>"
          class="<?= !in_array($option, ["email", "website"]) ? "filter-url" : "" ?>"
          data-check="<?= get_option("llp_{$option}_url") ? "added" : "" ?>"
          data-confirm="<?= get_option( "llp_${option}_url") ?>"
          placeholder="<?= (in_array($option, ["website", "email"])) ? ($option === "email" ? "example@email.com" : "www.example.com") : "username" ?>"
          value="<?= $option !== "email" ? ($option !== "website" ? $username : get_option("llp_{$option}_url")) : str_replace("mailto:", "", get_option("llp_${option}_url"))?>"
        >

      </div>
    <?php }
  }

  function announcementHTML() { ?>
    <div class="announcement-controls">
      <textarea type="text" name="llp_announcement" id=""><?= esc_html(get_option("llp_announcement")) ?></textarea>
      
      <div class="announcement-flex">
        <div class="switch-container">
          <input type="checkbox" name="llp_show_announcement" id="" <?= get_option("llp_show_announcement") ? "checked" : "" ?>>
          <div class="switch"></div>
        </div>
        
        <label for="llp_show_announcemnet">Show banner</label>
      </div>

    </div>
  <?php }

  function links_listV2HTML() { ?>
    <button id="addLink">Add a Link</button>

    <div id="llp-links-list">
      <input type="hidden" name="llp_added_links" value="">
      <?php 
        $result = get_option("llp_added_links");
        if ($result) { 
          $linksArr = unserialize($result);
          $index = 1;    
          
          foreach ($linksArr as $title => $link) {?>           
            <div id="link<?= $index ?>">
              <label for="link<?= $index ?>">Link <?= $index ?></label>
              <input type="text" id="link_<?= $index ?>_title" size="20" name="link_<?= $index ?>_title" value="<?= $title ?>" placeholder="Link title" />
              <input type="url" id="link_<?= $index ?>_url" size="20" name="link_<?= $index ?>_url" value="<?= $link ?>" placeholder="https://" />
              <button class="remLink">Remove</button>
            </div>

            <?php $index++;
          } 
        }
      ?>

    </div>
  <?php }

  function background_imageHTML() {
    $options = get_option('llp_background_image');
    $default_image = plugins_url("/assets/default_background_image.png", __FILE__);

    if (!empty($options)) {
      $image_attributes = wp_get_attachment_image_src($options, 'full');
      $src = $image_attributes[0] ?? $default_image;
      $value = $options;
    } else {
      $src = $default_image;
      $value = '';
    } ?>

    <div class="upload" style="max-width: 300px;">
      <img data-src=<?= $default_image ?> src=<?= $src ?> style="display: block; max-width: 100%; object-fit: cover;" />
      <div>
        <input type="hidden" name="llp_background_image" id="llp_background_image" value="' . $value . '" />
        <button type="submit" class="upload_image_button button">Upload</button>
        <button type="submit" class="remove_image_button button">Delete</button>
      </div>
    </div>

  <?php }

  function profile_picHTML() { 
    $options = get_option('llp_profile_picture');
    $default_image = plugins_url("/assets/default_profile_picture.png", __FILE__);
 
    if (!empty($options)) {
      $image_attributes = wp_get_attachment_image_src($options, 'full');
      $src = $image_attributes[0] ?? $default_image;
      $value = $options;
    } else {
      $src = $default_image;
      $value = '';
    } ?>

    <div class="upload" style="max-width:150px;">
      <img data-src=<?= $default_image ?> src=<?= $src ?> style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;" />
      <div>
        <input type="hidden" name="llp_profile_picture" id="llp_profile_picture" value=<?= $value ?>/>
        <button type="submit" class="upload_image_button button">Upload</button>
        <button type="submit" class="remove_image_button button">Delete</button>
      </div>
    </div>

  <?php }

  function profile_titleHTML() { ?>
    <input type="text" name="llp_profile_title" value="<?= esc_attr(get_option('llp_profile_title')) ?>">
  <?php }

  function descriptionHTML() { ?>
    <textarea type="text" name="llp_description" placeholder="Bio/description"><?= esc_html(get_option('llp_description')) ?></textarea>
  <?php }

  /**
   * 
   * Output section
   * 
   * 
   */

  public static function Output() {
    $image_attributes = wp_get_attachment_image_src(get_option('llp_profile_picture'), 'full');
    $src = $image_attributes[0] ?? plugins_url("/assets/default_profile_picture.png", __FILE__);

    $background_image = wp_get_attachment_image_src(get_option('llp_background_image'), 'full');
    $bg_src = $background_image[0] ?? '';
    
    ?>
    <div class="linkslist-main" style="background-image: url(<?= $bg_src ?>); color: <?= get_option("llp_main_text_color") ?>">
      <?php
      if (get_option( "llp_show_announcement")) {?>
        <div class="linkslist-banner">
          <?=  get_option("llp_announcement"); ?>
        </div>
        <?php } ?>
      <img src="<?= $src ?>" alt="" />
      <h1><?= get_option('llp_profile_title') ?></h1>
      <p><?= get_option('llp_description') ?></p>

      <div class="llp-links">
        <?php 
          $result = get_option("llp_added_links");
          if ($result) { 
            $linksArr = unserialize($result);
            
            foreach ($linksArr as $title => $link) { ?>           
              <a href="<?= $link ?>" target="_blank" class=<?= get_option("llp_appearance") ?> style="background-color: <?= get_option("llp_link_background_color") ?>; color: <?= get_option("llp_link_text_color") ?>"><?= $title ?></a>
            <?php }
          }
        ?>
      </div>

      <div class="llp-socials">
        <?php
          $socials = ["llp_facebook_url", "llp_twitter_url", "llp_instagram_url", "llp_codepen_url", "llp_email_url", "llp_website_url"];

          foreach ($socials as $social) { 
            if (get_option($social)) { ?>
              <a href="<?= get_option($social) ?? "" ?>" target="_blank">
              <?php 
                $option_name = explode("_", $social)[1];
                if (in_array($option_name, ["facebook", "twitter", "instagram"])) { ?>
                  <i class="fab fa-<?= $option_name ?>-square fa-2x"></i>
                <?php } else if ($option_name === "codepen") { ?>
                  <i class="fab fa-<?= $option_name ?> fa-2x"></i>
                <?php } else { ?>
                  <i class="fas fa-<?= $option_name !== "website" ? "envelope" : "globe" ?> fa-2x"></i>
                <?php }
              ?>
              </a> 
            <?php }
          }
        
        ?>
      </div>
  <?php }
}

new LinkList();