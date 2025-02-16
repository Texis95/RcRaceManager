(function($) {
    'use strict';

    $(document).ready(function() {
        console.log('RC Race Manager: jQuery ready');
    });

})(jQuery);

document.addEventListener('DOMContentLoaded', function() {
    console.log('RC Race Manager: Initializing public interface');

    if (typeof rcRaceManager === 'undefined') {
        console.error('RC Race Manager: rcRaceManager non è disponibile');
        return;
    }

    console.log('RC Race Manager: Checking rcRaceManager configuration...', rcRaceManager);
    if (!rcRaceManager || !rcRaceManager.ajaxurl || !rcRaceManager.security) {
        console.error('RC Race Manager: rcRaceManager is improperly configured:', rcRaceManager);
        return;
    }

    // Funzione per caricare il contenuto
    async function loadContent(section) {
        console.log('RC Race Manager: Inizio caricamento sezione:', section);
        try {
            const formData = new FormData();
            formData.append('action', 'rc_race_manager_load_section');
            formData.append('section', section);
            formData.append('security', rcRaceManager.security);

            console.log('RC Race Manager: Sending request:', {
                action: 'rc_race_manager_load_section',
                section: section,
                security: rcRaceManager.security,
                url: rcRaceManager.ajaxurl
            });

            const response = await fetch(rcRaceManager.ajaxurl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Errore di rete');
            }

            let data;
            try {
                const text = await response.text();
                data = JSON.parse(text);
            } catch (e) {
                console.error('RC Race Manager: Errore parsing JSON:', e);
                throw new Error('Risposta non valida dal server');
            }

            if (!data.success) {
                if (data.data?.error === 'not_logged_in') {
                    alert('Devi effettuare il login per accedere a questa funzionalità');
                    return;
                }
                throw new Error(data.data?.message || 'Errore nel caricamento del contenuto');
            }

            document.getElementById('rc-race-content').innerHTML = data.data.html;

            // Inizializza gli elementi dopo il caricamento del contenuto
            initializeForms();
            initializeViewToggles();
            loadSavedViewPreferences();

        } catch (error) {
            console.error('RC Race Manager: Errore:', error);
            document.getElementById('rc-race-content').innerHTML = `
                <div class="alert alert-danger">
                    <strong>Errore:</strong> ${error.message}
                </div>`;
        }
    }

    // Funzione per cambiare vista
    function toggleView(view, section) {
        console.log('RC Race Manager: Toggling view to:', view, 'for section:', section);

        const listView = document.getElementById(`listView-${section}`);
        const gridView = document.getElementById(`gridView-${section}`);
        const viewListBtn = document.querySelector(`[data-view="list"][data-section="${section}"]`);
        const viewGridBtn = document.querySelector(`[data-view="grid"][data-section="${section}"]`);

        if (!listView || !gridView || !viewListBtn || !viewGridBtn) {
            console.error('RC Race Manager: Missing view elements for section:', section);
            return;
        }

        if (view === 'grid') {
            listView.classList.add('d-none');
            gridView.classList.remove('d-none');
            viewListBtn.classList.remove('active');
            viewGridBtn.classList.add('active');
            localStorage.setItem(`viewPreference-${section}`, 'grid');
        } else {
            gridView.classList.add('d-none');
            listView.classList.remove('d-none');
            viewGridBtn.classList.remove('active');
            viewListBtn.classList.add('active');
            localStorage.setItem(`viewPreference-${section}`, 'list');
        }
    }

    // Inizializza i toggle della vista
    function initializeViewToggles() {
        console.log('RC Race Manager: Initializing view toggles');
        const container = document.getElementById('rc-race-content');
        if (!container) return;

        const viewButtons = container.querySelectorAll('[data-view]');
        viewButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const section = this.dataset.section;
                const view = this.dataset.view;
                console.log('RC Race Manager: View button clicked:', view, 'for section:', section);
                toggleView(view, section);
            });
        });
    }

    // Carica le preferenze salvate della vista
    function loadSavedViewPreferences() {
        console.log('RC Race Manager: Loading saved view preferences');
        const container = document.getElementById('rc-race-content');
        if (!container) return;

        const sections = ['piloti', 'piste', 'gare'];
        sections.forEach(section => {
            const savedView = localStorage.getItem(`viewPreference-${section}`);
            if (savedView) {
                console.log('RC Race Manager: Found saved view for section:', section, savedView);
                toggleView(savedView, section);
            }
        });
    }

    // Inizializzazione dei form
    const forms = {
        'formNuovaGara': 'rc_race_manager_add_gara',
        'formNuovoPilota': 'rc_race_manager_add_pilota',
        'formNuovaPista': 'rc_race_manager_add_pista',
        'formRegistrazionePilota': 'rc_race_manager_add_pilota'
    };

    function handleFormSubmit(form, action) {
        console.log('RC Race Manager: Initializing form handler for:', form.id, 'with action:', action);

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('RC Race Manager: Form submitted:', action);

            const messageContainer = this.querySelector('#formMessages');
            messageContainer.innerHTML = '';

            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }

            const formData = new FormData(this);
            formData.append('action', action);
            formData.append('security', rcRaceManager.security);

            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvataggio...';

            try {
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
                    this.reset();
                    this.classList.remove('was-validated');
                    const modal = bootstrap.Modal.getInstance(this.closest('.modal'));
                    if (modal) {
                        modal.hide();
                    }
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else if (data.data?.error === 'not_logged_in') {
                    alert('Devi effettuare il login per completare questa operazione');
                    const modal = bootstrap.Modal.getInstance(this.closest('.modal'));
                    if (modal) {
                        modal.hide();
                    }
                    return;
                }

                const alert = document.createElement('div');
                alert.className = `alert ${data.success ? 'alert-success' : 'alert-danger'} alert-dismissible fade show`;
                alert.innerHTML = `
                    ${data.data?.message || data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                messageContainer.appendChild(alert);

            } catch (error) {
                console.error('RC Race Manager: Error:', error);
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show';
                alert.innerHTML = `
                    ${rcRaceManager.strings.network_error}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                messageContainer.appendChild(alert);
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });

        // Resetta il form quando il modale viene chiuso
        const modal = form.closest('.modal');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function () {
                form.reset();
                form.classList.remove('was-validated');
                form.querySelector('#formMessages').innerHTML = '';
            });
        }
    }

    function initializeForms() {
        for (const [formId, action] of Object.entries(forms)) {
            const form = document.getElementById(formId);
            if (form) {
                console.log(`RC Race Manager: Initializing form ${formId} with action ${action}`);
                handleFormSubmit(form, action);
            }
        }
    }

    // Inizializzazione iniziale
    const defaultSection = 'gare';
    loadContent(defaultSection);

    // Event listener per i link del menu
    document.querySelectorAll('.list-group-item').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.list-group-item').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            loadContent(this.dataset.section);
        });
    });
});