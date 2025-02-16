<?php
// Protezione accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_piloti = $wpdb->prefix . 'rc_piloti';
$table_categorie = $wpdb->prefix . 'rc_categorie';

// Query per ottenere l'elenco dei piloti approvati
$piloti = $wpdb->get_results("
    SELECT p.*, c.nome as categoria_nome 
    FROM $table_piloti p 
    LEFT JOIN $table_categorie c ON p.categoria_id = c.id 
    WHERE p.approvato = 1 
    ORDER BY p.cognome, p.nome ASC
");

// Query per ottenere l'elenco delle categorie per il form
$categorie = $wpdb->get_results("
    SELECT id, nome 
    FROM $table_categorie 
    ORDER BY nome ASC
");

// Verifica se l'utente è autenticato
$is_logged_in = isset($this) && method_exists($this->auth, 'is_user_logged_in') ? $this->auth->is_user_logged_in() : false;
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Piloti</h2>
        <div class="d-flex gap-3">
            <!-- Toggle Vista -->
            <div class="btn-group" role="group" aria-label="Cambia vista">
                <button type="button" class="btn btn-outline-primary active" data-view="list" data-section="piloti">
                    <i class="fas fa-list"></i>
                </button>
                <button type="button" class="btn btn-outline-primary" data-view="grid" data-section="piloti">
                    <i class="fas fa-th-large"></i>
                </button>
            </div>
            <?php if ($is_logged_in): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuovoPilota">
                    <i class="fas fa-plus me-2"></i> Registrati come Pilota
                </button>
            <?php else: ?>
                <a href="#" class="btn btn-outline-secondary" onclick="alert('Devi effettuare il login per registrarti come pilota');">
                    <i class="fas fa-lock me-2"></i> Accedi per Registrarti
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Vista Lista -->
    <div id="listView-piloti" class="view-container">
        <?php if (empty($piloti)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Nessun pilota approvato disponibile al momento.
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">Pilota</th>
                                <th class="border-0">Categoria</th>
                                <th class="border-0">Trasponder</th>
                                <th class="border-0">Contatti</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($piloti as $pilota): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3">
                                            <?php echo strtoupper(substr($pilota->nome, 0, 1) . substr($pilota->cognome, 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong><?php echo esc_html($pilota->cognome . ' ' . $pilota->nome); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo esc_html($pilota->categoria_nome); ?></span>
                                </td>
                                <td>
                                    <code><?php echo esc_html($pilota->trasponder); ?></code>
                                </td>
                                <td>
                                    <?php if ($pilota->email && $is_logged_in): ?>
                                        <a href="mailto:<?php echo esc_attr($pilota->email); ?>" class="btn btn-sm btn-outline-secondary me-1">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($pilota->telefono && $is_logged_in): ?>
                                        <a href="tel:<?php echo esc_attr($pilota->telefono); ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-phone"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!$is_logged_in): ?>
                                        <span class="text-muted"><small><i class="fas fa-lock me-1"></i>Accedi per vedere i contatti</small></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Vista Grid -->
    <div id="gridView-piloti" class="view-container d-none">
        <?php if (empty($piloti)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Nessun pilota approvato disponibile al momento.
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($piloti as $pilota): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm hover-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-circle me-3">
                                    <?php echo strtoupper(substr($pilota->nome, 0, 1) . substr($pilota->cognome, 0, 1)); ?>
                                </div>
                                <div>
                                    <h5 class="card-title mb-1"><?php echo esc_html($pilota->cognome . ' ' . $pilota->nome); ?></h5>
                                    <span class="badge bg-primary"><?php echo esc_html($pilota->categoria_nome); ?></span>
                                </div>
                            </div>
                            <div class="card-text">
                                <div class="mb-2">
                                    <strong><i class="fas fa-broadcast-tower me-2"></i>Trasponder:</strong>
                                    <code class="ms-2"><?php echo esc_html($pilota->trasponder); ?></code>
                                </div>
                                <?php if ($is_logged_in): ?>
                                    <?php if ($pilota->email || $pilota->telefono): ?>
                                    <div class="d-flex gap-2">
                                        <?php if ($pilota->email): ?>
                                            <a href="mailto:<?php echo esc_attr($pilota->email); ?>" class="btn btn-sm btn-outline-secondary flex-fill">
                                                <i class="fas fa-envelope me-2"></i><?php echo esc_html($pilota->email); ?>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($pilota->telefono): ?>
                                            <a href="tel:<?php echo esc_attr($pilota->telefono); ?>" class="btn btn-sm btn-outline-secondary flex-fill">
                                                <i class="fas fa-phone me-2"></i><?php echo esc_html($pilota->telefono); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted">
                                        <i class="fas fa-lock me-2"></i>Accedi per vedere i contatti
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Nuovo Pilota (solo per utenti autenticati) -->
<?php if ($is_logged_in): ?>
    <?php include('rc-race-manager-public-form-pilota.php'); ?>
<?php endif; ?>

<style>
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
    .hover-card {
        transition: transform 0.2s ease-in-out;
    }
    .hover-card:hover {
        transform: translateY(-5px);
    }
    .view-container {
        min-height: 200px;
    }
</style>