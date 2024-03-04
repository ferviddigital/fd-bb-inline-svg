<?php
/**
 * Plugin Name:     Inline SVGs for Beaver Builder
 * Description:     Effortlessly upload and render SVGs directly within Beaver Builder Photo modules.
 * Author:          Fervid Digital
 * Author URI:      https://fervid.digital
 * Text Domain:     fd-bb-inline-svg
 * Version:         0.1.0
 */

/**
 * Check if Beaver Builder (Lite or Pro) plugin is installed and activated.
 * 
 * @return bool True if the plugin is installed and activated, false otherwise.
 */
function fd_test_bb_active() {
  if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
  }

  if ( is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' ) ) {
    return TRUE;
  } elseif ( is_plugin_active( 'bb-plugin/fl-builder.php' ) ) {
    return TRUE;
  } else {
    add_action( 'admin_notices', function() {
      $message = 'Inline SVGs for Beaver Builder requires Beaver Builder Plugin (Lite or Pro) to be activated. Please install and activate Beaver Builder Plugin (Lite or Pro) to use this plugin.';
      echo '<div class="notice notice-error"><p>' . $message . '</p></style></div>';
    } );
    return FALSE;
  }
}

$is_bb_active = fd_test_bb_active();

if ( ! $is_bb_active ) {
  // Disable plugin and display a message if Beaver Builder is not active
  deactivate_plugins( plugin_basename( __FILE__ ) );
  return;
}

/**
 * Insert Inline SVG selector in Photo module General tab.
 * 
 * @since 0.1.0
 * 
 * @param array   $form The form data.
 * @param string  $id   The form id.
 * 
 * @return array The form data.
 */
function fd_bb_photo_module_settings_form( $form, $id ) {
  if ( $id !== 'photo') return $form;

  $form['general']['sections']['general']['fields']['svg_inline'] = [
    'type' => 'select',
    'label' => 'Inline SVG',
    'default' => 'no',
    'options' => [
      'no' => 'No',
      'yes' => 'Yes'
    ],
    'help' => 'If the selected photo is an SVG, display the SVG inline rather than using and <strong>img</strong> tag.'
  ];

  return $form;
}

add_filter( 'fl_builder_register_settings_form', 'fd_bb_photo_module_settings_form', 10, 2);


/**
 * Include svg file type in Beaver Builder upload prefilter.
 * 
 * @since 0.1.0
 * 
 * @param array $regex The allowed file types.
 * 
 * @return array The allowed file types.
 */
function fd_bb_module_upload_regex( $regex ) {
  $regex['photo'] = preg_replace( "/(webp)/", "$1|svg", $regex['photo'] );
  return $regex;
}

add_filter( 'fl_module_upload_regex', 'fd_bb_module_upload_regex');


/**
 * Include svg in the allowed WordPress mime types.
 * 
 * @since 0.1.0
 * 
 * @return array The allowed mime types.
 */
function fd_wp_upload_mimes( $mime_types ) {
  $mime_types['svg'] = 'image/svg+xml';
  return $mime_types;
}

add_filter( 'upload_mimes', 'fd_wp_upload_mimes');


/**
 * Add data attribute to Inline SVG enabled Beaver Builder Photo module HTML output.
 * 
 * @since 0.1.0
 * 
 * @param array   $attrs  The module HTML attributes.
 * @param object  $module The module node object.
 * 
 * @return array The module HTML attributes.
 */
function fd_bb_module_attributes( $attrs, $module ) {

  if ($module->settings->type !== 'photo') return $attrs;
  if ($module->settings->svg_inline === 'no') return $attrs;

  $attrs['data-svg-inline'] = 'true';

  return $attrs;
}

add_filter('fl_builder_module_attributes', 'fd_bb_module_attributes', 10, 2);


/**
 * Register and enqueue script to transform image tag into inlined svg tag.
 * 
 * @since 0.1.0
 */
function fd_wp_enqueue_scripts() {

  wp_register_script(
    'fd-bb-inline-svg',
    plugins_url( 'public/js/fd-bb-inline-svg.js', __FILE__ ),
    ['jquery'],
    filemtime( plugin_dir_path( __FILE__ ) . 'public/js/fd-bb-inline-svg.js')
  );

  wp_enqueue_script('fd-bb-inline-svg');
}

add_action( 'wp_enqueue_scripts', 'fd_wp_enqueue_scripts');
