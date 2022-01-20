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
    $appearancePageHook = add_submenu_page( "linkslistsettings", "Links List Appearance", "Appearance", "manage_options", "linkslistappearancesettings", array($this, 'appearanceSettingsHTML'), 1);
    
    add_action("load-{$socialsPageHook}", array($this, "socialsPageAssets"));
    add_action("load-{$appearancePageHook}", array($this, "appearancePageAssets"));
  }

  function appearancePageAssets() {
    wp_enqueue_style("appearancePageStyles", plugins_url("css/llpappearance.css", __FILE__));
  }

  function socialsPageAssets() {
    wp_enqueue_script("socials-js", plugins_url("/js/llpsocials.js", __FILE__ ), array(), '', true);
    wp_enqueue_style("socialsPageStyles", plugins_url("css/llpsocials.css", __FILE__));
  }

  function settingsHTML() { 
    if (isset($_GET['settings-updated']) and empty(get_settings_errors('validation_messages'))) {
      add_settings_error( "validation_messages", 'validation_message', 'Settings Saved', 'updated');
    }

    settings_errors('validation_messages'); ?>

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

    <div>
      <h1>Hello</h1>
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

    // Links
    add_settings_field('llp_links', "Link Title", array($this, 'links_listHTML'), 'linkslist-settings-page', 'llp_second_section');
    register_setting('linkslistplugin', 'llp_link_1_title',  array('sanitize_callback' => 'sanitize_text_field'));
    register_setting('linkslistplugin', 'llp_link_1_url', array('sanitize_callback' => 'sanitize_text_field'));
    
    register_setting('linkslistplugin', 'llp_link_2_title',  array('sanitize_callback' => 'sanitize_text_field'));
    register_setting('linkslistplugin', 'llp_link_2_url', array('sanitize_callback' => 'sanitize_text_field'));
    
    register_setting('linkslistplugin', 'llp_link_3_title',  array('sanitize_callback' => 'sanitize_text_field'));
    register_setting('linkslistplugin', 'llp_link_3_url', array('sanitize_callback' => 'sanitize_text_field'));
    
    register_setting('linkslistplugin', 'llp_link_4_title',  array('sanitize_callback' => 'sanitize_text_field'));
    register_setting('linkslistplugin', 'llp_link_4_url', array('sanitize_callback' => 'sanitize_text_field'));
    
    register_setting('linkslistplugin', 'llp_link_5_title',  array('sanitize_callback' => 'sanitize_text_field'));
    register_setting('linkslistplugin', 'llp_link_5_url', array('sanitize_callback' => 'sanitize_text_field'));
    

    // Social Icons options page
    add_settings_section("llp_socials_section", null, null, "linkslist-socials-page");

    // Social Icons
    add_settings_field('llp_social_icons', "Social Icons", array($this, 'socialIconsHTML'), 'linkslist-socials-page', 'llp_socials_section');
    register_setting('linkslistpluginsocials', "llp_facebook_url", array('sanitize_callback' => array($this, 'sanitize_url')));
    register_setting('linkslistpluginsocials', "llp_twitter_url", array('sanitize_callback' => array($this, 'sanitize_url')));
    register_setting('linkslistpluginsocials', "llp_instagram_url", array('sanitize_callback' => array($this, 'sanitize_url')));
    register_setting('linkslistpluginsocials', "llp_codepen_url", array('sanitize_callback' => array($this, 'sanitize_url')));
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
    $has_errors = false;
    $pattern = "^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$";

    if (!filter_var($email, FILTER_SANITIZE_EMAIL) and preg_match($pattern, $email)) {
      add_settings_error("validation_messages", "validation_message", "Invalid format: Please check $email", "error");
      $has_errors = true;
    }

    if ($has_errors) {
      return $db_data;
    }

    return strtolower("mailto:$email");
  }

  function sanitize_url($url) {
    $url = strtolower($url);
    $has_errors = false;

    $pattern = "www";

    if (empty($url)) return;

    if (!filter_var($url, FILTER_SANITIZE_URL) and preg_match($pattern, $url)) {
      add_settings_error("validation_messages", "validation_message", "Invalid format: Please check $url.", "error");
      $has_errors = true;
    }

    if ($has_errors) {
      return '';
    }

    return strpos($url, "https://") === 0 ? $url : "https://$url";
  }

  /**
   * 
   * Settings fields display
   * 
   *  */ 

  function colorsHTML() { ?>
    <label for="llp_main_text_color">Text color</label>
    <input type="color" name="llp_main_text_color" value="<?= get_option("llp_main_text_color") ?>">
    <br>
    <label for="llp_link_background_color">Link background</label>
    <input type="color" name="llp_link_background_color" value="<?= get_option("llp_link_background_color") ?>">
    <br>
    <label for="llp_link_text_color">Link text</label>
    <input type="color" name="llp_link_text_color" value="<?= get_option("llp_link_text_color") ?>">
  <?php }

  function appearanceHTML() { ?>
    <div class="appearance-wrapper">
      <div class="llp-appearance">
        <input type="radio" name="llp_appearance" id="" value="default" <?= get_option("llp_appearance") === "default" ? "checked" : "" ?>>
        <div class="llp-preview">
          <a href="#" class="default"></a>
          <a href="#" class="default"></a>
          <a href="#" class="default"></a>
        </div>
      </div>
      <div class="llp-appearance">
        <input type="radio" name="llp_appearance" id="" value="classy" <?= get_option("llp_appearance") === "classy" ? "checked" : "" ?>>
        <div class="llp-preview">
          <a href="#" class="classy"></a>
          <a href="#" class="classy"></a>
          <a href="#" class="classy"></a>
        </div>
      </div>
      <div class="llp-appearance">
        <input type="radio" name="llp_appearance" id="" value="retro"<?= get_option("llp_appearance") === "retro" ? "checked" : "" ?>>
        <div class="llp-preview">
          <a href="#" class="retro"></a>
          <a href="#" class="retro"></a>
          <a href="#" class="retro"></a>
        </div>
      </div>
      <div class="llp-appearance">
        <input type="radio" name="llp_appearance" id="" value="modern"<?= get_option("llp_appearance") === "modern" ? "checked" : "" ?>>
        <div class="llp-preview">
          <a href="#" class="modern"></a>
          <a href="#" class="modern"></a>
          <a href="#" class="modern"></a>
        </div>
      </div>
      <div class="llp-appearance">
        <input type="radio" name="llp_appearance" id="" value="bubbly"<?= get_option("llp_appearance") === "bubbly" ? "checked" : "" ?>>
        <div class="llp-preview">
          <a href="#" class="bubbly"></a>
          <a href="#" class="bubbly"></a>
          <a href="#" class="bubbly"></a>
        </div>
      </div>
    </div>
    <?php }

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
      <input type="text" name="llp_email_url" value=<?= str_replace("mailto:", "", get_option('llp_email_url')) ?>>
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
    <div class="group-one">
      <input type="text" name="llp_link_1_title" value="<?= esc_attr(get_option('llp_link_1_title')) ?>">
      <input type="text" name="llp_link_1_url" value="<?= esc_attr(esc_url(get_option('llp_link_1_url'))) ?>">
    </div>
    <div class="group-one">
      <input type="text" name="llp_link_2_title" value="<?= esc_attr(get_option('llp_link_2_title')) ?>">
      <input type="text" name="llp_link_2_url" value="<?= esc_attr(esc_url(get_option('llp_link_2_url'))) ?>">
    </div>
    <div class="group-one">
      <input type="text" name="llp_link_3_title" value="<?= esc_attr(get_option('llp_link_3_title')) ?>">
      <input type="text" name="llp_link_3_url" value="<?= esc_attr(esc_url(get_option('llp_link_3_url'))) ?>">
    </div>
    <div class="group-one">
      <input type="text" name="llp_link_4_title" value="<?= esc_attr(get_option('llp_link_4_title')) ?>">
      <input type="text" name="llp_link_4_url" value="<?= esc_attr(esc_url(get_option('llp_link_4_url'))) ?>">
    </div>
    <div class="group-one">
      <input type="text" name="llp_link_5_title" value="<?= esc_attr(get_option('llp_link_5_title')) ?>">
      <input type="text" name="llp_link_5_url" value="<?= esc_attr(esc_url(get_option('llp_link_5_url'))) ?>">
    </div>
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
    <div class="linkslist-main" style="background-image: url(' . $bg_src . '); color: <?= get_option("llp_main_text_color") ?>">
      <div class="linkslist-banner"><?php
        if (get_option( "llp_show_announcement")) {
          echo  get_option("llp_announcement"); 
        }
      ?></div>
      <img src="<?= $src ?>" alt="" />
      <h3><?= get_option('llp_profile_title') ?></h3>
      <p><?= get_option('llp_description') ?></p>

      <div class="llp-links">
        <?php 
          $links = [
            "llp_link_1_title" => "llp_link_1_url", 
            "llp_link_2_title" => "llp_link_2_url", 
            "llp_link_3_title" => "llp_link_3_url",
            "llp_link_4_title" => "llp_link_4_url",
            "llp_link_5_title" => "llp_link_5_url",
          ];

          foreach ($links as $key => $value) { 
            if (get_option($key) and get_option($value)) { ?>
              <a href="<?= get_option($value) ?>" class=<?= get_option("llp_appearance") ?> style="background-color: <?= get_option("llp_link_background_color") ?>; color: <?= get_option("llp_link_text_color") ?>"><?= get_option($key) ?></a>
            <?php }
          }
        ?>
      </div>

      <div class="llp-socials">
        <?php
          $socials = ["llp_facebook_url", "llp_twitter_url", "llp_instagram_url", "llp_codepen_url", "llp_email_url", "llp_website_url"];

          foreach ($socials as $social) { 
            if (get_option($social)) { ?>
              <a href="<?= get_option($social) ?? "" ?>">
              <?php 
                $option_name = explode("_", $social)[1];
                if (in_array($option_name, ["facebook", "twitter", "instagram"])) { ?>
                  <i class="fab fa-<?= $option_name ?>-square"></i>
                <?php } else if ($option_name === "codepen") { ?>
                  <i class="fab fa-<?= $option_name ?>"></i>
                <?php } else { ?>
                  <i class="fas fa-<?= $option_name !== "website" ? "envelope" : "globe" ?>"></i>
                <?php }
              ?>
              </a> 
            <?php }
          }
        
        ?>
      </div>
    </div>
  <?php }
}

new LinkList();