<?php
/**
 * Plugin Name:       Flatsome Motion
 * Plugin URI:        https://kodekassa.no/plugins/flatsome-better-animations
 * Description:       Raskere Flatsome-animasjoner, scroll-reveal og Motion-felt direkte på UX-elementer.
 * Version:           2.0.2
 * Author:            Benjamin Pedersen
 * Text Domain:       flatsome-better-animations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FBA_VERSION', '2.0.2' );
define( 'FBA_DIR', plugin_dir_path( __FILE__ ) );
define( 'FBA_URL', plugin_dir_url( __FILE__ ) );

require_once FBA_DIR . 'includes/class-fba-library.php';
require_once FBA_DIR . 'includes/class-fba-plugin.php';
require_once FBA_DIR . 'includes/class-fba-admin.php';
require_once FBA_DIR . 'includes/class-fba-builder.php';
require_once FBA_DIR . 'includes/class-fba-output.php';

FBA_Plugin::get_instance();
