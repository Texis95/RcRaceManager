<?php
class RC_Race_Manager_Activator {
    public static function activate() {
        error_log('RC Race Manager: Inizio attivazione plugin');

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        error_log('RC Race Manager: Charset e collation: ' . $charset_collate);

        // Array delle tabelle da creare
        $tables = array(
            'rc_categorie' => "CREATE TABLE IF NOT EXISTS %s (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                nome varchar(50) NOT NULL,
                descrizione text,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            'rc_piloti' => "CREATE TABLE IF NOT EXISTS %s (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                nome varchar(50) NOT NULL,
                cognome varchar(50) NOT NULL,
                email varchar(100) NOT NULL,
                telefono varchar(20),
                trasponder varchar(50),
                categoria_id mediumint(9),
                approvato boolean DEFAULT 0,
                data_registrazione datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                UNIQUE KEY email (email),
                FOREIGN KEY (categoria_id) REFERENCES %s(id)
            ) $charset_collate;",

            'rc_piste' => "CREATE TABLE IF NOT EXISTS %s (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                nome varchar(100) NOT NULL,
                localita varchar(100) NOT NULL,
                descrizione text,
                approvato boolean DEFAULT 0,
                data_creazione datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            'rc_gare' => "CREATE TABLE IF NOT EXISTS %s (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                titolo varchar(100) NOT NULL,
                data date NOT NULL,
                pista_id mediumint(9),
                descrizione text,
                approvato boolean DEFAULT 0,
                data_registrazione datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                FOREIGN KEY (pista_id) REFERENCES %s(id)
            ) $charset_collate;"
        );

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Creazione delle tabelle nell'ordine corretto
        try {
            error_log('RC Race Manager: Inizio creazione tabelle');

            // 1. Categorie (nessuna dipendenza)
            $table_categorie = $wpdb->prefix . 'rc_categorie';
            $sql = sprintf($tables['rc_categorie'], $table_categorie);
            dbDelta($sql);
            error_log("RC Race Manager: Query creazione categorie: $sql");
            error_log("RC Race Manager: Risultato dbDelta categorie: " . print_r($wpdb->last_error, true));

            // 2. Piste (nessuna dipendenza)
            $table_piste = $wpdb->prefix . 'rc_piste';
            $sql = sprintf($tables['rc_piste'], $table_piste);
            dbDelta($sql);
            error_log("RC Race Manager: Query creazione piste: $sql");
            error_log("RC Race Manager: Risultato dbDelta piste: " . print_r($wpdb->last_error, true));

            // 3. Piloti (dipende da categorie)
            $table_piloti = $wpdb->prefix . 'rc_piloti';
            $sql = sprintf($tables['rc_piloti'], $table_piloti, $table_categorie);
            dbDelta($sql);
            error_log("RC Race Manager: Query creazione piloti: $sql");
            error_log("RC Race Manager: Risultato dbDelta piloti: " . print_r($wpdb->last_error, true));

            // 4. Gare (dipende da piste)
            $table_gare = $wpdb->prefix . 'rc_gare';
            $sql = sprintf($tables['rc_gare'], $table_gare, $table_piste);
            dbDelta($sql);
            error_log("RC Race Manager: Query creazione gare: $sql");
            error_log("RC Race Manager: Risultato dbDelta gare: " . print_r($wpdb->last_error, true));

            // Verifica la creazione delle tabelle
            $tables_to_check = array($table_categorie, $table_piste, $table_piloti, $table_gare);
            foreach ($tables_to_check as $table) {
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
                error_log("RC Race Manager: Verifica tabella $table - " . ($table_exists ? "Creata" : "Non creata"));
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
            } else {
                error_log("RC Race Manager: Categorie già presenti, skip inserimento default");
            }

        } catch (Exception $e) {
            error_log("RC Race Manager - Errore attivazione: " . $e->getMessage());
            error_log("RC Race Manager - Stack trace: " . $e->getTraceAsString());
        }
    }
}