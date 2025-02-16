<?php
/**
 * Plugin Name: RC Race Manager
 * Plugin URI: 
 * Description: Sistema gestione gare RC con form piloti esteso (dati personali, trasponder, categoria) e approvazione admin
 * Version: 1.0.0
 * Author: 
 * Author URI: 
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rc-race-manager
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Setup
define('RC_RACE_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Registra il template all'inizio
function rc_race_manager_add_template($templates) {
    error_log('RC Race Manager: Adding template to the list');
    $templates['rc-race-manager'] = 'RC Race Manager Full Screen';
    return $templates;
}
add_filter('theme_page_templates', 'rc_race_manager_add_template', 1);

function rc_race_manager_load_template($template) {
    $template_slug = get_page_template_slug();
    error_log('RC Race Manager: Current template slug: ' . $template_slug);

    if ('rc-race-manager' === $template_slug) {
        $plugin_template = RC_RACE_MANAGER_PLUGIN_DIR . 'page-rc-race-manager.php';
        error_log('RC Race Manager: Loading template from: ' . $plugin_template);
        error_log('RC Race Manager: Template exists: ' . (file_exists($plugin_template) ? 'yes' : 'no'));

        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter('template_include', 'rc_race_manager_load_template', 99);

// Includes
require_once RC_RACE_MANAGER_PLUGIN_DIR . 'includes/class-rc-race-manager.php';
require_once RC_RACE_MANAGER_PLUGIN_DIR . 'includes/class-rc-race-manager-activator.php';
require_once RC_RACE_MANAGER_PLUGIN_DIR . 'public/class-rc-race-manager-public.php';

// Activation
function rc_race_manager_activate() {
    error_log('RC Race Manager: Chiamata funzione di attivazione');
    require_once RC_RACE_MANAGER_PLUGIN_DIR . 'includes/class-rc-race-manager-activator.php';
    RC_Race_Manager_Activator::activate();
}
register_activation_hook(__FILE__, 'rc_race_manager_activate');

// Deactivation
function rc_race_manager_deactivate() {
    error_log('RC Race Manager: Plugin disattivato');
}
register_deactivation_hook(__FILE__, 'rc_race_manager_deactivate');

// Initialize plugin
function run_rc_race_manager() {
    error_log('RC Race Manager: Inizializzazione plugin');
    $plugin = new RC_Race_Manager();
    $plugin->run();
}

run_rc_race_manager();