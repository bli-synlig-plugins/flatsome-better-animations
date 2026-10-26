<?php
/**
 * Plugin Name:       Flatsome Better Animations
 * Description:       Erstatter Flatsomes trege standard-animasjoner med raskere, moderne overganger. Legger også til et eget .reveal scroll-animasjonssystem.
 * Version:           1.0.2
 * Author:            Benjamin Pedersen
 * Text Domain:       flatsome-better-animations
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'FBA_VERSION',  '1.0.2' );
define( 'FBA_DIR',      plugin_dir_path( __FILE__ ) );
define( 'FBA_URL',      plugin_dir_url( __FILE__ ) );

require_once FBA_DIR . 'includes/class-fba-plugin.php';

FBA_Plugin::get_instance();
