<!-- Modal Nuova Gara -->
<div class="modal fade" id="modalNuovaGara" tabindex="-1" aria-labelledby="modalNuovaGaraLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuovaGaraLabel">
                    <i class="fas fa-trophy me-2"></i>
                    Nuova Gara
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuovaGara" class="needs-validation" novalidate>
                <!-- Campo nascosto per il nonce -->
                <input type="hidden" name="security" value="<?php echo wp_create_nonce('rc_race_manager_nonce'); ?>">

                <div id="formMessages"></div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Gara *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-trophy"></i></span>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="invalid-feedback">
                            Inserisci il nome della gara
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="data_gara" class="form-label">Data *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            <input type="datetime-local" class="form-control" id="data_gara" name="data_gara" required>
                        </div>
                        <div class="invalid-feedback">
                            Seleziona la data della gara
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tipo_gara" class="form-label">Tipo Gara *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-flag"></i></span>
                            <select class="form-select" id="tipo_gara" name="tipo_gara" required>
                                <option value="">Seleziona un tipo</option>
                                <?php foreach ($tipi_gara as $key => $value): ?>
                                    <option value="<?php echo esc_attr($key); ?>">
                                        <?php echo esc_html($value); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="invalid-feedback">
                            Seleziona il tipo di gara
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="pista_id" class="form-label">Pista *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            <select class="form-select" id="pista_id" name="pista_id" required>
                                <option value="">Seleziona una pista</option>
                                <?php foreach ($piste as $pista): ?>
                                    <option value="<?php echo esc_attr($pista->id); ?>">
                                        <?php echo esc_html($pista->nome); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="invalid-feedback">
                            Seleziona una pista
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descrizione" class="form-label">Descrizione</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <textarea class="form-control" id="descrizione" name="descrizione" rows="3"></textarea>
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
