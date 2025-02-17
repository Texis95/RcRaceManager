/**
 * Gestione delle funzionalità del calendario gare
 */
(function($) {
    'use strict';

    // Debug info
    console.log('RC Race Manager Calendar: Inizializzazione script calendario');
    console.log('RC Race Manager Calendar: User logged in:', rcRaceManagerCalendar.user_logged_in);
    console.log('RC Race Manager Calendar: AJAX URL:', rcRaceManagerCalendar.ajaxurl);

    // Funzione per visualizzare i dettagli della gara
    window.visualizzaDettagliGara = function(garaId) {
        console.log('Richiesta visualizzazione dettagli per gara:', garaId);

        const formData = new FormData();
        formData.append('action', 'rc_race_manager_get_dettagli_gara');
        formData.append('gara_id', garaId);
        formData.append('security', rcRaceManagerCalendar.security);

        // Carica i dettagli della gara
        fetch(rcRaceManagerCalendar.ajaxurl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            console.log('Risposta server per dettagli gara:', data);
            if (data.success) {
                // Aggiorna i dettagli della gara
                document.querySelector('#dettagliGaraContent').innerHTML = data.data.dettagli_html;

                // Aggiorna la lista degli iscritti
                document.querySelector('#listaIscrittiContent').innerHTML = data.data.iscritti_html;

                // Aggiorna i pulsanti di azione
                const azioniContainer = document.querySelector('#azioniGara');
                azioniContainer.innerHTML = '';

                // Pulsante Esporta PDF
                const btnEsporta = document.createElement('button');
                btnEsporta.className = 'btn btn-secondary';
                btnEsporta.innerHTML = '<i class="fas fa-file-pdf me-2"></i>Esporta PDF';
                btnEsporta.onclick = () => esportaPDF(garaId);
                azioniContainer.appendChild(btnEsporta);

                // Pulsante Iscrizione/Cancellazione
                if (rcRaceManagerCalendar.user_logged_in) {
                    const btnIscrizione = document.createElement('button');
                    if (data.data.iscritto) {
                        btnIscrizione.className = 'btn btn-danger';
                        btnIscrizione.innerHTML = '<i class="fas fa-times me-2"></i>Cancella Iscrizione';
                        btnIscrizione.onclick = () => cancellaIscrizione(garaId);
                    } else {
                        btnIscrizione.className = 'btn btn-primary';
                        btnIscrizione.innerHTML = '<i class="fas fa-plus me-2"></i>Iscriviti';
                        btnIscrizione.onclick = () => iscrivitiGara(garaId);
                    }
                    azioniContainer.appendChild(btnIscrizione);
                }

                // Mostra il modale
                new bootstrap.Modal(document.getElementById('modalDettagliGara')).show();
            } else {
                console.error('Errore dal server:', data.data.message);
                alert(data.data.message);
            }
        })
        .catch(error => {
            console.error('Errore durante il recupero dei dettagli:', error);
            alert(rcRaceManager.strings.network_error);
        });
    };

    // Funzione per iscriversi a una gara
    window.iscrivitiGara = function(garaId) {
        console.log('Richiesta iscrizione alla gara:', garaId);

        if (!rcRaceManagerCalendar.user_logged_in) {
            console.log('Utente non autenticato, blocco iscrizione');
            alert('Devi effettuare il login per iscriverti a una gara');
            return;
        }

        if (confirm('Confermi l\'iscrizione alla gara?')) {
            const formData = new FormData();
            formData.append('action', 'rc_race_manager_iscrivi_pilota');
            formData.append('gara_id', garaId);
            formData.append('security', rcRaceManagerCalendar.security);

            console.log('Invio richiesta iscrizione al server...');
            fetch(rcRaceManagerCalendar.ajaxurl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                console.log('Risposta server per iscrizione:', data);
                if (data.success) {
                    alert(rcRaceManager.strings.iscrizione_success);
                    visualizzaDettagliGara(garaId); // Aggiorna i dettagli
                } else {
                    console.error('Errore dal server:', data.data.message);
                    alert(data.data.message || rcRaceManager.strings.iscrizione_error);
                }
            })
            .catch(error => {
                console.error('Errore durante l\'iscrizione:', error);
                alert(rcRaceManager.strings.network_error);
            });
        }
    };

    // Funzione per cancellare l'iscrizione a una gara
    window.cancellaIscrizione = function(garaId) {
        console.log('Richiesta cancellazione iscrizione dalla gara:', garaId);

        if (confirm('Sei sicuro di voler cancellare la tua iscrizione?')) {
            const formData = new FormData();
            formData.append('action', 'rc_race_manager_cancella_iscrizione');
            formData.append('gara_id', garaId);
            formData.append('security', rcRaceManagerCalendar.security);

            console.log('Invio richiesta cancellazione al server...');
            fetch(rcRaceManagerCalendar.ajaxurl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                console.log('Risposta server per cancellazione:', data);
                if (data.success) {
                    alert(rcRaceManager.strings.cancellazione_success);
                    visualizzaDettagliGara(garaId); // Aggiorna i dettagli
                } else {
                    console.error('Errore dal server:', data.data.message);
                    alert(data.data.message || rcRaceManager.strings.cancellazione_error);
                }
            })
            .catch(error => {
                console.error('Errore durante la cancellazione:', error);
                alert(rcRaceManager.strings.network_error);
            });
        }
    };

    // Funzione per esportare la lista iscritti in PDF
    window.esportaPDF = function(garaId) {
        console.log('Richiesta esportazione PDF per gara:', garaId);

        const formData = new FormData();
        formData.append('action', 'rc_race_manager_esporta_pdf');
        formData.append('gara_id', garaId);
        formData.append('security', rcRaceManagerCalendar.security);

        console.log('Invio richiesta esportazione PDF al server...');
        fetch(rcRaceManagerCalendar.ajaxurl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.blob())
        .then(blob => {
            console.log('PDF ricevuto dal server, avvio download...');
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `iscritti-gara-${garaId}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
        })
        .catch(error => {
            console.error('Errore durante l\'esportazione PDF:', error);
            alert(rcRaceManager.strings.network_error);
        });
    };

    // Log di completamento inizializzazione
    console.log('RC Race Manager Calendar: Script calendario inizializzato con successo');

})(jQuery);