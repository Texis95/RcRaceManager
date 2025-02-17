<!-- Modal Selezione Pilota -->
<div class="modal fade" id="modalSelezionePilota" tabindex="-1" aria-labelledby="modalSelezionePilotaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSelezionePilotaLabel">
                    <i class="fas fa-user-check me-2"></i>
                    Selezione Pilota
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="pilotiListContainer">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Chiudi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalSelezionePilota = new bootstrap.Modal(document.getElementById('modalSelezionePilota'));
    
    // Handler for the "Iscriviti" button
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('btn-iscrivi')) {
            e.preventDefault();
            const garaId = e.target.dataset.garaId;
            
            // Make AJAX call to get pilots list
            const formData = new FormData();
            formData.append('action', 'rc_race_manager_iscrivi_pilota');
            formData.append('gara_id', garaId);
            formData.append('security', rcRaceManager.security);

            fetch(rcRaceManager.ajaxurl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('pilotiListContainer').innerHTML = data.data.html;
                    modalSelezionePilota.show();
                } else {
                    // If no pilots registered, show message and option to register
                    if (data.data.show_registration) {
                        if (confirm(data.data.message + '\nVuoi registrare un nuovo pilota?')) {
                            // Redirect to pilot registration page or show registration modal
                            window.location.href = '#registrazione-pilota';
                        }
                    } else {
                        alert(data.data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore durante il caricamento dei piloti');
            });
        }
    });

    // Handler for pilot selection
    document.getElementById('pilotiListContainer').addEventListener('click', function(e) {
        if (e.target.closest('.pilot-select-btn')) {
            const btn = e.target.closest('.pilot-select-btn');
            const pilotaId = btn.dataset.pilotaId;
            const garaId = btn.dataset.garaId;

            const formData = new FormData();
            formData.append('action', 'rc_race_manager_iscrivi_pilota');
            formData.append('gara_id', garaId);
            formData.append('pilota_id', pilotaId);
            formData.append('security', rcRaceManager.security);

            fetch(rcRaceManager.ajaxurl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                modalSelezionePilota.hide();
                if (data.success) {
                    alert(data.data.message);
                    // Reload the race details or update UI as needed
                    location.reload();
                } else {
                    alert(data.data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore durante l\'iscrizione');
            });
        }
    });
});
</script>
