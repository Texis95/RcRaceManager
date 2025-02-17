<!-- Modal Nuovo Pilota -->
<div class="modal fade" id="modalNuovoPilota" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>
                    Registrazione Pilota
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuovoPilota" class="needs-validation" novalidate>
                <!-- Campo nascosto per il nonce -->
                <input type="hidden" name="security" value="<?php echo wp_create_nonce('rc_race_manager_nonce'); ?>">

                <div id="formMessages"></div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nome" class="form-label">Nome *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <div class="invalid-feedback">Inserisci il nome</div>
                        </div>

                        <div class="col-md-6">
                            <label for="cognome" class="form-label">Cognome *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="cognome" name="cognome" required>
                            </div>
                            <div class="invalid-feedback">Inserisci il cognome</div>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="invalid-feedback">Inserisci un indirizzo email valido</div>
                        </div>

                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Telefono</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" class="form-control" id="telefono" name="telefono">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="categoria_id" class="form-label">Categoria *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-trophy"></i></span>
                                <select class="form-select" id="categoria_id" name="categoria_id" required>
                                    <option value="">Seleziona una categoria</option>
                                    <?php foreach ($categorie as $categoria): ?>
                                        <option value="<?php echo $categoria->id; ?>">
                                            <?php echo esc_html($categoria->nome); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="invalid-feedback">Seleziona una categoria</div>
                        </div>

                        <div class="col-md-6">
                            <label for="trasponder" class="form-label">Trasponder *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-broadcast-tower"></i></span>
                                <input type="text" class="form-control" id="trasponder" name="trasponder" required>
                            </div>
                            <div class="invalid-feedback">Inserisci il numero del trasponder</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annulla
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Registrati
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
