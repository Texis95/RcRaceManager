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
        :root {
            --rc-primary: #0d6efd;
            --rc-secondary: #6c757d;
            --rc-success: #198754;
            --rc-bg-light: #f8f9fa;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--rc-bg-light);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .rc-race-manager-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar customization */
        .navbar-dark {
            background-color: #1a1a1a !important;
        }

        .navbar-brand {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Sidebar customization */
        .list-group-item {
            border: none;
            padding: 1rem 1.25rem;
            transition: all 0.2s ease;
        }

        .list-group-item:hover {
            background-color: rgba(13, 110, 253, 0.1);
            color: var(--rc-primary);
        }

        .list-group-item.active {
            background-color: var(--rc-primary);
            border-color: var(--rc-primary);
        }

        /* Card customization */
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Button customization */
        .btn {
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.35rem;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--rc-primary);
            border-color: var(--rc-primary);
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        /* Table customization */
        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }

        /* Modal customization */
        .modal-content {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-bottom: 1px solid #dee2e6;
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #dee2e6;
            padding: 1.5rem;
        }

        /* Form customization */
        .form-control {
            padding: 0.5rem 0.75rem;
            border-radius: 0.35rem;
            border: 1px solid #dee2e6;
        }

        .form-control:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .navbar {
                padding: 1rem;
            }

            .card {
                margin-bottom: 1rem;
            }
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