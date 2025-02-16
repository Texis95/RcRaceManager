<?php
// Protezione accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_gare = $wpdb->prefix . 'rc_gare';
$table_piste = $wpdb->prefix . 'rc_piste';

// Query per ottenere l'elenco delle gare approvate
$gare = $wpdb->get_results("
    SELECT g.*, p.nome as pista_nome 
    FROM $table_gare g 
    LEFT JOIN $table_piste p ON g.pista_id = p.id 
    WHERE g.approvato = 1 
    ORDER BY g.data_gara ASC
");

// Query per ottenere l'elenco delle piste approvate per il form
$piste = $wpdb->get_results("
    SELECT id, nome 
    FROM $table_piste 
    WHERE approvato = 1 
    ORDER BY nome ASC
");

// Definizione tipi di gara disponibili
$tipi_gara = array(
    'campionato' => 'Campionato',
    'amichevole' => 'Amichevole',
    'torneo' => 'Torneo'
);

// Verifica se l'utente è autenticato (assumendo che esista una funzione o metodo $this->auth->is_user_logged_in())
$is_logged_in = isset($this) && method_exists($this->auth, 'is_user_logged_in') ? $this->auth->is_user_logged_in() : false;
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Calendario Gare</h2>
        <div class="d-flex gap-3">
            <!-- Toggle Vista -->
            <div class="btn-group" role="group" aria-label="Cambia vista">
                <button type="button" class="btn btn-outline-primary active" data-view="list" id="viewListGare">
                    <i class="fas fa-list"></i>
                </button>
                <button type="button" class="btn btn-outline-primary" data-view="grid" id="viewGridGare">
                    <i class="fas fa-th-large"></i>
                </button>
            </div>
            <?php if ($is_logged_in): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuovaGara">
                    <i class="fas fa-plus me-2"></i> Aggiungi Gara
                </button>
            <?php else: ?>
                <a href="#" class="btn btn-outline-secondary" onclick="alert('Devi effettuare il login per aggiungere una gara');">
                    <i class="fas fa-lock me-2"></i> Accedi per Aggiungere
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($gare)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i> Non ci sono gare programmate al momento.
    </div>
    <?php else: ?>
    <!-- Vista Lista -->
    <div id="listViewGare" class="view-container fade-in">
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">Data</th>
                            <th class="border-0">Gara</th>
                            <th class="border-0">Tipo</th>
                            <th class="border-0">Pista</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gare as $gara): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="event-icon me-3">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                    <?php echo esc_html(date('d/m/Y', strtotime($gara->data_gara))); ?>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo esc_html($gara->nome); ?></strong>
                                <?php if (!empty($gara->descrizione)): ?>
                                    <p class="small text-muted mb-0"><?php echo esc_html($gara->descrizione); ?></p>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    <?php echo esc_html($tipi_gara[$gara->tipo_gara] ?? $gara->tipo_gara); ?>
                                </span>
                            </td>
                            <td>
                                <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                <?php echo esc_html($gara->pista_nome); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Vista Grid -->
    <div id="gridViewGare" class="view-container d-none fade-in">
        <div class="row g-4">
            <?php foreach ($gare as $gara): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm hover-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="event-icon-large me-3">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1"><?php echo esc_html($gara->nome); ?></h5>
                                <div class="text-muted small">
                                    <i class="fas fa-calendar me-2"></i>
                                    <?php echo date('d/m/Y', strtotime($gara->data_gara)); ?>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($gara->descrizione)): ?>
                            <p class="card-text"><?php echo esc_html($gara->descrizione); ?></p>
                        <?php endif; ?>
                        <div class="mt-3">
                            <div class="mb-2">
                                <span class="badge bg-primary">
                                    <?php echo esc_html($tipi_gara[$gara->tipo_gara] ?? $gara->tipo_gara); ?>
                                </span>
                            </div>
                            <div class="text-muted small">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo esc_html($gara->pista_nome); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Nuova Gara (solo per utenti autenticati) -->
<?php if ($is_logged_in): ?>
    <?php include('rc-race-manager-public-form-gara.php'); ?>
<?php endif; ?>

<style>
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
    .event-icon-large {
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
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }
    .hover-card {
        transition: transform 0.2s ease-in-out;
    }
    .hover-card:hover {
        transform: translateY(-5px);
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .view-container {
        min-height: 200px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const listViewGare = document.getElementById('listViewGare');
    const gridViewGare = document.getElementById('gridViewGare');
    const viewListBtnGare = document.getElementById('viewListGare');
    const viewGridBtnGare = document.getElementById('viewGridGare');

    // Funzione per cambiare vista
    function toggleView(view) {
        if (view === 'grid') {
            listViewGare.classList.add('d-none');
            gridViewGare.classList.remove('d-none');
            viewListBtnGare.classList.remove('active');
            viewGridBtnGare.classList.add('active');
            localStorage.setItem('gareViewPreference', 'grid');
        } else {
            gridViewGare.classList.add('d-none');
            listViewGare.classList.remove('d-none');
            viewGridBtnGare.classList.remove('active');
            viewListBtnGare.classList.add('active');
            localStorage.setItem('gareViewPreference', 'list');
        }
    }

    // Event listeners per i pulsanti
    viewListBtnGare.addEventListener('click', () => toggleView('list'));
    viewGridBtnGare.addEventListener('click', () => toggleView('grid'));

    // Carica preferenza salvata
    const savedView = localStorage.getItem('gareViewPreference');
    if (savedView) {
        toggleView(savedView);
    }

    // Il resto del codice JavaScript per la gestione del form rimane invariato
    const form = document.getElementById('formNuovaGara');

    // Imposta la data minima al giorno corrente
    const dataGaraInput = document.getElementById('data_gara');
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    dataGaraInput.min = now.toISOString().slice(0,16);

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Rimuovi eventuali messaggi precedenti
        const messageContainer = this.querySelector('#formMessages');
        messageContainer.innerHTML = '';

        if (!this.checkValidity()) {
            e.stopPropagation();
            this.classList.add('was-validated');
            return;
        }

        const formData = new FormData(this);
        formData.append('action', 'rc_race_manager_add_gara');
        formData.append('nonce', rcRaceManager.nonce);

        // Disabilita il pulsante durante l'invio
        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvataggio...';

        fetch(rcRaceManager.ajaxurl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Errore di rete');
            }
            return response.json();
        })
        .then(data => {
            const alert = document.createElement('div');
            alert.className = `alert ${data.success ? 'alert-success' : 'alert-danger'} alert-dismissible fade show`;
            alert.innerHTML = `
                ${data.data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            messageContainer.appendChild(alert);

            if (data.success) {
                // Reset form
                this.reset();
                this.classList.remove('was-validated');

                // Ricarica la pagina dopo 2 secondi
                setTimeout(() => {
                    location.reload();
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
                ${rcRaceManager.strings.network_error}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            messageContainer.appendChild(alert);
        })
        .finally(() => {
            // Riabilita il pulsante
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        });
    });

    // Resetta il form quando il modal viene chiuso
    const modal = document.getElementById('modalNuovaGara');
    modal.addEventListener('hidden.bs.modal', function () {
        form.reset();
        form.classList.remove('was-validated');
        form.querySelector('#formMessages').innerHTML = '';
    });
});
</script>