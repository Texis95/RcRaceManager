<?php
// Protezione accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_categorie = $wpdb->prefix . 'rc_categorie';

// Debug log per verificare se le categorie vengono caricate
error_log("RC Race Manager: Caricamento categorie dalla tabella $table_categorie");

// Ottieni le categorie per il dropdown
$categorie = $wpdb->get_results("SELECT * FROM $table_categorie ORDER BY nome ASC");

// Debug log per il numero di categorie trovate
error_log("RC Race Manager: Trovate " . count($categorie) . " categorie");
?>

<div class="container mt-4">
    <h2 class="mb-4">Registrazione Pilota</h2>

    <form id="formRegistrazionePilota" method="post" class="needs-validation" novalidate>
        <div class="row g-2">
            <div class="col-md-6 mb-2">
                <label for="nome" class="form-label">Nome *</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
                <div class="invalid-feedback">Inserisci il tuo nome.</div>
            </div>

            <div class="col-md-6 mb-2">
                <label for="cognome" class="form-label">Cognome *</label>
                <input type="text" class="form-control" id="cognome" name="cognome" required>
                <div class="invalid-feedback">Inserisci il tuo cognome.</div>
            </div>

            <div class="col-md-6 mb-2">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div class="invalid-feedback">Inserisci un indirizzo email valido.</div>
            </div>

            <div class="col-md-6 mb-2">
                <label for="telefono" class="form-label">Telefono</label>
                <input type="tel" class="form-control" id="telefono" name="telefono">
            </div>

            <div class="col-md-6 mb-2">
                <label for="trasponder" class="form-label">Trasponder *</label>
                <input type="text" class="form-control" id="trasponder" name="trasponder" required>
                <div class="invalid-feedback">Inserisci il numero del trasponder.</div>
            </div>

            <div class="col-md-6 mb-2">
                <label for="categoria_id" class="form-label">Categoria *</label>
                <select class="form-select" id="categoria_id" name="categoria_id" required>
                    <option value="">Seleziona una categoria</option>
                    <?php foreach ($categorie as $categoria): ?>
                        <option value="<?php echo esc_attr($categoria->id); ?>">
                            <?php echo esc_html($categoria->nome); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Seleziona una categoria.</div>
            </div>
        </div>

        <div class="alert" id="formMessages" style="display: none;"></div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Registrati</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formRegistrazionePilota');
    const messageContainer = document.getElementById('formMessages');

    // Debug log per verificare il caricamento del form
    console.log('Form inizializzato:', {
        form: form !== null,
        messageContainer: messageContainer !== null,
        rcRaceManager: typeof rcRaceManager !== 'undefined' ? 'Disponibile' : 'Non disponibile'
    });

    if (!rcRaceManager || !rcRaceManager.ajaxurl || !rcRaceManager.nonce) {
        console.error('Errore: rcRaceManager non configurato correttamente', rcRaceManager);
        return;
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted'); // Debug log

        // Rimuovi eventuali messaggi precedenti
        messageContainer.style.display = 'none';
        messageContainer.innerHTML = '';

        if (!this.checkValidity()) {
            e.stopPropagation();
            this.classList.add('was-validated');
            console.log('Form validation failed'); // Debug log
            return;
        }

        console.log('Form validation passed'); // Debug log

        const formData = new FormData(this);
        formData.append('action', 'rc_race_manager_add_pilota');
        formData.append('nonce', rcRaceManager.nonce);

        // Debug log dei dati del form
        const formDataObj = {};
        for (let pair of formData.entries()) {
            formDataObj[pair[0]] = pair[1];
            console.log(pair[0] + ': ' + pair[1]);
        }
        console.log('Form data:', formDataObj);

        // Disabilita il pulsante durante l'invio
        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvataggio...';

        console.log('Invio richiesta AJAX a:', rcRaceManager.ajaxurl);

        fetch(rcRaceManager.ajaxurl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Response status:', response.status); // Debug log
            return response.json().catch(error => {
                console.error('Errore parsing JSON:', error);
                throw new Error('Errore nella risposta del server');
            });
        })
        .then(data => {
            console.log('Server response:', data); // Debug log

            messageContainer.className = `alert ${data.success ? 'alert-success' : 'alert-danger'} alert-dismissible fade show`;
            messageContainer.innerHTML = `
                ${data.data ? data.data.message : data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            messageContainer.style.display = 'block';

            if (data.success) {
                // Reset form
                this.reset();
                this.classList.remove('was-validated');

                // Ricarica la pagina dopo 2 secondi
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else if (data.debug) {
                console.error('Debug info:', data.debug);
            }
        })
        .catch(error => {
            console.error('Error:', error); // Debug log
            messageContainer.className = 'alert alert-danger alert-dismissible fade show';
            messageContainer.innerHTML = `
                Si è verificato un errore durante il salvataggio. Riprova più tardi. (${error.message})
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            messageContainer.style.display = 'block';
        })
        .finally(() => {
            // Riabilita sempre il pulsante
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        });
    });
});
</script>