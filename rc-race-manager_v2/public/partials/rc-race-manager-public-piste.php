<?php
// Protezione accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_piste = $wpdb->prefix . 'rc_piste';

// Query per ottenere l'elenco delle piste approvate
$piste = $wpdb->get_results("
    SELECT * FROM $table_piste 
    WHERE approvato = 1 
    ORDER BY nome ASC
");

// Verifica se l'utente è autenticato
$is_logged_in = isset($this) && method_exists($this->auth, 'is_user_logged_in') ? $this->auth->is_user_logged_in() : false;
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Piste</h2>
        <div class="d-flex gap-3">
            <!-- Toggle Vista -->
            <div class="btn-group" role="group" aria-label="Cambia vista">
                <button type="button" class="btn btn-outline-primary active" data-view="list" data-section="piste">
                    <i class="fas fa-list"></i>
                </button>
                <button type="button" class="btn btn-outline-primary" data-view="grid" data-section="piste">
                    <i class="fas fa-th-large"></i>
                </button>
            </div>
            <?php if ($is_logged_in): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuovaPista">
                    <i class="fas fa-plus me-2"></i> Aggiungi Pista
                </button>
            <?php else: ?>
                <a href="#" class="btn btn-outline-secondary" onclick="alert('Devi effettuare il login per aggiungere una pista');">
                    <i class="fas fa-lock me-2"></i> Accedi per Aggiungere
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Vista Lista -->
    <div id="listView-piste" class="view-container">
        <?php if (empty($piste)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Nessuna pista approvata disponibile al momento.
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">Nome</th>
                                <th class="border-0">Località</th>
                                <th class="border-0">Descrizione</th>
                                <th class="border-0">Data Aggiunta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($piste as $pista): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="track-icon me-3">
                                            <i class="fas fa-flag-checkered"></i>
                                        </div>
                                        <strong><?php echo esc_html($pista->nome); ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                    <?php echo esc_html($pista->localita); ?>
                                </td>
                                <td><?php echo esc_html($pista->descrizione); ?></td>
                                <td>
                                    <i class="fas fa-calendar text-muted me-2"></i>
                                    <?php echo date('d/m/Y', strtotime($pista->data_creazione)); ?>
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
    <div id="gridView-piste" class="view-container d-none">
        <?php if (empty($piste)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Nessuna pista approvata disponibile al momento.
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($piste as $pista): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm hover-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="track-icon-large me-3">
                                    <i class="fas fa-flag-checkered"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-1"><?php echo esc_html($pista->nome); ?></h5>
                                    <div class="text-muted small">
                                        <i class="fas fa-map-marker-alt me-2"></i><?php echo esc_html($pista->localita); ?>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($pista->descrizione)): ?>
                                <p class="card-text"><?php echo esc_html($pista->descrizione); ?></p>
                            <?php endif; ?>
                            <div class="text-muted small mt-3">
                                <i class="fas fa-calendar me-2"></i>
                                Aggiunta il <?php echo date('d/m/Y', strtotime($pista->data_creazione)); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Nuova Pista (solo per utenti autenticati) -->
<?php if ($is_logged_in): ?>
    <?php include('rc-race-manager-public-form-pista.php'); ?>
<?php endif; ?>

<style>
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
    .track-icon-large {
        width: 48px;
        height: 48px;
        background-color: #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #495057;
        font-size: 1.2rem;
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