<?php
class RC_Race_Manager_Admin {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/rc-race-manager-admin.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/rc-race-manager-admin.js', array('jquery'), $this->version, false);
    }

    public function add_plugin_admin_menu() {
        add_menu_page(
            'RC Race Manager', 
            'RC Race Manager', 
            'manage_options', 
            $this->plugin_name, 
            array($this, 'display_plugin_admin_page'),
            'dashicons-flag',
            26
        );

        // Sottomenu per le varie sezioni
        add_submenu_page(
            $this->plugin_name,
            'Gare',
            'Gare',
            'manage_options',
            $this->plugin_name . '-gare',
            array($this, 'display_plugin_gare_page')
        );

        add_submenu_page(
            $this->plugin_name,
            'Piloti',
            'Piloti',
            'manage_options',
            $this->plugin_name . '-piloti',
            array($this, 'display_plugin_piloti_page')
        );

        add_submenu_page(
            $this->plugin_name,
            'Piste',
            'Piste',
            'manage_options',
            $this->plugin_name . '-piste',
            array($this, 'display_plugin_piste_page')
        );
    }

    public function display_plugin_admin_page() {
        include_once 'partials/rc-race-manager-admin-display.php';
    }

    public function display_plugin_gare_page() {
        include_once 'partials/rc-race-manager-admin-gare.php';
    }

    public function display_plugin_piloti_page() {
        include_once 'partials/rc-race-manager-admin-piloti.php';
    }

    public function display_plugin_piste_page() {
        include_once 'partials/rc-race-manager-admin-piste.php';
    }
}
