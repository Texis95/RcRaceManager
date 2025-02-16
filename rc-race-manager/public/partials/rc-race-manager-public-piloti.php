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
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Piloti</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuovoPilota">
            <i class="fas fa-plus"></i> Registrati come Pilota
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Pilota</th>
                    <th>Categoria</th>
                    <th>Trasponder</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($piloti as $pilota): ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($pilota->cognome . ' ' . $pilota->nome); ?></strong>
                    </td>
                    <td><?php echo esc_html($pilota->categoria_nome); ?></td>
                    <td><?php echo esc_html($pilota->trasponder); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nuovo Pilota -->
<div class="modal fade" id="modalNuovoPilota" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrazione Pilota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuovoPilota" class="needs-validation" novalidate>
                <div id="formMessages"></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label for="nome" class="form-label">Nome *</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                        <div class="invalid-feedback">Inserisci il nome</div>
                    </div>

                    <div class="mb-2">
                        <label for="cognome" class="form-label">Cognome *</label>
                        <input type="text" class="form-control" id="cognome" name="cognome" required>
                        <div class="invalid-feedback">Inserisci il cognome</div>
                    </div>

                    <div class="mb-2">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="invalid-feedback">Inserisci un indirizzo email valido</div>
                    </div>

                    <div class="mb-2">
                        <label for="telefono" class="form-label">Telefono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono">
                    </div>

                    <div class="mb-2">
                        <label for="categoria_id" class="form-label">Categoria *</label>
                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                            <option value="">Seleziona una categoria</option>
                            <?php foreach ($categorie as $categoria): ?>
                                <option value="<?php echo $categoria->id; ?>">
                                    <?php echo esc_html($categoria->nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Seleziona una categoria</div>
                    </div>

                    <div class="mb-2">
                        <label for="trasponder" class="form-label">Trasponder *</label>
                        <input type="text" class="form-control" id="trasponder" name="trasponder" required>
                        <div class="invalid-feedback">Inserisci il numero del trasponder</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Registrati</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formNuovoPilota');
    const messageContainer = form.querySelector('#formMessages');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted'); // Debug log

        // Rimuovi eventuali messaggi precedenti
        messageContainer.innerHTML = '';

        if (!this.checkValidity()) {
            e.stopPropagation();
            this.classList.add('was-validated');
            return;
        }

        const formData = new FormData(this);
        formData.append('action', 'rc_race_manager_add_pilota');
        formData.append('nonce', rcRaceManager.nonce);

        // Debug log dei dati del form
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

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
            console.log('Response status:', response.status); // Debug log
            return response.json();
        })
        .then(data => {
            console.log('Server response:', data); // Debug log

            const alert = document.createElement('div');
            alert.className = `alert ${data.success ? 'alert-success' : 'alert-danger'} alert-dismissible fade show`;
            alert.innerHTML = `
                ${data.data && data.data.message ? data.data.message : data.message}
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
            console.error('Error:', error); // Debug log
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
                ${rcRaceManager.strings.network_error}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            messageContainer.appendChild(alert);
        })
        .finally(() => {
            // Riabilita sempre il pulsante
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        });
    });

    // Resetta il form quando il modal viene chiuso
    document.getElementById('modalNuovoPilota').addEventListener('hidden.bs.modal', function () {
        form.reset();
        form.classList.remove('was-validated');
        messageContainer.innerHTML = '';
    });
});
</script>