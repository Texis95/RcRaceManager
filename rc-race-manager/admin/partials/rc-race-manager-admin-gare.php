<?php
// Protezione accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_gare = $wpdb->prefix . 'rc_gare';
$table_piste = $wpdb->prefix . 'rc_piste';

// Gestione approvazione/rifiuto
if (isset($_POST['action']) && isset($_POST['gara_id'])) {
    $gara_id = intval($_POST['gara_id']);
    $action = sanitize_text_field($_POST['action']);

    if ($action === 'approva') {
        $wpdb->update(
            $table_gare,
            array('approvato' => 1),
            array('id' => $gara_id),
            array('%d'),
            array('%d')
        );
    } elseif ($action === 'rifiuta') {
        $wpdb->update(
            $table_gare,
            array('approvato' => 0),
            array('id' => $gara_id),
            array('%d'),
            array('%d')
        );
    }
}

// Definizione tipi di gara
$tipi_gara = array(
    'campionato' => 'Campionato',
    'amichevole' => 'Amichevole',
    'torneo' => 'Torneo'
);

// Query per ottenere l'elenco delle gare con informazioni aggiuntive
$gare = $wpdb->get_results("
    SELECT g.*, p.nome as pista_nome 
    FROM $table_gare g 
    LEFT JOIN $table_piste p ON g.pista_id = p.id 
    ORDER BY g.data_gara DESC
");

// Statistiche
$total_gare = count($gare);
$gare_future = array_reduce($gare, function($count, $gara) {
    return $count + (strtotime($gara->data_gara) > time() ? 1 : 0);
}, 0);
$gare_passate = $total_gare - $gare_future;
$gare_approvate = array_reduce($gare, function($count, $gara) {
    return $count + ($gara->approvato ? 1 : 0);
}, 0);
?>

<div class="wrap rc-race-manager-admin">
    <!-- Header con statistiche -->
    <div class="header-container mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fas fa-trophy me-2"></i>Gestione Gare</h1>
            <div class="d-flex gap-3">
                <div class="stat-pill">
                    <i class="fas fa-calendar-check text-success"></i>
                    <span><?php echo $gare_future; ?> Future</span>
                </div>
                <div class="stat-pill">
                    <i class="fas fa-calendar-times text-secondary"></i>
                    <span><?php echo $gare_passate; ?> Passate</span>
                </div>
                <div class="stat-pill">
                    <i class="fas fa-check-circle text-primary"></i>
                    <span><?php echo $gare_approvate; ?> Approvate</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista Gare -->
    <div class="card dashboard-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Gara</th>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Pista</th>
                            <th>Stato</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gare as $gara): ?>
                        <tr class="align-middle fade-in">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="event-icon me-3">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                    <div>
                                        <strong><?php echo esc_html($gara->nome); ?></strong>
                                        <?php if (!empty($gara->descrizione)): ?>
                                            <div class="text-muted small">
                                                <?php echo esc_html($gara->descrizione); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong><?php echo date('d/m/Y', strtotime($gara->data_gara)); ?></strong>
                                    <small class="text-muted">
                                        <?php echo date('H:i', strtotime($gara->data_gara)); ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    <?php echo esc_html($tipi_gara[$gara->tipo_gara] ?? $gara->tipo_gara); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo esc_html($gara->pista_nome); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (strtotime($gara->data_gara) > time()): ?>
                                    <span class="badge bg-success">Programmata</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Completata</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" class="d-inline-block">
                                    <input type="hidden" name="gara_id" value="<?php echo $gara->id; ?>">
                                    <?php if (!$gara->approvato): ?>
                                        <button type="submit" name="action" value="approva" 
                                                class="btn btn-sm btn-success" title="Approva gara">
                                            <i class="fas fa-check me-1"></i> Approva
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="rifiuta" 
                                                class="btn btn-sm btn-warning" title="Rimuovi approvazione">
                                            <i class="fas fa-ban me-1"></i> Rifiuta
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .rc-race-manager-admin {
            padding: 20px;
        }

        .header-container {
            background: #fff;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .stat-pill {
            background: #f8f9fa;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            color: #495057;
        }

        .event-icon {
            width: 40px;
            height: 40px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #495057;
        }

        .dashboard-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Hover effect sulle righe della tabella */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
            transition: background-color 0.2s ease-in-out;
        }

        /* Stile per i bottoni di azione */
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            transition: all 0.2s ease-in-out;
        }

        .btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        /* Badge personalizzati */
        .badge {
            font-weight: 500;
            padding: 0.5em 0.75em;
        }
    </style>
</div>