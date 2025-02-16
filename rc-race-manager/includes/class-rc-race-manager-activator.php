<?php
if (!defined('ABSPATH')) {
    exit;
}

class RC_Race_Manager_Activator {
    public static function activate() {
        error_log('RC Race Manager: Inizio attivazione plugin');

        try {
            global $wpdb;
            if (!$wpdb) {
                throw new Exception('Oggetto $wpdb non disponibile');
            }

            if (!method_exists($wpdb, 'get_charset_collate')) {
                throw new Exception('Metodo get_charset_collate non disponibile');
            }

            $charset_collate = $wpdb->get_charset_collate();
            error_log('RC Race Manager: Charset e collation: ' . $charset_collate);

            // Verifica che il file dbDelta sia disponibile
            if (!function_exists('dbDelta')) {
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                if (!function_exists('dbDelta')) {
                    throw new Exception('Funzione dbDelta non disponibile');
                }
            }

            // Array delle tabelle da creare
            $tables = array(
                'rc_users' => "CREATE TABLE IF NOT EXISTS %s (
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
                ) $charset_collate",

                'rc_categorie' => "CREATE TABLE IF NOT EXISTS %s (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    nome varchar(50) NOT NULL,
                    descrizione text,
                    PRIMARY KEY  (id)
                ) $charset_collate",

                'rc_piloti' => "CREATE TABLE IF NOT EXISTS %s (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    nome varchar(50) NOT NULL,
                    cognome varchar(50) NOT NULL,
                    email varchar(100) NOT NULL,
                    telefono varchar(20),
                    trasponder varchar(50),
                    categoria_id mediumint(9),
                    user_id bigint(20),
                    approvato boolean DEFAULT 0,
                    data_registrazione datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY  (id),
                    UNIQUE KEY email (email),
                    FOREIGN KEY (categoria_id) REFERENCES %s(id),
                    FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}rc_users(id)
                ) $charset_collate",

                'rc_piste' => "CREATE TABLE IF NOT EXISTS %s (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    nome varchar(100) NOT NULL,
                    localita varchar(100) NOT NULL,
                    descrizione text,
                    user_id bigint(20),
                    approvato boolean DEFAULT 0,
                    data_creazione datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY  (id),
                    FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}rc_users(id)
                ) $charset_collate",

                'rc_gare' => "CREATE TABLE IF NOT EXISTS %s (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    nome varchar(100) NOT NULL,
                    data_gara datetime NOT NULL,
                    pista_id mediumint(9),
                    tipo_gara varchar(50) NOT NULL,
                    descrizione text,
                    user_id bigint(20),
                    approvato boolean DEFAULT 0,
                    data_creazione datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY  (id),
                    FOREIGN KEY (pista_id) REFERENCES %s(id),
                    FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}rc_users(id)
                ) $charset_collate",

                'rc_iscrizioni_gara' => "CREATE TABLE IF NOT EXISTS %s (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    gara_id mediumint(9) NOT NULL,
                    pilota_id mediumint(9) NOT NULL,
                    data_iscrizione datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY  (id),
                    UNIQUE KEY unique_iscrizione (gara_id, pilota_id),
                    FOREIGN KEY (gara_id) REFERENCES %s(id),
                    FOREIGN KEY (pilota_id) REFERENCES %s(id)
                ) $charset_collate"
            );

            try {
                error_log('RC Race Manager: Inizio creazione tabelle');

                // 1. Users (nessuna dipendenza)
                $table_users = $wpdb->prefix . 'rc_users';
                $sql = sprintf($tables['rc_users'], $table_users);
                error_log("RC Race Manager: Query creazione users: $sql");
                dbDelta($sql);

                if ($wpdb->last_error) {
                    throw new Exception("Errore creazione tabella users: " . $wpdb->last_error);
                }

                // 2. Categorie (nessuna dipendenza)
                $table_categorie = $wpdb->prefix . 'rc_categorie';
                $sql = sprintf($tables['rc_categorie'], $table_categorie);
                error_log("RC Race Manager: Query creazione categorie: $sql");
                dbDelta($sql);

                if ($wpdb->last_error) {
                    throw new Exception("Errore creazione tabella categorie: " . $wpdb->last_error);
                }

                // 3. Piste (dipende da users)
                $table_piste = $wpdb->prefix . 'rc_piste';
                $sql = sprintf($tables['rc_piste'], $table_piste);
                error_log("RC Race Manager: Query creazione piste: $sql");
                dbDelta($sql);

                if ($wpdb->last_error) {
                    throw new Exception("Errore creazione tabella piste: " . $wpdb->last_error);
                }

                // 4. Piloti (dipende da categorie e users)
                $table_piloti = $wpdb->prefix . 'rc_piloti';
                $sql = sprintf($tables['rc_piloti'], $table_piloti, $table_categorie);
                error_log("RC Race Manager: Query creazione piloti: $sql");
                dbDelta($sql);

                if ($wpdb->last_error) {
                    throw new Exception("Errore creazione tabella piloti: " . $wpdb->last_error);
                }

                // 5. Gare (dipende da piste e users)
                $table_gare = $wpdb->prefix . 'rc_gare';
                $sql = sprintf($tables['rc_gare'], $table_gare, $table_piste);
                error_log("RC Race Manager: Query creazione gare: $sql");
                dbDelta($sql);

                if ($wpdb->last_error) {
                    throw new Exception("Errore creazione tabella gare: " . $wpdb->last_error);
                }

                // 6. Iscrizioni Gara (dipende da gare e piloti)
                $table_iscrizioni = $wpdb->prefix . 'rc_iscrizioni_gara';
                $sql = sprintf($tables['rc_iscrizioni_gara'], $table_iscrizioni, $table_gare, $table_piloti);
                error_log("RC Race Manager: Query creazione iscrizioni gara: $sql");
                dbDelta($sql);

                if ($wpdb->last_error) {
                    throw new Exception("Errore creazione tabella iscrizioni: " . $wpdb->last_error);
                }

                error_log('RC Race Manager: Fine creazione tabelle');

                // Inserimento categorie di default solo se la tabella è vuota
                $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_categorie");
                if ($count == 0) {
                    error_log('RC Race Manager: Inserimento categorie di default');
                    $categorie_default = array(
                        array('nome' => '1/8 Buggy', 'descrizione' => 'Categoria 1/8 Buggy'),
                        array('nome' => '1/10 Touring', 'descrizione' => 'Categoria 1/10 Touring'),
                        array('nome' => '1/5 Large Scale', 'descrizione' => 'Categoria 1/5 Large Scale')
                    );

                    foreach ($categorie_default as $categoria) {
                        $result = $wpdb->insert(
                            $table_categorie,
                            $categoria,
                            array('%s', '%s')
                        );
                        if ($result === false) {
                            error_log("RC Race Manager: Errore inserimento categoria {$categoria['nome']}: " . $wpdb->last_error);
                        } else {
                            error_log("RC Race Manager: Categoria {$categoria['nome']} inserita con successo");
                        }
                    }
                }

            } catch (Exception $e) {
                error_log("RC Race Manager - Errore durante la creazione delle tabelle: " . $e->getMessage());
                throw $e;
            }

        } catch (Exception $e) {
            error_log("RC Race Manager - Errore attivazione: " . $e->getMessage());
            error_log("RC Race Manager - Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
}