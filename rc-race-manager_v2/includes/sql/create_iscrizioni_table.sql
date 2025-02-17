-- Creazione tabella iscrizioni_gara se non esiste
CREATE TABLE IF NOT EXISTS `{prefix}rc_iscrizioni_gara` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `gara_id` bigint(20) unsigned NOT NULL,
    `pilota_id` bigint(20) unsigned NOT NULL,
    `data_iscrizione` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_iscrizione` (`gara_id`, `pilota_id`),
    CONSTRAINT `fk_iscrizione_gara` FOREIGN KEY (`gara_id`) 
        REFERENCES `{prefix}rc_gare` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_iscrizione_pilota` FOREIGN KEY (`pilota_id`) 
        REFERENCES `{prefix}rc_piloti` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
