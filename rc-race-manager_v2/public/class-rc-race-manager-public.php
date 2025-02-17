<?php
class RC_Race_Manager_Public {
    private $plugin_name;
    private $version;
    private $auth;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Inizializza l'autenticazione
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rc-race-manager-auth.php';
        $this->auth = new RC_Race_Manager_Auth($plugin_name, $version);

        // Avvia la sessione
        $this->auth->start_session();

        // Registra gli shortcode
        add_shortcode('rc_race_manager_frontend', array($this, 'display_frontend_shortcode'));

        // Registra gli endpoint AJAX
        add_action('wp_ajax_rc_race_manager_register', array($this, 'handle_registration'));
        add_action('wp_ajax_nopriv_rc_race_manager_register', array($this, 'handle_registration'));

        add_action('wp_ajax_rc_race_manager_login', array($this, 'handle_login'));
        add_action('wp_ajax_nopriv_rc_race_manager_login', array($this, 'handle_login'));

        add_action('wp_ajax_rc_race_manager_logout', array($this, 'handle_logout'));

        // Endpoint protetti (solo utenti autenticati)
        add_action('wp_ajax_rc_race_manager_add_pista', array($this, 'add_pista'));
        add_action('wp_ajax_rc_race_manager_add_pilota', array($this, 'add_pilota'));
        add_action('wp_ajax_rc_race_manager_add_gara', array($this, 'add_gara'));

        add_action('wp_ajax_rc_race_manager_load_section', array($this, 'load_section'));
        add_action('wp_ajax_nopriv_rc_race_manager_load_section', array($this, 'load_section'));

        add_action('wp_ajax_rc_race_manager_get_iscritti', array($this, 'get_iscritti'));
        add_action('wp_ajax_nopriv_rc_race_manager_get_iscritti', array($this, 'get_iscritti'));

        add_action('wp_ajax_rc_race_manager_iscrivi_pilota', array($this, 'iscrivi_pilota'));
        add_action('wp_ajax_rc_race_manager_cancella_iscrizione', array($this, 'cancella_iscrizione'));
        add_action('wp_ajax_rc_race_manager_esporta_pdf', array($this, 'esporta_pdf'));

        //New AJAX action added here
        add_action('wp_ajax_rc_race_manager_get_dettagli_gara', array($this, 'get_dettagli_gara'));
        add_action('wp_ajax_nopriv_rc_race_manager_get_dettagli_gara', array($this, 'get_dettagli_gara'));


