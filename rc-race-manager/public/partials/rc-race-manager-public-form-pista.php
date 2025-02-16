<!-- Modal Nuova Pista -->
<div class="modal fade" id="modalNuovaPista" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-flag-checkered me-2"></i>
                    Nuova Pista
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuovaPista" class="needs-validation" novalidate>
                <!-- Campo nascosto per il nonce -->
                <input type="hidden" name="security" value="<?php echo wp_create_nonce('rc_race_manager_nonce'); ?>">

                <div id="formMessages"></div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="nome" class="form-label">Nome Pista *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map"></i></span>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <div class="invalid-feedback">Inserisci il nome della pista</div>
                        </div>

                        <div class="col-12">
                            <label for="localita" class="form-label">Località *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" class="form-control" id="localita" name="localita" required>
                            </div>
                            <div class="invalid-feedback">Inserisci la località della pista</div>
                        </div>

                        <div class="col-12">
                            <label for="descrizione" class="form-label">Descrizione</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                                <textarea class="form-control" id="descrizione" name="descrizione" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annulla
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Salva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
