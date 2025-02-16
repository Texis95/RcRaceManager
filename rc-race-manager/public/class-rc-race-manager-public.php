<?php
class RC_Race_Manager_Public {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Registra gli shortcode
        add_shortcode('rc_race_manager_frontend', array($this, 'display_frontend_shortcode'));

        // Registra gli endpoint AJAX - IMPORTANTE: aggiungere sia per utenti loggati che non loggati
        add_action('wp_ajax_rc_race_manager_add_pista', array($this, 'add_pista'));
        add_action('wp_ajax_nopriv_rc_race_manager_add_pista', array($this, 'add_pista'));

        add_action('wp_ajax_rc_race_manager_add_pilota', array($this, 'add_pilota'));
        add_action('wp_ajax_nopriv_rc_race_manager_add_pilota', array($this, 'add_pilota'));

        add_action('wp_ajax_rc_race_manager_add_gara', array($this, 'add_gara'));
        add_action('wp_ajax_nopriv_rc_race_manager_add_gara', array($this, 'add_gara'));

        add_action('wp_ajax_rc_race_manager_load_section', array($this, 'load_section'));
        add_action('wp_ajax_nopriv_rc_race_manager_load_section', array($this, 'load_section'));

        // Log di inizializzazione
        error_log('RC Race Manager: Inizializzazione classe public');
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

        // Crea un nuovo nonce per la sicurezza AJAX
        $nonce = wp_create_nonce('rc_race_manager_nonce');
        error_log('RC Race Manager: Nonce generato per JS: ' . $nonce);