        error_log('RC Race Manager: Inizializzazione classe public');
    }

    // Gestione autenticazione
    public function handle_registration() {
        if (!$this->verify_nonce('rc_race_manager_auth_nonce')) {
            return;
        }

        $required_fields = array('username', 'email', 'password');
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                wp_send_json_error(array('message' => "Il campo $field è obbligatorio"));
                return;
            }
        }

        $result = $this->auth->register_user(
            sanitize_user($_POST['username']),
            sanitize_email($_POST['email']),
            $_POST['password']
        );

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        } else {
            wp_send_json_success(array('message' => 'Registrazione completata con successo!'));
        }
    }

    public function handle_login() {
        if (!$this->verify_nonce('rc_race_manager_auth_nonce')) {
            return;
        }

        $required_fields = array('username', 'password');
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                wp_send_json_error(array('message' => "Il campo $field è obbligatorio"));
                return;
            }
        }

        $result = $this->auth->login_user(
            sanitize_user($_POST['username']),
            $_POST['password']
        );

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        } else {
            wp_send_json_success(array('message' => 'Login effettuato con successo!'));
        }
    }

    public function handle_logout() {
        error_log('RC Race Manager: [START] Inizio funzione handle_logout');
        error_log('RC Race Manager: [POST] Dati ricevuti: ' . print_r($_POST, true));

        if (!$this->verify_nonce('rc_race_manager_auth_nonce')) {
            error_log('RC Race Manager: [ERRORE] Verifica nonce fallita');
            return;
        }

        if (!$this->auth->is_user_logged_in()) {
            error_log('RC Race Manager: [ERRORE] Utente non loggato');
            wp_send_json_error(array('message' => 'Utente non loggato'));
            return;
        }

        if ($this->auth->logout_user()) {
            error_log('RC Race Manager: [OK] Logout effettuato con successo');
            wp_send_json_success(array(
                'message' => 'Logout effettuato con successo!',
                'redirect' => home_url() // Aggiungiamo URL di reindirizzamento
            ));
        } else {
            error_log('RC Race Manager: [ERRORE] Errore durante il logout');
            wp_send_json_error(array('message' => 'Errore durante il logout'));
        }
    }

    // Verifica autenticazione per funzioni protette
    private function check_auth() {
        if (!$this->auth->is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Devi effettuare il login per eseguire questa azione'));
            return false;
        }
        return true;
    }

    // Override delle funzioni esistenti per aggiungere protezione
    public function add_pista() {
        if (!$this->check_auth()) {
            return;
        }
        $this->add_pista_original();
    }

    public function add_pilota() {
        if (!$this->check_auth()) {
            return;
        }
        $this->add_pilota_original();
    }

    public function add_gara() {
        if (!$this->check_auth()) {
            return;
        }
        $this->add_gara_original();
    }


    private function add_pista_original(){
        error_log('RC Race Manager: [START] Inizio funzione add_pista');
        error_log('RC Race Manager: [POST] Dati ricevuti: ' . print_r($_POST, true));

        if (!$this->verify_nonce()) {
            return;
        }

        global $wpdb;
        if (!$wpdb->check_connection()) {
            error_log('RC Race Manager: [ERRORE] Connessione al database fallita');
            wp_send_json_error(array('message' => 'Errore di connessione al database'));
            return;
        }

        $table_piste = $wpdb->prefix . 'rc_piste';
        error_log('RC Race Manager: [INFO] Nome tabella: ' . $table_piste);


        // Test esistenza tabella con query diretta
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_piste'");
        error_log('RC Race Manager: [INFO] Test esistenza tabella - Risultato: ' . ($table_exists === $table_piste ? 'Esiste' : 'Non esiste'));

        if ($table_exists !== $table_piste) {
            error_log('RC Race Manager: [ERRORE] Tabella non trovata');
            wp_send_json_error(array(
                'message' => 'Errore di configurazione del database',
                'debug' => array(
                    'table_name' => $table_piste,
                    'exists' => $table_exists,
                    'wpdb_prefix' => $wpdb->prefix,
                    'tables' => $wpdb->get_results("SHOW TABLES", ARRAY_N)
                )
            ));
            return;
        }

        // Log della struttura della tabella
        $table_structure = $wpdb->get_results("DESCRIBE $table_piste");
        error_log('RC Race Manager: [INFO] Struttura tabella: ' . print_r($table_structure, true));

        // Verifica e sanitizza i campi obbligatori
        $required_fields = array('nome', 'localita');
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                error_log("RC Race Manager: [ERRORE] Campo obbligatorio mancante - $field");
                wp_send_json_error(array(
                    'message' => "Il campo $field è obbligatorio",
                    'debug' => array(
                        'missing_field' => $field,
                        'post_data' => $_POST
                    )
                ));
                return;
            }
        }

        // Sanitizzazione dati
        $nome = sanitize_text_field($_POST['nome']);
        $localita = sanitize_text_field($_POST['localita']);
        $descrizione = isset($_POST['descrizione']) ? sanitize_textarea_field($_POST['descrizione']) : '';

        error_log('RC Race Manager: [INFO] Dati sanitizzati:');
        error_log('RC Race Manager: [INFO] - Nome: ' . $nome);
        error_log('RC Race Manager: [INFO] - Località: ' . $localita);
        error_log('RC Race Manager: [INFO] - Descrizione: ' . $descrizione);

        // Test permessi scrittura
        $test_query = $wpdb->prepare("INSERT INTO $table_piste (nome) VALUES (%s)", 'test_permission');
        $can_write = $wpdb->query($test_query);
        if ($can_write === false) {
            error_log('RC Race Manager: [ERRORE] Test permessi scrittura fallito');
            error_log('RC Race Manager: [ERRORE] Errore MySQL: ' . $wpdb->last_error);
            wp_send_json_error(array(
                'message' => 'Errore di permessi database',
                'debug' => array(
                    'last_error' => $wpdb->last_error,
                    'last_query' => $wpdb->last_query
                )
            ));
            // Pulisci il test
            $wpdb->query("DELETE FROM $table_piste WHERE nome = 'test_permission'");
            return;
        }
        $wpdb->query("DELETE FROM $table_piste WHERE nome = 'test_permission'");
        error_log('RC Race Manager: [OK] Test permessi scrittura superato');

        // Preparazione dati per inserimento
        $data = array(
            'nome' => $nome,
            'localita' => $localita,
            'descrizione' => $descrizione,
            'approvato' => 0,
            'data_creazione' => current_time('mysql')
        );
        $format = array('%s', '%s', '%s', '%d', '%s');

        error_log('RC Race Manager: [INFO] Tentativo inserimento con dati: ' . print_r($data, true));
        error_log('RC Race Manager: [INFO] Format: ' . print_r($format, true));

        // Inserimento nel database
        $result = $wpdb->insert($table_piste, $data, $format);

        if ($result === false) {
            error_log('RC Race Manager: [ERRORE] Inserimento fallito');
            error_log('RC Race Manager: [ERRORE] Ultimo errore: ' . $wpdb->last_error);
            error_log('RC Race Manager: [ERRORE] Ultima query: ' . $wpdb->last_query);
            wp_send_json_error(array(
                'message' => 'Errore durante il salvataggio dei dati',
                'debug' => array(
                    'query' => $wpdb->last_query,
                    'error' => $wpdb->last_error,
                    'data' => $data
                )
            ));
        } else {
            error_log('RC Race Manager: [OK] Inserimento completato con successo');
            error_log('RC Race Manager: [INFO] ID inserito: ' . $wpdb->insert_id);
            wp_send_json_success(array(
                'message' => 'Pista registrata con successo. La richiesta verrà approvata da un amministratore.',
                'id' => $wpdb->insert_id
            ));
        }

        wp_die();
    }

    private function add_pilota_original(){
        error_log('RC Race Manager: [START] Inizio funzione add_pilota');
        error_log('RC Race Manager: [POST] Dati ricevuti: ' . print_r($_POST, true));

        if (!$this->verify_nonce()) {
            return;
        }

        global $wpdb;
        if (!$wpdb->check_connection()) {
            error_log('RC Race Manager: [ERRORE] Connessione al database fallita');
            wp_send_json_error(array('message' => 'Errore di connessione al database'));
            return;
        }

        $table_piloti = $wpdb->prefix . 'rc_piloti';
        $table_categorie = $wpdb->prefix . 'rc_categorie';

        // Verifica campi obbligatori
        $required_fields = array('nome', 'cognome', 'email', 'categoria_id', 'trasponder');
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                error_log("RC Race Manager: Campo mancante: $field");
                wp_send_json_error(array('message' => "Il campo $field è obbligatorio"));
                return;
            }
        }

        // Verifica esistenza categoria
        $categoria_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_categorie WHERE id = %d",
            intval($_POST['categoria_id'])
        ));

        if (!$categoria_exists) {
            error_log('RC Race Manager: Categoria non valida: ' . $_POST['categoria_id']);
            wp_send_json_error(array('message' => 'Categoria selezionata non valida'));
            return;
        }

        // Verifica email duplicata
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_piloti WHERE email = %s",
            $_POST['email']
        ));

        if ($exists) {
            error_log('RC Race Manager: Email duplicata: ' . $_POST['email']);
            wp_send_json_error(array('message' => 'Questa email è già registrata'));
            return;
        }

        // Ottieni l'ID dell'utente corrente
        $current_user_id = get_current_user_id();
        if (!$current_user_id) {
            error_log('RC Race Manager: Utente non autenticato');
            wp_send_json_error(array('message' => 'Devi essere autenticato per registrarti come pilota'));
            return;
        }

        // Verifica se l'utente è già registrato come pilota
        $pilota_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_piloti WHERE user_id = %d",
            $current_user_id
        ));

        if ($pilota_exists) {
            error_log('RC Race Manager: Utente già registrato come pilota');
            wp_send_json_error(array('message' => 'Sei già registrato come pilota'));
            return;
        }

        // Preparazione dati
        $data = array(
            'nome' => sanitize_text_field($_POST['nome']),
            'cognome' => sanitize_text_field($_POST['cognome']),
            'email' => sanitize_email($_POST['email']),
            'telefono' => isset($_POST['telefono']) ? sanitize_text_field($_POST['telefono']) : '',
            'categoria_id' => intval($_POST['categoria_id']),
            'trasponder' => sanitize_text_field($_POST['trasponder']),
            'user_id' => $current_user_id,
            'approvato' => 0,
            'data_registrazione' => current_time('mysql')
        );

        error_log('RC Race Manager: Tentativo inserimento con dati: ' . print_r($data, true));

        // Inserimento nel database
        $result = $wpdb->insert(
            $table_piloti,
            $data,
            array(
                '%s', // nome
                '%s', // cognome
                '%s', // email
                '%s', // telefono
                '%d', // categoria_id
                '%s', // trasponder
                '%d', // user_id
                '%d', // approvato
                '%s'  // data_registrazione
            )
        );

        if ($result === false) {
            error_log('RC Race Manager: Errore inserimento: ' . $wpdb->last_error);
            error_log('RC Race Manager: Query: ' . $wpdb->last_query);
            wp_send_json_error(array(
                'message' => 'Errore durante il salvataggio dei dati',
                'debug' => array(
                    'error' => $wpdb->last_error,
                    'query' => $wpdb->last_query
                )
            ));
        } else {
            error_log('RC Race Manager: Pilota inserito con successo. ID: ' . $wpdb->insert_id);
            wp_send_json_success(array(
                'message' => 'Registrazione completata con successo. In attesa di approvazione.',
                'id' => $wpdb->insert_id
            ));
        }
    }

    private function add_gara_original(){
        error_log('RC Race Manager: [START] Inizio funzione add_gara');
        error_log('RC Race Manager: [POST] Dati ricevuti: ' . print_r($_POST, true));

        if (!$this->verify_nonce()) {
            return;
        }

        global $wpdb;
        if (!$wpdb->check_connection()) {
            error_log('RC Race Manager: [ERRORE] Connessione al database fallita');
            wp_send_json_error(array('message' => 'Errore di connessione al database'));
            return;
        }

        $table_gare = $wpdb->prefix . 'rc_gare';
        $table_piste = $wpdb->prefix . 'rc_piste';

        // Verifica campi obbligatori
        $required_fields = array('nome', 'data_gara', 'pista_id', 'tipo_gara');
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                error_log("RC Race Manager: Campo mancante: $field");
                wp_send_json_error(array('message' => "Il campo $field è obbligatorio"));
                return;
            }
        }

        // Verifica validità del tipo gara
        $tipi_gara_validi = array('campionato', 'amichevole', 'torneo');
        if (!in_array($_POST['tipo_gara'], $tipi_gara_validi)) {
            error_log('RC Race Manager: Tipo gara non valido: ' . $_POST['tipo_gara']);
            wp_send_json_error(array('message' => 'Tipo gara non valido'));
            return;
        }

        // Verifica validità della data
        $data_gara = strtotime($_POST['data_gara']);
        if ($data_gara === false || $data_gara < time()) {
            error_log('RC Race Manager: Data non valida: ' . $_POST['data_gara']);
            wp_send_json_error(array('message' => 'La data della gara non è valida o è nel passato'));
            return;
        }

        // Verifica esistenza pista
        $pista_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_piste WHERE id = %d AND approvato = 1",
            intval($_POST['pista_id'])
        ));

        if (!$pista_exists) {
            error_log('RC Race Manager: Pista non valida: ' . $_POST['pista_id']);
            wp_send_json_error(array('message' => 'Pista selezionata non valida o non approvata'));
            return;
        }

        // Preparazione dati
        $data = array(
            'nome' => sanitize_text_field($_POST['nome']),
            'data_gara' => date('Y-m-d H:i:s', $data_gara),
            'pista_id' => intval($_POST['pista_id']),
            'tipo_gara' => sanitize_text_field($_POST['tipo_gara']),
            'descrizione' => isset($_POST['descrizione']) ? sanitize_textarea_field($_POST['descrizione']) : '',
            'approvato' => 0,
            'data_creazione' => current_time('mysql')
        );

        error_log('RC Race Manager: Tentativo inserimento con dati: ' . print_r($data, true));

        // Inserimento nel database
        $result = $wpdb->insert(
            $table_gare,
            $data,
            array(
                '%s', // nome
                '%s', // data_gara
                '%d', // pista_id
                '%s', // tipo_gara
                '%s', // descrizione
                '%d', // approvato
                '%s'  // data_creazione
            )
        );

        if ($result === false) {
            error_log('RC Race Manager: Errore inserimento: ' . $wpdb->last_error);
            error_log('RC Race Manager: Query: ' . $wpdb->last_query);
            wp_send_json_error(array(
                'message' => 'Errore durante il salvataggio dei dati',
                'debug' => array(
                    'error' => $wpdb->last_error,
                    'query' => $wpdb->last_query
                )
            ));
        } else {
            error_log('RC Race Manager: Gara inserita con successo. ID: ' . $wpdb->insert_id);
            wp_send_json_success(array(
                'message' => 'Gara registrata con successo. In attesa di approvazione.',
                'id' => $wpdb->insert_id
            ));
        }
    }



    public function enqueue_styles() {
        error_log('RC Race Manager: Caricamento stili');
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/rc-race-manager-public.css', array(), $this->version, 'all');
        wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css');
        wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
    }

    public function enqueue_scripts() {
        error_log('RC Race Manager: Inizio caricamento script');

        wp_enqueue_script('jquery');
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/rc-race-manager-public.js', array('jquery'), $this->version, true);
        wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', array(), '5.1.3', true);

        // Script specifico per il calendario
        wp_enqueue_script('rc-race-manager-calendar', plugin_dir_url(__FILE__) . 'js/rc-race-manager-calendar.js', array('jquery', 'bootstrap'), $this->version, true);

        // Genera un nuovo nonce per ogni caricamento della pagina
        $nonce = wp_create_nonce('rc_race_manager_nonce');
        error_log('RC Race Manager: Nuovo nonce generato: ' . $nonce);

        // Ottieni lo stato di autenticazione dell'utente
        $user_logged_in = $this->auth->is_user_logged_in();
        $current_user = $user_logged_in ? $this->auth->get_current_user() : null;

        // Localizza lo script con nonce, URL AJAX e stato utente
        wp_localize_script($this->plugin_name, 'rcRaceManager', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'security' => $nonce,
            'user_logged_in' => $user_logged_in,
            'current_user' => $current_user,
            'strings' => array(
                'error_saving' => 'Errore durante il salvataggio',
                'success_saving' => 'Salvataggio completato con successo',
                'network_error' => 'Errore di rete durante il salvataggio',
                'iscrizione_success' => 'Iscrizione completata con successo',
                'iscrizione_error' => 'Errore durante l\'iscrizione',
                'cancellazione_success' => 'Iscrizione cancellata con successo',
                'cancellazione_error' => 'Errore durante la cancellazione dell\'iscrizione'
            )
        ));

        error_log('RC Race Manager: Script localizzato con nonce e URL AJAX');
    }

    private function verify_nonce($action = 'rc_race_manager_nonce') {
        error_log('RC Race Manager: Verifica nonce, ricevuto: ' . (isset($_POST['security']) ? $_POST['security'] : 'non impostato'));

        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], $action)) {
            error_log('RC Race Manager: Verifica nonce fallita');
            wp_send_json_error(array(
                'message' => 'Errore di sicurezza: sessione scaduta. Ricarica la pagina.',
                'debug' => array(
                    'nonce_received' => isset($_POST['security']) ? $_POST['security'] : 'non impostato',
                    'action' => $_POST['action'] ?? 'non impostata'
                )
            ));
            return false;
        }
        error_log('RC Race Manager: Verifica nonce completata con successo');
        return true;
    }

    public function display_frontend_shortcode($atts) {
        error_log('RC Race Manager: Rendering frontend shortcode');
        ob_start();
        include_once 'partials/rc-race-manager-public-display.php';
        return ob_get_clean();
    }

    private function check_table_exists($table_name) {
        global $wpdb;
        error_log("RC Race Manager: Verifico esistenza tabella $table_name");
        $result = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
        error_log("RC Race Manager: Risultato verifica tabella $table_name: " . ($result === $table_name ? 'Esiste' : 'Non esiste'));
        return $result === $table_name;
    }

    public function load_section() {
        error_log('RC Race Manager: Inizio caricamento sezione');

        if (!$this->verify_nonce()) {
            return;
        }

        // Verifica e sanitizza la sezione richiesta
        $section = isset($_POST['section']) ? sanitize_text_field($_POST['section']) : '';
        error_log('RC Race Manager: Richiesta sezione: ' . $section);

        if (empty($section)) {
            error_log('RC Race Manager: Sezione non specificata');
            wp_send_json_error(array('message' => 'Sezione non specificata'));
            return;
        }

        // Costruisci e verifica il percorso del file
        $base_path = plugin_dir_path(__FILE__);
        $file_path = $base_path . 'partials/rc-race-manager-public-' . $section . '.php';

        error_log('RC Race Manager: Base path: ' . $base_path);
        error_log('RC Race Manager: Tentativo di caricare il file: ' . $file_path);
        error_log('RC Race Manager: Il file esiste?: ' . (file_exists($file_path) ? 'Sì' : 'No'));

        if (!file_exists($file_path)) {
            error_log('RC Race Manager: File non trovato: ' . $file_path);
            wp_send_json_error(array(
                'message' => 'Sezione non trovata',
                'debug' => array(
                    'base_path' => $base_path,
                    'file_path' => $file_path,
                    'section' => $section
                )
            ));
            return;
        }

        // Carica il contenuto
        ob_start();
        try {
            include $file_path;
            $content = ob_get_clean();

            if (empty($content)) {
                error_log('RC Race Manager: Contenuto vuoto per la sezione: ' . $section);
                throw new Exception('Contenuto vuoto');
            }

            error_log('RC Race Manager: Contenuto caricato con successo per la sezione: ' . $section);
            error_log('RC Race Manager: Lunghezza contenuto: ' . strlen($content));

            wp_send_json_success(array(
                'html' => $content,
                'message' => 'Contenuto caricato con successo'
            ));
        } catch (Exception $e) {
            ob_end_clean();
            error_log('RC Race Manager: Errore durante il caricamento: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => 'Errore nel caricamento del contenuto: ' . $e->getMessage(),
                'debug' => array(
                    'error' => $e->getMessage(),
                    'file' => $file_path
                )
            ));
        }
    }

    public function get_iscritti() {
        error_log('RC Race Manager: [START] Inizio funzione get_iscritti');
        error_log('RC Race Manager: [POST] Dati ricevuti: ' . print_r($_POST, true));

        if (!$this->verify_nonce()) {
            return;
        }

        if (!isset($_POST['gara_id'])) {
            error_log('RC Race Manager: [ERRORE] ID gara non specificato');
            wp_send_json_error(array('message' => 'ID gara non specificato'));
            return;
        }

        global $wpdb;
        $gara_id = intval($_POST['gara_id']);
        $table_iscrizioni = $wpdb->prefix . 'rc_iscrizioni_gara';
        $table_piloti = $wpdb->prefix . 'rc_piloti';
        $table_categorie = $wpdb->prefix . 'rc_categorie';
        $table_gare = $wpdb->prefix . 'rc_gare';

        // Verifica che la gara esista ed sia approvata
        $gara = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_gare WHERE id = %d AND approvato = 1",
            $gara_id
        ));

        if (!$gara) {
            error_log('RC Race Manager: [ERRORE] Gara non trovata o non approvata');
            wp_send_json_error(array('message' => 'Gara non disponibile'));
            return;
        }

        $iscritti = $wpdb->get_results($wpdb->prepare("
            SELECT p.*, c.nome as categoria_nome, i.data_iscrizione
            FROM $table_iscrizioni i
            JOIN $table_piloti p ON i.pilota_id = p.id
            LEFT JOIN $table_categorie c ON p.categoria_id = c.id
            WHERE i.gara_id = %d
            ORDER BY c.nome, p.cognome, p.nome ASC
        ", $gara_id));

        error_log('RC Race Manager: [INFO] Numero iscritti trovati: ' . count($iscritti));

        ob_start();
        if (empty($iscritti)) {
            echo '<div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Nessun pilota iscritto a questa gara.
                  </div>';
        } else {
            ?>
            <div class="table-responsive">
                <table class"table table-hover">                    <thead class="table-light">
                        <tr>
                            <th scope="col">Pilota</th>
                            <th scope="col">Categoria</th>
                            <th scope="col">Transponder</th>
                            <th scope="col">Data Iscrizione</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $current_categoria = '';
                        foreach ($iscritti as $iscritto): 
                            // Aggiungi header categoria se cambia
                            if ($current_categoria !== $iscritto->categoria_nome):
                                $current_categoria = $iscritto->categoria_nome;
                                ?>
                                <tr class="table-secondary">
                                    <td colspan="4">
                                        <strong>
                                            <i class="fas fa-trophy me-2"></i>
                                            <?php echo esc_html($current_categoria); ?>
                                        </strong>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-icon me-2">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <?php echo esc_html($iscritto->nome . ' ' . $iscritto->cognome); ?>
                                    </div>
                                </td>
                                <td><?php echo esc_html($iscritto->categoria_nome); ?></td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?php echo esc_html($iscritto->transponder); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($iscritto->data_iscrizione)); ?>
                                    </small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
        $html = ob_get_clean();

        error_log('RC Race Manager: [OK] Lista iscritti generata con successo');
        wp_send_json_success(array(
            'html' => $html,
            'count' => count($iscritti)
        ));
    }

    public function iscrivi_pilota() {
        error_log('RC Race Manager: [START] Inizio funzione iscrivi_pilota');
        error_log('RC Race Manager: [POST] Dati ricevuti: ' . print_r($_POST, true));

        if (!$this->verify_nonce() || !$this->check_auth()) {
            return;
        }

        if (!isset($_POST['gara_id'])) {
            error_log('RC Race Manager: [ERRORE] ID gara non specificato');
            wp_send_json_error(array('message' => 'ID gara non specificato'));
            return;
        }

        global $wpdb;
        $current_user_id = get_current_user_id();
        $gara_id = intval($_POST['gara_id']);

        // Get all pilots associated with the current user
        $table_piloti = $wpdb->prefix . 'rc_piloti';
        $piloti = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_piloti WHERE user_id = %d AND approvato = 1",
            $current_user_id
        ));

        if (empty($piloti)) {
            wp_send_json_error(array(
                'message' => 'Non hai ancora registrato nessun pilota',
                'show_registration' => true
            ));
            return;
        }

        // If pilota_id is not set, return the list of pilots for selection
        if (!isset($_POST['pilota_id'])) {
            ob_start();
            ?>
            <div class="modal-body">
                <h5>Seleziona il pilota da iscrivere:</h5>
                <div class="list-group">
                    <?php foreach ($piloti as $pilota): ?>
                    <button type="button" 
                            class="list-group-item list-group-item-action pilot-select-btn" 
                            data-pilota-id="<?php echo esc_attr($pilota->id); ?>"
                            data-gara-id="<?php echo esc_attr($gara_id); ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo esc_html($pilota->nome . ' ' . $pilota->cognome); ?></strong>
                                <small class="d-block text-muted">
                                    Trasponder: <?php echo esc_html($pilota->trasponder); ?>
                                </small>
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
            $html = ob_get_clean();
            wp_send_json_success(array('html' => $html));
            return;
        }

        // Continue with registration if pilota_id is set
        $pilota_id = intval($_POST['pilota_id']);
        $table_iscrizioni = $wpdb->prefix . 'rc_iscrizioni_gara';
        $table_gare = $wpdb->prefix . 'rc_gare';

        // Verify race exists and is approved
        $gara = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_gare WHERE id = %d AND approvato = 1",
            $gara_id
        ));

        if (!$gara) {
            wp_send_json_error(array('message' => 'Gara non disponibile'));
            return;
        }

        // Check if pilot is already registered
        $iscrizione_esistente = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_iscrizioni WHERE gara_id = %d AND pilota_id = %d",
            $gara_id,
            $pilota_id
        ));

        if ($iscrizione_esistente) {
            wp_send_json_error(array('message' => 'Questo pilota è già iscritto alla gara'));
            return;
        }

        // Insert registration
        $result = $wpdb->insert(
            $table_iscrizioni,
            array(
                'gara_id' => $gara_id,
                'pilota_id' => $pilota_id,
                'data_iscrizione' => current_time('mysql')
            ),
            array('%d', '%d', '%s')
        );

        if ($result === false) {
            error_log('RC Race Manager: Errore durante l\'iscrizione: ' . $wpdb->last_error);
            wp_send_json_error(array('message' => 'Errore durante l\'iscrizione'));
            return;
        }

        wp_send_json_success(array('message' => 'Iscrizione completata con successo'));
    }

    public function cancella_iscrizione() {
        error_log('RC Race Manager: [START] Inizio funzione cancella_iscrizione');
        error_log('RC Race Manager: [POST] Dati ricevuti: ' . print_r($_POST, true));

        if (!$this->verify_nonce() || !$this->check_auth()) {
            return;
        }

        if (!isset($_POST['gara_id']) || !isset($_POST['pilota_id'])) {
            error_log('RC Race Manager: [ERRORE] Parametri mancanti');
            wp_send_json_error(array('message' => 'Dati mancanti'));
            return;
        }

        global $wpdb;
        $gara_id = intval($_POST['gara_id']);
        $pilota_id = intval($_POST['pilota_id']);
        $table_iscrizioni = $wpdb->prefix . 'rc_iscrizioni_gara';
        $table_gare = $wpdb->prefix . 'rc_gare';

        // Verifica che la gara sia futura
        $gara = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_gare 
            WHERE id = %d AND data_gara > NOW()",
            $gara_id
        ));

        if (!$gara) {
            error_log('RC Race Manager: [ERRORE] Gara non trovata o già passata');
            wp_send_json_error(array('message' => 'Non puoi cancellare l\'iscrizione a una gara passata'));
            return;
        }

        // Verifica esistenza iscrizione
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_iscrizioni WHERE gara_id = %d AND pilota_id = %d",
            $gara_id, $pilota_id
        ));

        if (!$exists) {
            error_log('RC Race Manager: [ERRORE] Iscrizione non trovata');
            wp_send_json_error(array('message' => 'Iscrizione non trovata'));
            return;
        }

        // Cancella iscrizione
        $result = $wpdb->delete(
            $table_iscrizioni,
            array(
                'gara_id' => $gara_id,
                'pilota_id' => $pilota_id
            ),
            array('%d', '%d')
        );

        if ($result === false) {
            error_log('RC Race Manager: [ERRORE] Cancellazione fallita');
            error_log('RC Race Manager: [ERRORE] Ultimo errore: ' . $wpdb->last_error);
            wp_send_json_error(array('message' => 'Errore durante la cancellazione dell\'iscrizione'));
        } else {
            error_log('RC Race Manager: [OK] Iscrizione cancellata con successo');
            wp_send_json_success(array('message' => 'Iscrizione cancellata con successo'));
        }
    }

    public function esporta_pdf() {
        if (!$this->verify_nonce('rc_race_manager_nonce') || !$this->check_auth()) {
            return;
        }

        if (!isset($_GET['gara_id'])) {
            wp_die('ID gara non specificato');
            return;
        }

        global $wpdb;
        $gara_id = intval($_GET['gara_id']);

        // Verifica che l'utente sia il gestore della gara
        $current_user_id = get_current_user_id();
        $is_owner = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}rc_gare WHERE id = %d AND user_id = %d",
            $gara_id, $current_user_id
        ));

        if (!$is_owner) {
            wp_die('Non hai i permessi per esportare questa lista');
            return;
        }

        // Recupera i dati della gara e degli iscritti
        $gara = $wpdb->get_row($wpdb->prepare("
            SELECT g.*, p.nome as pista_nome 
            FROM {$wpdb->prefix}rc_gare g
            LEFT JOIN {$wpdb->prefix}rc_piste p ON g.pista_id = p.id
            WHERE g.id = %d
        ", $gara_id));

        $iscritti = $wpdb->get_results($wpdb->prepare("
            SELECT p.*, c.nome as categoria_nome, i.data_iscrizione
            FROM {$wpdb->prefix}rc_iscrizioni_gara i
            JOIN {$wpdb->prefix}rc_piloti p ON i.pilota_id = p.id
            LEFT JOIN {$wpdb->prefix}rc_categorie c ON p.categoria_id = c.id
            WHERE i.gara_id = %d
            ORDER BY c.nome, p.cognome, p.nome
        ", $gara_id));

        // Genera il PDF
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rc-race-manager-pdf.php';

        $pdf = new RC_Race_Manager_PDF('Lista Iscritti - ' . $gara->nome);

        // Aggiunge titolo e informazioni della gara
        $pdf->addTitle($gara->nome);
        $pdf->addText('Data: ' . date('d/m/Y H:i', strtotime($gara->data_gara)));
        $pdf->addText('Pista: ' . $gara->pista_nome);

        // Definisce le intestazioni della tabella
        $headers = array('Pilota', 'Categoria', 'Transponder', 'Data Iscrizione');

        // Prepara i dati degli iscritti
        $rows = array();
        foreach ($iscritti as $iscritto) {
            $rows[] = array(
                $iscritto->nome . ' ' . $iscritto->cognome,
                $iscritto->categoria_nome,
                $iscritto->transponder,
                date('d/m/Y H:i', strtotime($iscritto->data_iscrizione))
            );
        }

        // Aggiunge la tabella al PDF
        $pdf->addTable($headers, $rows);

        // Output del PDF
        $pdf->output();
        exit;
    }

    public function get_dettagli_gara() {
        error_log('RC Race Manager: [START] Inizio funzione get_dettagli_gara');
        error_log('RC Race Manager: [POST] Dati ricevuti: ' . print_r($_POST, true));

        if (!$this->verify_nonce()) {
            return;
        }

        if (!isset($_POST['gara_id'])) {
            error_log('RC Race Manager: [ERRORE] ID gara non specificato');
            wp_send_json_error(array('message' => 'ID gara non specificato'));
            return;
        }

        global $wpdb;
        $gara_id = intval($_POST['gara_id']);
        $table_gare = $wpdb->prefix . 'rc_gare';
        $table_piste = $wpdb->prefix . 'rc_piste';
        $table_iscrizioni = $wpdb->prefix . 'rc_iscrizioni_gara';
        $table_piloti = $wpdb->prefix . 'rc_piloti';
        $table_categorie = $wpdb->prefix . 'rc_categorie';

        // Recupera i dettagli della gara
        $gara = $wpdb->get_row($wpdb->prepare("
            SELECT g.*, p.nome as pista_nome, p.localita as pista_localita
            FROM $table_gare g
            JOIN $table_piste p ON g.pista_id = p.id
            WHERE g.id = %d AND g.approvato = 1
        ", $gara_id));

        if (!$gara) {
            error_log('RC Race Manager: [ERRORE] Gara non trovata o non approvata');
            wp_send_json_error(array('message' => 'Gara non disponibile'));
            return;
        }

        // Recupera gli iscritti
        $iscritti = $wpdb->get_results($wpdb->prepare("
            SELECT p.*, c.nome as categoria_nome, i.data_iscrizione
            FROM $table_iscrizioni i
            JOIN $table_piloti p ON i.pilota_id = p.id
            LEFT JOIN $table_categorie c ON p.categoria_id = c.id
            WHERE i.gara_id = %d
            ORDER BY c.nome, p.cognome, p.nome ASC
        ", $gara_id));

        // Verifica se l'utente corrente è iscritto
        $user_logged_in = $this->auth->is_user_logged_in();
        $is_iscritto = false;
        if ($user_logged_in) {
            $current_user = $this->auth->get_current_user();
            $is_iscritto = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*)
                FROM $table_iscrizioni i
                JOIN $table_piloti p ON i.pilota_id = p.id
                WHERE i.gara_id = %d AND p.user_id = %d
            ", $gara_id, $current_user['id'])) > 0;
        }

        // Genera HTML per i dettagli della gara
        ob_start();
        ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?php echo esc_html($gara->nome); ?></h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-map-marker-alt me-2"></i>Pista:</strong> 
                            <?php echo esc_html($gara->pista_nome . ' - ' . $gara->pista_localita); ?>
                        </p>
                        <p><strong><i class="fas fa-calendar-alt me-2"></i>Data:</strong> 
                            <?php echo date('d/m/Y H:i', strtotime($gara->data_gara)); ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-trophy me-2"></i>Tipo:</strong> 
                            <?php echo esc_html(ucfirst($gara->tipo_gara)); ?>
                        </p>
                        <?php if (!empty($gara->descrizione)): ?>
                            <p><strong><i class="fas fa-info-circle me-2"></i>Descrizione:</strong> 
                                <?php echo esc_html($gara->descrizione); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $dettagli_html = ob_get_clean();

        // Genera HTML per la lista iscritti
        ob_start();
        if (empty($iscritti)) {
            ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Nessun pilota iscritto a questa gara.
            </div>
            <?php
        } else {
            ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Pilota</th>
                            <th scope="col">Categoria</th>
                            <th scope="col">Transponder</th>
                            <th scope="col">Data Iscrizione</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $current_categoria = '';
                        foreach ($iscritti as $iscritto): 
                            if ($current_categoria !== $iscritto->categoria_nome):
                                $current_categoria = $iscritto->categoria_nome;
                                ?>
                                <tr class="table-secondary">
                                    <td colspan="4">
                                        <strong>
                                            <i class="fas fa-trophy me-2"></i>
                                            <?php echo esc_html($current_categoria); ?>
                                        </strong>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-icon me-2">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <?php echo esc_html($iscritto->nome . ' ' . $iscritto->cognome); ?>
                                    </div>
                                </td>
                                <td><?php echo esc_html($iscritto->categoria_nome); ?></td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?php echo esc_html($iscritto->transponder); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($iscritto->data_iscrizione)); ?>
                                    </small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
        $iscritti_html = ob_get_clean();

        error_log('RC Race Manager: [OK] Dettagli gara generati con successo');
        wp_send_json_success(array(
            'dettagli_html' => $dettagli_html,
            'iscritti_html' => $iscritti_html,
            'iscritto' => $is_iscritto,
            'count' => count($iscritti)
        ));
    }

    // ... 
}