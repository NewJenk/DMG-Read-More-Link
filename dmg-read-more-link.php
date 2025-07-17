<?php
/**
 * Plugin Name:       DMG Read More Link
 * Plugin URI:        https://github.com/NewJenk/DMG-Read-More-Link
 * Description:       A dynamic WordPress block that allows you to easily search for and link to other posts on your site, prepended with 'Read More: '.
 * Version:           1.0.1
 * Requires at least: 6.0
 * Tested up to:      6.8.1
 * Requires PHP:      7.4
 * Author:            Shaun Jenkins
 * Author URI:        https://shaunjenkins.com/
 * License:           GPLv3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       dmg-rml
 * Domain Path:       /languages
 *
 * @package           DMG Read More Link
 * @author            Shaun Jenkins
 * @copyright         2025 Shaun Jenkins
 * @license           GPLv3
 * @link              https://github.com/NewJenk/DMG-Read-More-Link
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DMG_RML_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'DMG_RML_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
            
// Include the Registry 
require_once plugin_dir_path(__FILE__) . 'includes/registry.php';
// Initiate the Registry
$registry = new DMG\RML\Registry();
$registry->build_registry();
/**
 * Add a filter to make the registry available to other parts of the plugin.
 *
 * @link https://tommcfarlin.com/registry-pattern-in-wordpress/
 */
add_filter('dmg_rml_registry', function () use ($registry) {
    return $registry;
});

// Example use
// Retrieve the registry using the filter 'dmg_rml_registry'
/* $the_test = apply_filters('dmg_rml_registry', null);
var_dump($the_test);
var_dump($the_test->get('service1')->doSomething()); */