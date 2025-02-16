(function($) {
    'use strict';

    $(document).ready(function() {
        console.log('RC Race Manager: jQuery ready');
    });

})(jQuery);

document.addEventListener('DOMContentLoaded', function() {
    console.log('RC Race Manager: Initializing public interface');

    // Verifica disponibilità rcRaceManager
    if (typeof rcRaceManager === 'undefined') {
        console.error('RC Race Manager: rcRaceManager non è disponibile');
        return;
    }

    //Verifica configurazione rcRaceManager
    console.log('RC Race Manager: Checking rcRaceManager configuration...', rcRaceManager);
    if (!rcRaceManager || !rcRaceManager.ajaxurl || !rcRaceManager.nonce) {
        console.error('RC Race Manager: rcRaceManager is improperly configured:', rcRaceManager);
        return;
    }
    console.log('RC Race Manager: rcRaceManager configuration appears valid.');

    // Gestione menu laterale
    const menuLinks = document.querySelectorAll('.list-group-item');
    const contentDiv = document.getElementById('rc-race-content');
    const loadingSpinner = document.querySelector('.loading-spinner');

    if (!contentDiv) {
        console.error('RC Race Manager: Content container not found');
        return;
    }

    console.log('RC Race Manager: DOM elements initialization complete', {
        menuLinks: menuLinks.length,
        hasContentDiv: !!contentDiv,
        hasLoadingSpinner: !!loadingSpinner
    });

    // Funzione per caricare il contenuto
    async function loadContent(section) {
        console.log('RC Race Manager: Inizio caricamento sezione:', section);
        try {
            if (!rcRaceManager || !rcRaceManager.ajaxurl || !rcRaceManager.nonce) {
                console.error('RC Race Manager: Configurazione mancante', rcRaceManager);
                throw new Error('Configurazione RC Race Manager non valida');
            }

            if (loadingSpinner) {
                loadingSpinner.classList.remove('d-none');
            }

            const formData = new FormData();
            formData.append('action', 'rc_race_manager_load_section');
            formData.append('section', section);
            formData.append('nonce', rcRaceManager.nonce);

            // Log completo dei dati inviati
            console.log('RC Race Manager: Dati richiesta:', {
                action: 'rc_race_manager_load_section',
                section: section,
                hasNonce: !!rcRaceManager.nonce,
                url: rcRaceManager.ajaxurl
            });

            const response = await fetch(rcRaceManager.ajaxurl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            console.log('RC Race Manager: Stato risposta:', response.status, response.statusText);

            const contentType = response.headers.get('content-type');
            console.log('RC Race Manager: Content-Type risposta:', contentType);

            let text = await response.text();
            console.log('RC Race Manager: Risposta raw:', text);

            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('RC Race Manager: Errore parsing JSON:', e);
                console.error('RC Race Manager: Testo risposta:', text);
                throw new Error('Risposta non valida dal server');
            }

            console.log('RC Race Manager: Dati risposta elaborati:', data);

            if (!data.success) {
                console.error('RC Race Manager: Errore server:', data);
                let errorMessage = data.data?.message || 'Errore nel caricamento del contenuto';
                if (data.data?.debug) {
                    console.error('RC Race Manager: Debug info:', data.data.debug);
                    errorMessage += '\nDettagli debug disponibili nella console.';
                }
                throw new Error(errorMessage);
            }

            if (!data.data?.html) {
                console.error('RC Race Manager: Contenuto mancante nella risposta:', data);
                throw new Error('Contenuto non valido nella risposta');
            }

            contentDiv.innerHTML = data.data.html;

            // Inizializza form pista se presente
            const formPista = document.getElementById('formNuovaPista');
            if (formPista) {
                console.log('RC Race Manager: Form pista trovato, inizializzazione');
                handleFormPista(formPista);
            }

            // Inizializza i tooltip di Bootstrap
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

        } catch (error) {
            console.error('RC Race Manager: Errore:', error);
            contentDiv.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Errore:</strong> ${error.message}
                    <br>
                    <small>Riprova più tardi o contatta l'amministratore se l'errore persiste.</small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
        } finally {
            if (loadingSpinner) {
                loadingSpinner.classList.add('d-none');
            }
        }
    }

    // Event listeners per i link del menu
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('RC Race Manager: Click su menu item:', this.dataset.section);

            // Aggiorna classe active
            menuLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');

            // Carica il contenuto
            const section = this.dataset.section;
            loadContent(section);
        });
    });

    // Carica la sezione gare all'avvio se non è già stato caricato un contenuto
    if (contentDiv && !contentDiv.innerHTML.trim()) {
        console.log('RC Race Manager: Caricamento sezione iniziale (gare)');
        loadContent('gare');

        // Attiva il link del menu corrispondente
        const defaultLink = document.querySelector('.list-group-item[data-section="gare"]');
        if (defaultLink) {
            defaultLink.classList.add('active');
        }
    }
});

// Gestione form pista
function handleFormPista(form) {
    console.log('RC Race Manager: Inizializzazione gestione form pista');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        console.log('RC Race Manager: Form pista submitted');

        // Log dei dati del form
        const formData = new FormData(this);
        console.log('RC Race Manager: Form data:', Object.fromEntries(formData));
        console.log('RC Race Manager: Using nonce:', rcRaceManager.nonce);
        console.log('RC Race Manager: Ajax URL:', rcRaceManager.ajaxurl);

        // Rimuovi messaggi precedenti
        const messageContainer = this.querySelector('#formMessages');
        messageContainer.innerHTML = '';

        // Validazione form
        if (!this.checkValidity()) {
            console.log('RC Race Manager: Form validation failed');
            e.stopPropagation();
            this.classList.add('was-validated');
            return;
        }

        // Prepara dati form per AJAX
        formData.append('action', 'rc_race_manager_add_pista');
        formData.append('nonce', rcRaceManager.nonce);

        // Gestione pulsante submit
        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvataggio...';

        try {
            console.log('RC Race Manager: Invio richiesta AJAX');
            const response = await fetch(rcRaceManager.ajaxurl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            console.log('RC Race Manager: Stato risposta:', response.status, response.statusText);
            console.log('RC Race Manager: Headers risposta:', Object.fromEntries(response.headers));

            const text = await response.text();
            console.log('RC Race Manager: Risposta raw:', text);

            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('RC Race Manager: Errore parsing JSON:', e);
                throw new Error('Risposta non valida dal server: ' + text);
            }

            console.log('RC Race Manager: Risposta elaborata:', data);

            if (data.success) {
                messageContainer.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        ${data.data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;

                // Reset form e chiudi modal
                this.reset();
                this.classList.remove('was-validated');
                const modal = document.getElementById('modalNuovaPista');
                if (modal) {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                }

                // Ricarica la pagina dopo 1.5 secondi
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                console.error('RC Race Manager: Errore dal server:', data);
                let errorMessage = data.data?.message || 'Errore durante il salvataggio';
                if (data.data?.debug) {
                    console.error('RC Race Manager: Debug info:', data.data.debug);
                    errorMessage += '<br><small>Controlla la console per maggiori dettagli</small>';
                }
                messageContainer.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        ${errorMessage}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
            }
        } catch (error) {
            console.error('RC Race Manager: Errore durante il salvataggio:', error);
            messageContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Errore durante il salvataggio: ${error.message}
                    <br>
                    <small>Controlla la console per maggiori dettagli</small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
        } finally {
            // Ripristina pulsante
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    });
}