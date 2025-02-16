<?php
// Protezione accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_gare = $wpdb->prefix . 'rc_gare';
$table_piste = $wpdb->prefix . 'rc_piste';
$table_iscrizioni = $wpdb->prefix . 'rc_iscrizioni_gara';
$table_piloti = $wpdb->prefix . 'rc_piloti';

// Query per ottenere l'elenco delle gare approvate
$gare = $wpdb->get_results("
    SELECT g.*, p.nome as pista_nome, u.display_name as gestore_nome,
           (SELECT COUNT(*) FROM $table_iscrizioni i WHERE i.gara_id = g.id) as num_iscritti
    FROM $table_gare g 
    LEFT JOIN $table_piste p ON g.pista_id = p.id 
    LEFT JOIN wp_users u ON g.user_id = u.ID
    WHERE g.approvato = 1 
    AND g.data_gara >= CURDATE()
    ORDER BY g.data_gara ASC
");

// Definizione tipi di gara
$tipi_gara = array(
    'campionato' => 'Campionato',
    'amichevole' => 'Amichevole',
    'torneo' => 'Torneo'
);

// Verifica se l'utente è autenticato
$is_logged_in = isset($this) && method_exists($this->auth, 'is_user_logged_in') ? $this->auth->is_user_logged_in() : false;
$current_user_id = $is_logged_in ? get_current_user_id() : 0;

// Se l'utente è loggato, verifica se è un pilota
$pilota_id = 0;
if ($is_logged_in) {
    $pilota_id = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_piloti WHERE user_id = %d AND approvato = 1",
        $current_user_id
    ));
}
?>

<div class="container">
    <h2 class="mb-4">Calendario Gare</h2>

    <?php if (empty($gare)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> Non ci sono gare programmate al momento.
        </div>
    <?php else: ?>
        <?php foreach ($gare as $gara): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?php echo esc_html($gara->nome); ?></h5>
                    <span class="badge bg-light text-primary"><?php echo esc_html($tipi_gara[$gara->tipo_gara] ?? $gara->tipo_gara); ?></span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <p class="mb-2">
                                <i class="fas fa-calendar me-2"></i>
                                <strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($gara->data_gara)); ?>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <strong>Pista:</strong> <?php echo esc_html($gara->pista_nome); ?>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-user me-2"></i>
                                <strong>Gestore:</strong> <?php echo esc_html($gara->gestore_nome); ?>
                            </p>
                            <?php if ($gara->descrizione): ?>
                                <p class="mb-2">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <?php echo esc_html($gara->descrizione); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-end">
                            <p class="mb-3">
                                <i class="fas fa-users me-2"></i>
                                <strong>Iscritti:</strong> <?php echo $gara->num_iscritti; ?>
                            </p>
                            <div class="btn-group">
                                <button type="button" 
                                        class="btn btn-outline-primary" 
                                        onclick="visualizzaIscritti(<?php echo $gara->id; ?>)">
                                    <i class="fas fa-list me-2"></i>Lista Iscritti
                                </button>
                                <?php if ($gara->user_id == $current_user_id): ?>
                                    <button type="button" 
                                            class="btn btn-outline-primary"
                                            onclick="esportaPDF(<?php echo $gara->id; ?>)">
                                        <i class="fas fa-file-pdf me-2"></i>Esporta PDF
                                    </button>
                                <?php endif; ?>
                            </div>
                            <?php if ($is_logged_in && $pilota_id): ?>
                                <?php
                                $iscritto = $wpdb->get_var($wpdb->prepare(
                                    "SELECT COUNT(*) FROM $table_iscrizioni WHERE gara_id = %d AND pilota_id = %d",
                                    $gara->id, $pilota_id
                                ));
                                ?>
                                <?php if (!$iscritto): ?>
                                    <button type="button" 
                                            class="btn btn-primary mt-2" 
                                            onclick="iscrivitiGara(<?php echo $gara->id; ?>, <?php echo $pilota_id; ?>)">
                                        <i class="fas fa-plus me-2"></i>Iscriviti
                                    </button>
                                <?php else: ?>
                                    <button type="button" 
                                            class="btn btn-danger mt-2" 
                                            onclick="cancellaIscrizione(<?php echo $gara->id; ?>, <?php echo $pilota_id; ?>)">
                                        <i class="fas fa-times me-2"></i>Cancella Iscrizione
                                    </button>
                                <?php endif; ?>
                            <?php elseif (!$is_logged_in): ?>
                                <div class="alert alert-info mt-2">
                                    <i class="fas fa-info-circle me-2"></i>Accedi per iscriverti
                                </div>
                            <?php elseif (!$pilota_id): ?>
                                <div class="alert alert-warning mt-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Registrati come pilota per iscriverti
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal Lista Iscritti -->
<div class="modal fade" id="modalIscritti" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lista Iscritti</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="listaIscritti"></div>
            </div>
        </div>
    </div>
</div>

<script>
function visualizzaIscritti(garaId) {
    jQuery.ajax({
        url: rcRaceManager.ajaxurl,
        type: 'POST',
        data: {
            action: 'rc_race_manager_get_iscritti',
            gara_id: garaId,
            security: rcRaceManager.security
        },
        success: function(response) {
            if (response.success) {
                jQuery('#listaIscritti').html(response.data.html);
                new bootstrap.Modal(document.getElementById('modalIscritti')).show();
            } else {
                alert(response.data.message);
            }
        }
    });
}

function iscrivitiGara(garaId, pilotaId) {
    if (!confirm('Confermi l\'iscrizione alla gara?')) return;

    jQuery.ajax({
        url: rcRaceManager.ajaxurl,
        type: 'POST',
        data: {
            action: 'rc_race_manager_iscrivi_pilota',
            gara_id: garaId,
            pilota_id: pilotaId,
            security: rcRaceManager.security
        },
        success: function(response) {
            if (response.success) {
                alert(response.data.message);
                location.reload();
            } else {
                alert(response.data.message);
            }
        }
    });
}

function cancellaIscrizione(garaId, pilotaId) {
    if (!confirm('Sei sicuro di voler cancellare l\'iscrizione?')) return;

    jQuery.ajax({
        url: rcRaceManager.ajaxurl,
        type: 'POST',
        data: {
            action: 'rc_race_manager_cancella_iscrizione',
            gara_id: garaId,
            pilota_id: pilotaId,
            security: rcRaceManager.security
        },
        success: function(response) {
            if (response.success) {
                alert(response.data.message);
                location.reload();
            } else {
                alert(response.data.message);
            }
        }
    });
}

function esportaPDF(garaId) {
    window.location.href = rcRaceManager.ajaxurl + '?action=rc_race_manager_esporta_pdf&gara_id=' + garaId + '&security=' + rcRaceManager.security;
}
</script>