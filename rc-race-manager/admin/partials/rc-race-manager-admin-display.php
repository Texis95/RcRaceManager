<?php
// Protezione accesso diretto
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap rc-race-manager-admin">
    <div class="header-container mb-4">
        <h1><i class="fas fa-flag-checkered me-2"></i>RC Race Manager</h1>
        <p class="text-muted">Gestisci le tue gare RC, piloti e piste da questa dashboard.</p>
    </div>

    <div class="row g-4">
        <!-- Piloti Stats -->
        <div class="col-md-4">
            <div class="card stat-card bg-gradient-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon me-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h6 class="stat-label mb-1">Piloti Registrati</h6>
                            <?php
                            global $wpdb;
                            $piloti_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rc_piloti");
                            ?>
                            <h2 class="stat-value mb-0"><?php echo $piloti_count; ?></h2>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <a href="?page=rc-race-manager-piloti" class="text-white text-decoration-none">
                            Gestisci Piloti <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gare Stats -->
        <div class="col-md-4">
            <div class="card stat-card bg-gradient-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon me-3">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div>
                            <h6 class="stat-label mb-1">Gare Totali</h6>
                            <?php
                            $gare_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rc_gare");
                            ?>
                            <h2 class="stat-value mb-0"><?php echo $gare_count; ?></h2>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <a href="?page=rc-race-manager-gare" class="text-white text-decoration-none">
                            Gestisci Gare <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Piste Stats -->
        <div class="col-md-4">
            <div class="card stat-card bg-gradient-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon me-3">
                            <i class="fas fa-road"></i>
                        </div>
                        <div>
                            <h6 class="stat-label mb-1">Piste Registrate</h6>
                            <?php
                            $piste_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rc_piste");
                            ?>
                            <h2 class="stat-value mb-0"><?php echo $piste_count; ?></h2>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <a href="?page=rc-race-manager-piste" class="text-white text-decoration-none">
                            Gestisci Piste <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="card dashboard-card mt-4">
        <div class="card-header bg-transparent">
            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Attività Recenti</h5>
        </div>
        <div class="card-body">
            <?php
            // Ultime 5 gare
            $recent_gare = $wpdb->get_results("
                SELECT g.*, p.nome as pista_nome 
                FROM {$wpdb->prefix}rc_gare g
                LEFT JOIN {$wpdb->prefix}rc_piste p ON g.pista_id = p.id
                ORDER BY g.data_creazione DESC 
                LIMIT 5
            ");
            ?>

            <?php if (!empty($recent_gare)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Evento</th>
                            <th>Data</th>
                            <th>Pista</th>
                            <th>Stato</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_gare as $gara): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="event-icon me-3">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?php echo esc_html($gara->nome); ?></h6>
                                        <small class="text-muted"><?php echo esc_html($gara->tipo_gara); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($gara->data_gara)); ?></td>
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
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center text-muted py-4">
                <i class="fas fa-info-circle mb-2"></i>
                <p class="mb-0">Nessuna gara recente da mostrare.</p>
            </div>
            <?php endif; ?>
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

        .stat-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .bg-gradient-primary {
            background: linear-gradient(45deg, #0d6efd, #0a58ca);
        }

        .bg-gradient-success {
            background: linear-gradient(45deg, #198754, #157347);
        }

        .bg-gradient-info {
            background: linear-gradient(45deg, #0dcaf0, #0aa2c0);
        }

        .stat-card .card-body {
            padding: 1.5rem;
            color: white;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-label {
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 600;
        }

        .stat-footer {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dashboard-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .dashboard-card .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            padding: 1rem 1.5rem;
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

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }

        .table td {
            vertical-align: middle;
        }

        /* Animazioni */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stat-card, .dashboard-card {
            animation: fadeIn 0.3s ease-out;
        }
    </style>
</div>