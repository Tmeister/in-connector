<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://enriquechavez.co
 * @since             1.0.0
 * @package           In_Connector
 *
 * @wordpress-plugin
 * Plugin Name:       InfusionSoft Connector
 * Plugin URI:        http://impactful.io
 * Description:       Add a simple proxy between the Client App and InfusionSoft, this is needed because there is no JavaScript API for InfusionSoft.
 * Version:           1.0.3
 * Author:            Enrique Chávez
 * Author URI:        https://enriquechavez.co
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       in-connector
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-in-connector-activator.php
 */
function activate_in_connector()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-in-connector-activator.php';
    In_Connector_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-in-connector-deactivator.php
 */
function deactivate_in_connector()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-in-connector-deactivator.php';
    In_Connector_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_in_connector');
register_deactivation_hook(__FILE__, 'deactivate_in_connector');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-in-connector.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_in_connector()
{

    $plugin = new In_Connector();
    $plugin->run();

}
run_in_connector();
