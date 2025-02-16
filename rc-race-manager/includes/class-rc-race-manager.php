<?php
if (!defined('ABSPATH')) {
    exit;
}

class RC_Race_Manager {
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'rc-race-manager';
        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        if (!class_exists('RC_Race_Manager_Loader')) {
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rc-race-manager-loader.php';
        }

        if (!class_exists('RC_Race_Manager_Admin')) {
            require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-rc-race-manager-admin.php';
        }

        if (!class_exists('RC_Race_Manager_Public')) {
            require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-rc-race-manager-public.php';
        }

        $this->loader = new RC_Race_Manager_Loader();

        if (!$this->loader) {
            error_log('RC Race Manager: Errore - Impossibile creare il loader');
            return;
        }
    }

    private function define_admin_hooks() {
        try {
            $plugin_admin = new RC_Race_Manager_Admin($this->get_plugin_name(), $this->get_version());

            $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
            $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
            $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        } catch (Exception $e) {
            error_log('RC Race Manager: Errore definizione hook admin - ' . $e->getMessage());
        }
    }

    private function define_public_hooks() {
        try {
            $plugin_public = new RC_Race_Manager_Public($this->get_plugin_name(), $this->get_version());

            $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
            $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        } catch (Exception $e) {
            error_log('RC Race Manager: Errore definizione hook public - ' . $e->getMessage());
        }
    }

    public function run() {
        if ($this->loader) {
            $this->loader->run();
        } else {
            error_log('RC Race Manager: Errore - Loader non inizializzato');
        }
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }
}