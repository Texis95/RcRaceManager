<?php
// Protezione accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_piste = $wpdb->prefix . 'rc_piste';

// Gestione approvazione/rifiuto
if (isset($_POST['action']) && isset($_POST['pista_id'])) {
    $pista_id = intval($_POST['pista_id']);
    $action = sanitize_text_field($_POST['action']);

    if ($action === 'approva') {
        $wpdb->update(
            $table_piste,
            array('approvato' => 1),
            array('id' => $pista_id),
            array('%d'),
            array('%d')
        );
    } elseif ($action === 'rifiuta') {
        $wpdb->update(
            $table_piste,
            array('approvato' => 0),
            array('id' => $pista_id),
            array('%d'),
            array('%d')
        );
    }
}

// Query per ottenere l'elenco delle piste
$piste = $wpdb->get_results("
    SELECT * FROM $table_piste 
    ORDER BY nome ASC
");

// Statistiche
$total_piste = count($piste);
$piste_approvate = array_reduce($piste, function($count, $pista) {
    return $count + ($pista->approvato ? 1 : 0);
}, 0);
$piste_in_attesa = $total_piste - $piste_approvate;

// Calcola il numero di gare per pista
$gare_per_pista = $wpdb->get_results("
    SELECT pista_id, COUNT(*) as num_gare 
    FROM {$wpdb->prefix}rc_gare 
    GROUP BY pista_id
", OBJECT_K);
?>

<div class="wrap rc-race-manager-admin">
    <!-- Header con statistiche -->
    <div class="header-container mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fas fa-road me-2"></i>Gestione Piste</h1>
            <div class="d-flex gap-3">
                <div class="stat-pill">
                    <i class="fas fa-check-circle text-success"></i>
                    <span><?php echo $piste_approvate; ?> Approvate</span>
                </div>
                <div class="stat-pill">
                    <i class="fas fa-clock text-warning"></i>
                    <span><?php echo $piste_in_attesa; ?> In attesa</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista Piste -->
    <div class="card dashboard-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Pista</th>
                            <th>Località</th>
                            <th>Gare Ospitate</th>
                            <th>Stato</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($piste as $pista): ?>
                        <tr class="align-middle fade-in">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="track-icon me-3">
                                        <i class="fas fa-flag-checkered"></i>
                                    </div>
                                    <div>
                                        <strong><?php echo esc_html($pista->nome); ?></strong>
                                        <?php if (!empty($pista->descrizione)): ?>
                                            <div class="text-muted small">
                                                <?php echo esc_html($pista->descrizione); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                <?php echo esc_html($pista->localita); ?>
                            </td>
                            <td>
                                <?php
                                $num_gare = isset($gare_per_pista[$pista->id]) ? $gare_per_pista[$pista->id]->num_gare : 0;
                                if ($num_gare > 0): ?>
                                    <span class="badge bg-primary">
                                        <?php echo $num_gare; ?> gare
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark">
                                        Nessuna gara
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($pista->approvato): ?>
                                    <span class="badge bg-success">Approvata</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">In attesa</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" class="d-inline-block">
                                    <input type="hidden" name="pista_id" value="<?php echo $pista->id; ?>">
                                    <?php if (!$pista->approvato): ?>
                                        <button type="submit" name="action" value="approva" 
                                                class="btn btn-sm btn-success" title="Approva pista">
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

        .track-icon {
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