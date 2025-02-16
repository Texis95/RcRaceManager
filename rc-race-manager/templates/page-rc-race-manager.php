<?php
/**
 * Template Name: RC Race Manager Full Screen
 */

// Rimuove tutti gli stili e script di WordPress non necessari
function rc_race_manager_remove_styles() {
    global $wp_styles;
    global $wp_scripts;

    // Mantiene solo gli stili e script necessari
    $allowed_styles = array('rc-race-manager', 'bootstrap', 'fontawesome');
    $allowed_scripts = array('jquery', 'rc-race-manager', 'bootstrap');

    foreach($wp_styles->queue as $handle) {
        if (!in_array($handle, $allowed_styles)) {
            wp_dequeue_style($handle);
            wp_deregister_style($handle);
        }
    }

    foreach($wp_scripts->queue as $handle) {
        if (!in_array($handle, $allowed_scripts)) {
            wp_dequeue_script($handle);
            wp_deregister_script($handle);
        }
    }
}
add_action('wp_enqueue_scripts', 'rc_race_manager_remove_styles', 100);

// Disabilita la barra di amministrazione
add_filter('show_admin_bar', '__return_false');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        body {
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
        }
        .rc-race-manager-container {
            height: 100vh;
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f8f9fa;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .rc-race-manager-container .row {
            height: 100%;
            margin: 0;
            flex: 1;
        }
        .rc-race-manager-container .col-md-3 {
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            z-index: 1;
            overflow-y: auto;
        }
        .rc-race-manager-container .col-md-9 {
            padding: 20px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        #rc-race-content {
            background: transparent;
            box-shadow: none;
            flex: 1;
        }
        .wp-site-blocks,
        .entry-content,
        .post-inner,
        article {
            height: 100%;
            margin: 0 !important;
            padding: 0 !important;
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
        }

        /* Nasconde elementi di WordPress non necessari */
        .site-header,
        .site-footer,
        .post-meta-wrapper,
        .entry-header {
            display: none !important;
        }

        /* Gestione scroll nei contenitori */
        .table-responsive {
            overflow-y: auto;
            max-height: calc(100vh - 150px); /* Altezza massima per le tabelle */
        }
    </style>
</head>
<body <?php body_class(); ?>>
    <?php 
    while (have_posts()) : 
        the_post();
        the_content();
    endwhile;
    ?>
    <?php wp_footer(); ?>
</body>
</html>