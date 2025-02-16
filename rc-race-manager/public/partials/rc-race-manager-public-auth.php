<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="container mt-4">
    <div class="row">
        <!-- Login Form -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4">Accedi</h3>
                    <form id="loginForm" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="rc_race_manager_login">
                        <input type="hidden" name="security" value="<?php echo wp_create_nonce('rc_race_manager_auth_nonce'); ?>">

                        <div class="mb-3">
                            <label for="login_username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="login_username" name="username" required>
                            <div class="invalid-feedback">Inserisci il tuo username.</div>
                        </div>

                        <div class="mb-3">
                            <label for="login_password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="login_password" name="password" required>
                            <div class="invalid-feedback">Inserisci la password.</div>
                        </div>

                        <div id="loginMessages"></div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Accedi
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Registration Form -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4">Registrati</h3>
                    <form id="registrationForm" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="rc_race_manager_register">
                        <input type="hidden" name="security" value="<?php echo wp_create_nonce('rc_race_manager_auth_nonce'); ?>">

                        <div class="mb-3">
                            <label for="reg_username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="reg_username" name="username" required>
                            <div class="invalid-feedback">Scegli un username.</div>
                        </div>

                        <div class="mb-3">
                            <label for="reg_email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="reg_email" name="email" required>
                            <div class="invalid-feedback">Inserisci un indirizzo email valido.</div>
                        </div>

                        <div class="mb-3">
                            <label for="reg_password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="reg_password" name="password" required>
                            <div class="invalid-feedback">Scegli una password.</div>
                        </div>

                        <div class="mb-3">
                            <label for="reg_password_confirm" class="form-label">Conferma Password *</label>
                            <input type="password" class="form-control" id="reg_password_confirm" name="password_confirm" required>
                            <div class="invalid-feedback">Le password non coincidono.</div>
                        </div>

                        <div id="registrationMessages"></div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Registrati
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Login Form Handler
    const loginForm = document.getElementById('loginForm');
    const loginMessages = document.getElementById('loginMessages');

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!this.checkValidity()) {
            e.stopPropagation();
            this.classList.add('was-validated');
            return;
        }

        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Accesso in corso...';

        fetch(rcRaceManager.ajaxurl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            loginMessages.innerHTML = `
                <div class="alert ${data.success ? 'alert-success' : 'alert-danger'} alert-dismissible fade show mt-3">
                    ${data.data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            if (data.success) {
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        })
        .catch(error => {
            loginMessages.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show mt-3">
                    Errore durante l'accesso. Riprova più tardi.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        });
    });

    // Registration Form Handler
    const registrationForm = document.getElementById('registrationForm');
    const registrationMessages = document.getElementById('registrationMessages');

    registrationForm.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            e.stopPropagation();
            this.classList.add('was-validated');
            return;
        }

        // Verifica password
        const password = this.querySelector('#reg_password').value;
        const passwordConfirm = this.querySelector('#reg_password_confirm').value;

        if (password !== passwordConfirm) {
            registrationMessages.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show mt-3">
                    Le password non coincidono.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            return;
        }

        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Registrazione in corso...';

        fetch(rcRaceManager.ajaxurl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            registrationMessages.innerHTML = `
                <div class="alert ${data.success ? 'alert-success' : 'alert-danger'} alert-dismissible fade show mt-3">
                    ${data.data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            if (data.success) {
                this.reset();
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        })
        .catch(error => {
            registrationMessages.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show mt-3">
                    Errore durante la registrazione. Riprova più tardi.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        });
    });
});
</script>
