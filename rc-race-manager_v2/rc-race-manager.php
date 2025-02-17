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

// Previeni l'accesso diretto al file
if (!defined('ABSPATH')) {
    exit;
}

// Verifica funzioni WordPress essenziali
if (!function_exists('add_action') || !function_exists('add_filter')) {
    error_log('RC Race Manager: Errore - Funzioni WordPress essenziali mancanti');
    return;
}

// Setup
if (!defined('RC_RACE_MANAGER_PLUGIN_DIR')) {
    define('RC_RACE_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Includes - Ordine di caricamento importante
$required_files = array(
    'includes/class-rc-race-manager-loader.php',     // 1. Loader (base)
    'includes/class-rc-race-manager-activator.php',  // 2. Activator (dipende da loader)
    'includes/class-rc-race-manager-auth.php',       // 3. Auth (dipende da loader)
    'includes/class-rc-race-manager-pdf.php',        // 4. PDF (indipendente)
    'public/class-rc-race-manager-public.php',       // 5. Public (dipende da loader, auth e pdf)
    'includes/class-rc-race-manager.php'             // 6. Main class (dipende da tutti)
);

foreach ($required_files as $file) {
    $file_path = RC_RACE_MANAGER_PLUGIN_DIR . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
        error_log("RC Race Manager: File caricato - $file");
    } else {
        error_log("RC Race Manager: ERRORE - File mancante - $file_path");
        return;
    }
}

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

// Activation
function rc_race_manager_activate() {
    error_log('RC Race Manager: Chiamata funzione di attivazione');
    try {
        require_once RC_RACE_MANAGER_PLUGIN_DIR . 'includes/class-rc-race-manager-activator.php';
        RC_Race_Manager_Activator::activate();
        error_log('RC Race Manager: Attivazione completata con successo');
    } catch (Exception $e) {
        error_log('RC Race Manager: Errore durante l\'attivazione - ' . $e->getMessage());
        throw $e;
    }
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

    // Verifica tutte le classi necessarie
    $required_classes = array(
        'RC_Race_Manager_Loader',
        'RC_Race_Manager',
        'RC_Race_Manager_Activator',
        'RC_Race_Manager_Auth',
        'RC_Race_Manager_Public',
        'RC_Race_Manager_PDF'
    );

    foreach ($required_classes as $class) {
        if (!class_exists($class)) {
            error_log("RC Race Manager: Errore - Classe $class non trovata");
            return;
        }
    }

    try {
        $plugin = new RC_Race_Manager();
        $plugin->run();
        error_log('RC Race Manager: Plugin inizializzato con successo');
    } catch (Exception $e) {
        error_log('RC Race Manager: Errore durante l\'inizializzazione - ' . $e->getMessage());
    }
}

// Avvio del plugin con controllo di sicurezza
if (defined('ABSPATH') && defined('RC_RACE_MANAGER_PLUGIN_DIR')) {
    run_rc_race_manager();
} else {
    error_log('RC Race Manager: Errore - Costanti di base non definite');
}