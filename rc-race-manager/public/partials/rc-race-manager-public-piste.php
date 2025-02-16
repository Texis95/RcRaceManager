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

// Debug query
error_log('RC Race Manager: Query piste - ' . $wpdb->last_query);
error_log('RC Race Manager: Numero piste trovate - ' . count($piste));
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Piste</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuovaPista">
            <i class="fas fa-plus"></i> Aggiungi Pista
        </button>
    </div>

    <!-- Toast per notifiche -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fas fa-info-circle me-2"></i>
                <strong class="me-auto">Notifica</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body"></div>
        </div>
    </div>

    <div class="row g-4">
        <?php if (empty($piste)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Nessuna pista approvata disponibile al momento.
                    <?php
                    // Debug - Mostra il numero totale di piste, incluse quelle non approvate
                    $total_tracks = $wpdb->get_var("SELECT COUNT(*) FROM $table_piste");
                    if ($total_tracks > 0) {
                        echo ' (' . $total_tracks . ' piste in attesa di approvazione)';
                    }
                    ?>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($piste as $pista): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php echo esc_html($pista->nome); ?>
                            <?php if ($pista->approvato): ?>
                                <span class="badge bg-success ms-2" title="Pista approvata">
                                    <i class="fas fa-check"></i>
                                </span>
                            <?php endif; ?>
                        </h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            <i class="fas fa-map-marker-alt"></i> <?php echo esc_html($pista->localita); ?>
                        </h6>
                        <p class="card-text"><?php echo esc_html($pista->descrizione); ?></p>
                        <div class="text-muted small">
                            <i class="fas fa-calendar"></i> Aggiunta il <?php echo date('d/m/Y', strtotime($pista->data_creazione)); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Nuova Pista -->
<div class="modal fade" id="modalNuovaPista" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuova Pista</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuovaPista" class="needs-validation" novalidate>
                <div id="formMessages"></div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Pista <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" 
                               required minlength="3" maxlength="100">
                        <div class="invalid-feedback">
                            Il nome della pista deve essere compreso tra 3 e 100 caratteri.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="localita" class="form-label">Località <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="localita" name="localita" 
                               required minlength="2" maxlength="100">
                        <div class="invalid-feedback">
                            Inserisci una località valida (minimo 2 caratteri).
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descrizione" class="form-label">Descrizione</label>
                        <textarea class="form-control" id="descrizione" name="descrizione" 
                                  rows="3" maxlength="500"></textarea>
                        <div class="form-text">
                            Massimo 500 caratteri
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inizializza i toast di Bootstrap
    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    const toastList = toastElList.map(function(toastEl) {
        return new bootstrap.Toast(toastEl, { delay: 3000 });
    });

    // Funzione per mostrare notifiche
    function showNotification(message, type = 'success') {
        const toast = document.getElementById('notificationToast');
        const toastBootstrap = bootstrap.Toast.getInstance(toast);

        toast.querySelector('.toast-body').textContent = message;
        toast.classList.remove('bg-success', 'bg-danger');
        toast.classList.add(type === 'success' ? 'bg-success' : 'bg-danger');
        toast.classList.add('text-white');

        toastBootstrap.show();
    }

    document.getElementById('formNuovaPista').addEventListener('submit', async function(e) {
        e.preventDefault();
        console.log('RC Race Manager: Form pista submitted');

        const messageContainer = this.querySelector('#formMessages');
        messageContainer.innerHTML = '';

        if (!this.checkValidity()) {
            e.stopPropagation();
            this.classList.add('was-validated');
            console.log('RC Race Manager: Form validation failed');
            return;
        }

        const formData = new FormData(this);
        formData.append('action', 'rc_race_manager_add_pista');
        formData.append('nonce', rcRaceManager.nonce);

        // Log dei dati del form
        const formDataObj = {};
        for (let pair of formData.entries()) {
            formDataObj[pair[0]] = pair[1];
            console.log('RC Race Manager: Form data -', pair[0] + ': ' + pair[1]);
        }

        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvataggio...';

        try {
            console.log('RC Race Manager: Sending AJAX request to:', rcRaceManager.ajaxurl);
            const response = await fetch(rcRaceManager.ajaxurl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Errore di rete');
            }

            const data = await response.json();
            console.log('RC Race Manager: Server response:', data);

            if (data.success) {
                showNotification(data.data.message, 'success');
                this.reset();
                this.classList.remove('was-validated');
                bootstrap.Modal.getInstance(document.getElementById('modalNuovaPista')).hide();

                // Ricarica la pagina dopo 1.5 secondi
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification(data.data.message, 'error');
                if (data.data.debug) {
                    console.error('RC Race Manager: Debug info:', data.data.debug);
                }
            }
        } catch (error) {
            console.error('RC Race Manager: Error:', error);
            showNotification(rcRaceManager.strings.network_error, 'error');
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    });

    // Reset form quando il modal viene chiuso
    document.getElementById('modalNuovaPista').addEventListener('hidden.bs.modal', function() {
        const form = this.querySelector('form');
        form.reset();
        form.classList.remove('was-validated');
        form.querySelector('#formMessages').innerHTML = '';
    });
});
</script>