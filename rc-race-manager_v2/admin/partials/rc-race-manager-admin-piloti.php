<?php
// Protezione accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_piloti = $wpdb->prefix . 'rc_piloti';
$table_categorie = $wpdb->prefix . 'rc_categorie';

// Gestione approvazione/rifiuto
if (isset($_POST['action']) && isset($_POST['pilota_id'])) {
    $pilota_id = intval($_POST['pilota_id']);
    $action = sanitize_text_field($_POST['action']);

    if ($action === 'approva') {
        $wpdb->update(
            $table_piloti,
            array('approvato' => 1),
            array('id' => $pilota_id),
            array('%d'),
            array('%d')
        );
    } elseif ($action === 'rifiuta') {
        $wpdb->update(
            $table_piloti,
            array('approvato' => 0),
            array('id' => $pilota_id),
            array('%d'),
            array('%d')
        );
    }
}

// Query per ottenere l'elenco dei piloti
$piloti = $wpdb->get_results("
    SELECT p.*, c.nome as categoria_nome 
    FROM $table_piloti p 
    LEFT JOIN $table_categorie c ON p.categoria_id = c.id 
    ORDER BY p.data_registrazione DESC
");

// Statistiche
$total_piloti = count($piloti);
$piloti_approvati = array_reduce($piloti, function($count, $pilota) {
    return $count + ($pilota->approvato ? 1 : 0);
}, 0);
$piloti_in_attesa = $total_piloti - $piloti_approvati;
?>

<div class="wrap rc-race-manager-admin">
    <!-- Header con statistiche -->
    <div class="header-container mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fas fa-users me-2"></i>Gestione Piloti</h1>
            <div class="d-flex gap-3">
                <div class="stat-pill">
                    <i class="fas fa-user-check text-success"></i>
                    <span><?php echo $piloti_approvati; ?> Approvati</span>
                </div>
                <div class="stat-pill">
                    <i class="fas fa-user-clock text-warning"></i>
                    <span><?php echo $piloti_in_attesa; ?> In attesa</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista Piloti -->
    <div class="card dashboard-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Pilota</th>
                            <th>Contatti</th>
                            <th>Categoria</th>
                            <th>Trasponder</th>
                            <th>Stato</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($piloti as $pilota): ?>
                        <tr class="align-middle fade-in">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3">
                                        <?php echo strtoupper(substr($pilota->nome, 0, 1) . substr($pilota->cognome, 0, 1)); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo esc_html($pilota->nome . ' ' . $pilota->cognome); ?></strong>
                                        <div class="text-muted small">
                                            Registrato il <?php echo date('d/m/Y', strtotime($pilota->data_registrazione)); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <?php if ($pilota->email): ?>
                                        <a href="mailto:<?php echo esc_attr($pilota->email); ?>" class="text-decoration-none">
                                            <i class="fas fa-envelope text-muted me-2"></i><?php echo esc_html($pilota->email); ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($pilota->telefono): ?>
                                        <a href="tel:<?php echo esc_attr($pilota->telefono); ?>" class="text-decoration-none">
                                            <i class="fas fa-phone text-muted me-2"></i><?php echo esc_html($pilota->telefono); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    <?php echo esc_html($pilota->categoria_nome); ?>
                                </span>
                            </td>
                            <td>
                                <code><?php echo esc_html($pilota->trasponder); ?></code>
                            </td>
                            <td>
                                <?php if ($pilota->approvato): ?>
                                    <span class="badge bg-success">Approvato</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">In attesa</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" class="d-inline-block">
                                    <input type="hidden" name="pilota_id" value="<?php echo $pilota->id; ?>">
                                    <?php if (!$pilota->approvato): ?>
                                        <button type="submit" name="action" value="approva" 
                                                class="btn btn-sm btn-success" title="Approva pilota">
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

        .avatar-circle {
            width: 40px;
            height: 40px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #495057;
            font-size: 0.875rem;
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