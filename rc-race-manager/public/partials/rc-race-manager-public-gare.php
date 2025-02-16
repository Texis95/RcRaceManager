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
    ORDER BY g.data ASC
");

// Query per ottenere l'elenco delle piste approvate per il form
$piste = $wpdb->get_results("
    SELECT id, nome 
    FROM $table_piste 
    WHERE approvato = 1 
    ORDER BY nome ASC
");
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Calendario Gare</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuovaGara">
            <i class="fas fa-plus"></i> Aggiungi Gara
        </button>
    </div>

    <?php if (empty($gare)): ?>
    <div class="alert alert-info">
        Non ci sono gare programmate al momento.
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Gara</th>
                    <th>Pista</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($gare as $gara): ?>
                <tr>
                    <td><?php echo esc_html(date('d/m/Y', strtotime($gara->data))); ?></td>
                    <td>
                        <strong><?php echo esc_html($gara->titolo); ?></strong>
                        <?php if (!empty($gara->descrizione)): ?>
                            <p class="small text-muted mb-0"><?php echo esc_html($gara->descrizione); ?></p>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($gara->pista_nome); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Nuova Gara -->
<div class="modal fade" id="modalNuovaGara" tabindex="-1" aria-labelledby="modalNuovaGaraLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuovaGaraLabel">Nuova Gara</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuovaGara" class="needs-validation" novalidate>
                <div id="formMessages"></div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="titolo" class="form-label">Titolo Gara *</label>
                        <input type="text" class="form-control" id="titolo" name="titolo" required>
                        <div class="invalid-feedback">
                            Inserisci il titolo della gara.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="data" class="form-label">Data *</label>
                        <input type="date" class="form-control" id="data" name="data" required>
                        <div class="invalid-feedback">
                            Seleziona la data della gara.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="pista_id" class="form-label">Pista *</label>
                        <select class="form-select" id="pista_id" name="pista_id" required>
                            <option value="">Seleziona una pista</option>
                            <?php foreach ($piste as $pista): ?>
                                <option value="<?php echo esc_attr($pista->id); ?>">
                                    <?php echo esc_html($pista->nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            Seleziona una pista.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descrizione" class="form-label">Descrizione</label>
                        <textarea class="form-control" id="descrizione" name="descrizione" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Salva</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formNuovaGara');

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