        // Localizza lo script con nonce e URL AJAX
        wp_localize_script($this->plugin_name, 'rcRaceManager', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => $nonce,
            'strings' => array(
                'error_saving' => 'Errore durante il salvataggio',
                'success_saving' => 'Salvataggio completato con successo',
                'network_error' => 'Errore di rete durante il salvataggio'
            )
        ));
        error_log('RC Race Manager: Script localizzato con successo');
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

    public function add_pilota() {
        error_log('RC Race Manager: [START] Inizio funzione add_pilota');
        error_log('RC Race Manager: [POST] Dati ricevuti: ' . print_r($_POST, true));

        // Test connessione database
        global $wpdb;
        if (!$wpdb->check_connection()) {
            error_log('RC Race Manager: [ERRORE CRITICO] Connessione al database fallita');
            wp_send_json_error(array(
                'message' => 'Errore di connessione al database',
                'debug' => array(
                    'last_error' => $wpdb->last_error,
                    'db_connected' => $wpdb->check_connection()
                )
            ));
            return;
        }
        error_log('RC Race Manager: [OK] Connessione al database verificata');

        // Verifica nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rc_race_manager_nonce')) {
            error_log('RC Race Manager: [ERRORE] Verifica nonce fallita');
            error_log('RC Race Manager: [ERRORE] Nonce ricevuto: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'non impostato'));
            error_log('RC Race Manager: [ERRORE] Nonce atteso: ' . wp_create_nonce('rc_race_manager_nonce'));
            wp_send_json_error(array(
                'message' => 'Errore di sicurezza: sessione scaduta. Ricarica la pagina.',
                'debug' => array(
                    'nonce_received' => isset($_POST['nonce']) ? $_POST['nonce'] : 'non impostato',
                    'action' => $_POST['action'] ?? 'non impostata',
                    'current_user' => wp_get_current_user()->ID
                )
            ));
            return;
        }
        error_log('RC Race Manager: [OK] Verifica nonce completata con successo');

        $table_piloti = $wpdb->prefix . 'rc_piloti';
        $table_categorie = $wpdb->prefix . 'rc_categorie';
        error_log('RC Race Manager: [INFO] Nome tabelle - Piloti: ' . $table_piloti . ', Categorie: ' . $table_categorie);

        // Test esistenza tabelle con query diretta
        $piloti_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_piloti'");
        $categorie_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_categorie'");

        error_log('RC Race Manager: [INFO] Test esistenza tabelle:');
        error_log('RC Race Manager: [INFO] - Piloti: ' . ($piloti_exists === $table_piloti ? 'Esiste' : 'Non esiste'));
        error_log('RC Race Manager: [INFO] - Categorie: ' . ($categorie_exists === $table_categorie ? 'Esiste' : 'Non esiste'));

        if ($piloti_exists !== $table_piloti || $categorie_exists !== $table_categorie) {
            error_log('RC Race Manager: [ERRORE] Una o più tabelle non trovate');
            wp_send_json_error(array(
                'message' => 'Errore di configurazione del database',
                'debug' => array(
                    'tables' => array(
                        'piloti' => array(
                            'name' => $table_piloti,
                            'exists' => $piloti_exists === $table_piloti
                        ),
                        'categorie' => array(
                            'name' => $table_categorie,
                            'exists' => $categorie_exists === $table_categorie
                        )
                    ),
                    'wpdb_prefix' => $wpdb->prefix,
                    'all_tables' => $wpdb->get_results("SHOW TABLES", ARRAY_N)
                )
            ));
            return;
        }

        // Log della struttura delle tabelle
        $piloti_structure = $wpdb->get_results("DESCRIBE $table_piloti");
        $categorie_structure = $wpdb->get_results("DESCRIBE $table_categorie");
        error_log('RC Race Manager: [INFO] Struttura tabella piloti: ' . print_r($piloti_structure, true));
        error_log('RC Race Manager: [INFO] Struttura tabella categorie: ' . print_r($categorie_structure, true));

        // Verifica e sanitizza i campi obbligatori
        $required_fields = array('nome', 'cognome', 'email', 'categoria_id', 'trasponder');
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
        $cognome = sanitize_text_field($_POST['cognome']);
        $email = sanitize_email($_POST['email']);
        $telefono = isset($_POST['telefono']) ? sanitize_text_field($_POST['telefono']) : '';
        $categoria_id = intval($_POST['categoria_id']);
        $trasponder = sanitize_text_field($_POST['trasponder']);

        error_log('RC Race Manager: [INFO] Dati sanitizzati:');
        error_log('RC Race Manager: [INFO] - Nome: ' . $nome);
        error_log('RC Race Manager: [INFO] - Cognome: ' . $cognome);
        error_log('RC Race Manager: [INFO] - Email: ' . $email);
        error_log('RC Race Manager: [INFO] - Categoria ID: ' . $categoria_id);

        // Test permessi scrittura
        $test_query = $wpdb->prepare("INSERT INTO $table_piloti (nome, cognome) VALUES (%s, %s)", 'test_permission', 'test');
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
            $wpdb->query("DELETE FROM $table_piloti WHERE nome = 'test_permission'");
            return;
        }
        $wpdb->query("DELETE FROM $table_piloti WHERE nome = 'test_permission'");
        error_log('RC Race Manager: [OK] Test permessi scrittura superato');

        // Verifica esistenza categoria
        $categoria_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_categorie WHERE id = %d",
            $categoria_id
        ));

        if (!$categoria_exists) {
            error_log('RC Race Manager: [ERRORE] Categoria non trovata - ID: ' . $categoria_id);
            wp_send_json_error(array('message' => 'Categoria selezionata non valida'));
            return;
        }
        error_log('RC Race Manager: [OK] Categoria verificata con successo');

        // Verifica email duplicata
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_piloti WHERE email = %s",
            $email
        ));

        if ($exists) {
            error_log('RC Race Manager: [ERRORE] Email già registrata - ' . $email);
            wp_send_json_error(array('message' => 'Questa email è già registrata'));
            return;
        }
        error_log('RC Race Manager: [OK] Email verificata con successo');

        error_log('RC Race Manager: [INFO] Tentativo inserimento pilota - Nome: ' . $nome . ' ' . $cognome);

        // Inserimento nel database
        $result = $wpdb->insert(
            $table_piloti,
            array(
                'nome' => $nome,
                'cognome' => $cognome,
                'email' => $email,
                'telefono' => $telefono,
                'categoria_id' => $categoria_id,
                'trasponder' => $trasponder,
                'approvato' => 0,
                'data_registrazione' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s')
        );

        if ($result === false) {
            error_log('RC Race Manager: [ERRORE] Inserimento fallito');
            error_log('RC Race Manager: [ERRORE] Ultimo errore: ' . $wpdb->last_error);
            error_log('RC Race Manager: [ERRORE] Ultima query: ' . $wpdb->last_query);
            wp_send_json_error(array(
                'message' => 'Errore durante il salvataggio dei dati',
                'debug' => array(
                    'query' => $wpdb->last_query,
                    'error' => $wpdb->last_error,
                    'data' => array(
                        'nome' => $nome,
                        'cognome' => $cognome,
                        'categoria_id' => $categoria_id
                    )
                )
            ));
        } else {
            error_log('RC Race Manager: [OK] Inserimento completato con successo');
            error_log('RC Race Manager: [INFO] ID inserito: ' . $wpdb->insert_id);
            wp_send_json_success(array(
                'message' => 'Registrazione completata con successo. Un amministratore approverà la tua richiesta.',
                'id' => $wpdb->insert_id
            ));
        }

        wp_die();
    }



    public function add_pista() {
        error_log('RC Race Manager: [START] Inizio funzione add_pista');
        error_log('RC Race Manager: [POST] Dati ricevuti: ' . print_r($_POST, true));

        // Test connessione database
        global $wpdb;
        if (!$wpdb->check_connection()) {
            error_log('RC Race Manager: [ERRORE CRITICO] Connessione al database fallita');
            wp_send_json_error(array(
                'message' => 'Errore di connessione al database',
                'debug' => array(
                    'last_error' => $wpdb->last_error,
                    'db_connected' => $wpdb->check_connection()
                )
            ));
            return;
        }
        error_log('RC Race Manager: [OK] Connessione al database verificata');

        // Verifica nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rc_race_manager_nonce')) {
            error_log('RC Race Manager: [ERRORE] Verifica nonce fallita');
            error_log('RC Race Manager: [ERRORE] Nonce ricevuto: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'non impostato'));
            error_log('RC Race Manager: [ERRORE] Nonce atteso: ' . wp_create_nonce('rc_race_manager_nonce'));
            wp_send_json_error(array(
                'message' => 'Errore di sicurezza: sessione scaduta. Ricarica la pagina.',
                'debug' => array(
                    'nonce_received' => isset($_POST['nonce']) ? $_POST['nonce'] : 'non impostato',
                    'action' => $_POST['action'] ?? 'non impostata',
                    'current_user' => wp_get_current_user()->ID
                )
            ));
            return;
        }
        error_log('RC Race Manager: [OK] Verifica nonce completata con successo');

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

    public function load_section() {
        error_log('RC Race Manager: Inizio caricamento sezione');

        // Verifica nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rc_race_manager_nonce')) {
            error_log('RC Race Manager: Verifica nonce fallita per load_section');
            error_log('RC Race Manager: Nonce ricevuto: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'non impostato'));
            wp_send_json_error(array('message' => 'Errore di sicurezza'));
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
    public function add_gara() {
        error_log('RC Race Manager: [START] Inizio funzione add_gara');
        error_log('RC Race Manager: [POST] Dati ricevuti: ' . print_r($_POST, true));

        // Test connessione database
        global $wpdb;
        if (!$wpdb->check_connection()) {
            error_log('RC Race Manager: [ERRORE CRITICO] Connessione al database fallita');
            wp_send_json_error(array(
                'message' => 'Errore di connessione al database',
                'debug' => array(
                    'last_error' => $wpdb->last_error,
                    'db_connected' => $wpdb->check_connection()
                )
            ));
            return;
        }
        error_log('RC Race Manager: [OK] Connessione al database verificata');

        // Verifica nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rc_race_manager_nonce')) {
            error_log('RC Race Manager: [ERRORE] Verifica nonce fallita');
            error_log('RC Race Manager: [ERRORE] Nonce ricevuto: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'non impostato'));
            error_log('RC Race Manager: [ERRORE] Nonce atteso: ' . wp_create_nonce('rc_race_manager_nonce'));
            wp_send_json_error(array(
                'message' => 'Errore di sicurezza: sessione scaduta. Ricarica la pagina.',
                'debug' => array(
                    'nonce_received' => isset($_POST['nonce']) ? $_POST['nonce'] : 'non impostato',
                    'action' => $_POST['action'] ?? 'non impostata',
                    'current_user' => wp_get_current_user()->ID
                )
            ));
            return;
        }
        error_log('RC Race Manager: [OK] Verifica nonce completata con successo');

        $table_gare = $wpdb->prefix . 'rc_gare';
        $table_piste = $wpdb->prefix . 'rc_piste';
        error_log('RC Race Manager: [INFO] Nome tabelle - Gare: ' . $table_gare . ', Piste: ' . $table_piste);

        // Test esistenza tabelle con query diretta
        $gare_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_gare'");
        $piste_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_piste'");

        error_log('RC Race Manager: [INFO] Test esistenza tabelle:');
        error_log('RC Race Manager: [INFO] - Gare: ' . ($gare_exists === $table_gare ? 'Esiste' : 'Non esiste'));
        error_log('RC Race Manager: [INFO] - Piste: ' . ($piste_exists === $table_piste ? 'Esiste' : 'Non esiste'));

        if ($gare_exists !== $table_gare || $piste_exists !== $table_piste) {
            error_log('RC Race Manager: [ERRORE] Una o più tabelle non trovate');
            wp_send_json_error(array(
                'message' => 'Errore di configurazione del database',
                'debug' => array(
                    'tables' => array(
                        'gare' => array(
                            'name' => $table_gare,
                            'exists' => $gare_exists === $table_gare
                        ),
                        'piste' => array(
                            'name' => $table_piste,
                            'exists' => $piste_exists === $table_piste
                        )
                    ),
                    'wpdb_prefix' => $wpdb->prefix,
                    'all_tables' => $wpdb->get_results("SHOW TABLES", ARRAY_N)
                )
            ));
            return;
        }

        // Log della struttura delle tabelle
        $gare_structure = $wpdb->get_results("DESCRIBE $table_gare");
        error_log('RC Race Manager: [INFO] Struttura tabella gare: ' . print_r($gare_structure, true));

        // Verifica e sanitizza i campi obbligatori
        $required_fields = array('nome', 'data_gara', 'pista_id', 'tipo_gara');
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
        $data_gara = sanitize_text_field($_POST['data_gara']);
        $pista_id = intval($_POST['pista_id']);
        $tipo_gara = sanitize_text_field($_POST['tipo_gara']);
        $descrizione = isset($_POST['descrizione']) ? sanitize_textarea_field($_POST['descrizione']) : '';

        error_log('RC Race Manager: [INFO] Dati sanitizzati:');
        error_log('RC Race Manager: [INFO] - Nome: ' . $nome);
        error_log('RC Race Manager: [INFO] - Data: ' . $data_gara);
        error_log('RC Race Manager: [INFO] - Pista ID: ' . $pista_id);
        error_log('RC Race Manager: [INFO] - Tipo: ' . $tipo_gara);

        // Test permessi scrittura
        $test_query = $wpdb->prepare("INSERT INTO $table_gare (nome) VALUES (%s)", 'test_permission');
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
            $wpdb->query("DELETE FROM $table_gare WHERE nome = 'test_permission'");
            return;
        }
        $wpdb->query("DELETE FROM $table_gare WHERE nome = 'test_permission'");
        error_log('RC Race Manager: [OK] Test permessi scrittura superato');

        // Verifica esistenza pista
        $pista_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_piste WHERE id = %d AND approvato = 1",
            $pista_id
        ));

        if (!$pista_exists) {
            error_log('RC Race Manager: [ERRORE] Pista non trovata o non approvata - ID: ' . $pista_id);
            wp_send_json_error(array('message' => 'Pista selezionata non valida o non ancora approvata'));
            return;
        }
        error_log('RC Race Manager: [OK] Pista verificata con successo');

        // Preparazione dati per inserimento
        $data = array(
            'nome' => $nome,
            'data_gara' => $data_gara,
            'pista_id' => $pista_id,
            'tipo_gara' => $tipo_gara,
            'descrizione' => $descrizione,
            'approvato' => 0,
            'data_creazione' => current_time('mysql')
        );
        $format = array('%s', '%s', '%d', '%s', '%s', '%d', '%s');

        error_log('RC Race Manager: [INFO] Tentativo inserimento gara con dati: ' .print_r($data, true));

        // Inserimento nel database
        $result = $wpdb->insert($table_gare, $data, $format);

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
                'message' => 'Gara registrata con successo. La richiesta verrà approvata da un amministratore.',
                'id' => $wpdb->insert_id
            ));
        }

        wp_die();
    }

}