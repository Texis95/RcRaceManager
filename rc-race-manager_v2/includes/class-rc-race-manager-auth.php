<?php
class RC_Race_Manager_Auth {
    private $plugin_name;
    private $version;
    private $table_name;
    private $session_started = false;

    public function __construct($plugin_name, $version) {
        global $wpdb;
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->table_name = $wpdb->prefix . 'rc_users';

        // Inizializza la sessione se non è già attiva
        $this->start_session();

        // Aggiungi gli hook per gestire le azioni AJAX
        add_action('wp_ajax_nopriv_rc_race_manager_login', array($this, 'handle_login'));
        add_action('wp_ajax_nopriv_rc_race_manager_register', array($this, 'handle_registration'));
        add_action('wp_ajax_rc_race_manager_logout', array($this, 'handle_logout'));
    }

    public function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            username varchar(50) NOT NULL,
            email varchar(100) NOT NULL,
            password varchar(255) NOT NULL,
            role varchar(20) DEFAULT 'user',
            status tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY username (username),
            UNIQUE KEY email (email)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function register_user($username, $email, $password) {
        global $wpdb;

        // Verifica se l'utente esiste già
        $existing_user = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE username = %s OR email = %s",
                $username,
                $email
            )
        );

        if ($existing_user) {
            return new WP_Error('registration-error', 'Username o email già in uso.');
        }

        // Hash della password usando la funzione WordPress
        $hashed_password = wp_hash_password($password);

        // Inserimento nuovo utente
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'username' => $username,
                'email' => $email,
                'password' => $hashed_password,
                'role' => 'user',
                'status' => 1
            ),
            array('%s', '%s', '%s', '%s', '%d')
        );

        if ($result === false) {
            return new WP_Error('registration-error', 'Errore durante la registrazione.');
        }

        return $wpdb->insert_id;
    }

    public function login_user($username, $password) {
        global $wpdb;

        // Cerca l'utente per username o email
        $user = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE (username = %s OR email = %s) AND status = 1",
                $username,
                $username
            )
        );

        if (!$user) {
            return new WP_Error('login-error', 'Credenziali non valide.');
        }

        // Verifica password usando la funzione WordPress
        if (!wp_check_password($password, $user->password)) {
            return new WP_Error('login-error', 'Credenziali non valide.');
        }

        // Imposta la sessione
        $_SESSION['rc_race_manager_user'] = array(
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role
        );

        return $user;
    }

    public function handle_login() {
        check_ajax_referer('rc_race_manager_nonce', 'security');

        $username = sanitize_text_field($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            wp_send_json_error(array('message' => 'Username e password sono obbligatori.'));
        }

        $result = $this->login_user($username, $password);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'message' => 'Login effettuato con successo.',
            'user' => array(
                'username' => $result->username,
                'email' => $result->email,
                'role' => $result->role
            )
        ));
    }

    public function handle_registration() {
        check_ajax_referer('rc_race_manager_nonce', 'security');

        $username = sanitize_text_field($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        if (empty($username) || empty($email) || empty($password)) {
            wp_send_json_error(array('message' => 'Tutti i campi sono obbligatori.'));
        }

        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Email non valida.'));
        }

        $result = $this->register_user($username, $email, $password);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        // Effettua il login automatico dopo la registrazione
        $login_result = $this->login_user($username, $password);

        wp_send_json_success(array('message' => 'Registrazione completata con successo.'));
    }

    public function handle_logout() {
        check_ajax_referer('rc_race_manager_nonce', 'security');

        if ($this->logout_user()) {
            wp_send_json_success(array('message' => 'Logout effettuato con successo.'));
        } else {
            wp_send_json_error(array('message' => 'Errore durante il logout.'));
        }
    }

    public function is_user_logged_in() {
        return isset($_SESSION['rc_race_manager_user']);
    }

    public function get_current_user() {
        return $this->is_user_logged_in() ? $_SESSION['rc_race_manager_user'] : null;
    }

    public function logout_user() {
        if (isset($_SESSION['rc_race_manager_user'])) {
            unset($_SESSION['rc_race_manager_user']);
            session_destroy();
            return true;
        }
        return false;
    }

    // Funzione per avviare la sessione se non è già attiva
    public function start_session() {
        if (!$this->session_started && !session_id()) {
            session_start();
            $this->session_started = true;
        }
    }

    // Funzione per verificare se un utente ha un ruolo specifico
    public function user_has_role($role) {
        $current_user = $this->get_current_user();
        return $current_user && $current_user['role'] === $role;
    }

    // Funzione per verificare se un utente può eseguire un'azione
    public function can_perform_action($action) {
        if (!$this->is_user_logged_in()) {
            return false;
        }

        $user = $this->get_current_user();

        // Definisci le autorizzazioni in base al ruolo
        $permissions = array(
            'admin' => array('add_pista', 'edit_pista', 'delete_pista', 'add_gara', 'edit_gara', 'delete_gara'),
            'user' => array('add_pilota', 'edit_own_pilota')
        );

        return in_array($action, $permissions[$user['role']] ?? array());
    }
}