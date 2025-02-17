<?php
$current_user = $this->auth->get_current_user();
$is_logged_in = $this->auth->is_user_logged_in();

// Log per debug
error_log('RC Race Manager: Stato autenticazione - Logged in: ' . ($is_logged_in ? 'Si' : 'No'));
if ($is_logged_in) {
    error_log('RC Race Manager: User ID: ' . $current_user['ID'] . ', Username: ' . $current_user['username']);
}
?>

<div class="rc-race-manager-container">
    <!-- Navbar principale -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-flag-checkered me-2"></i>
                RC Race Manager
            </a>
            <div class="ms-auto">
                <?php if ($is_logged_in): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" id="userMenuButton" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-2"></i><?php echo esc_html($current_user['username']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="#" id="logoutButton">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <!-- Menu laterale -->
            <div class="col-md-3 mb-4">
                <div class="list-group shadow-sm">
                    <a href="#" class="list-group-item list-group-item-action active" data-section="gare">
                        <i class="fas fa-trophy"></i> Calendario Gare
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" data-section="piloti">
                        <i class="fas fa-users"></i> Piloti
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" data-section="piste">
                        <i class="fas fa-road"></i> Piste
                    </a>
                </div>
            </div>

            <!-- Contenuto principale -->
            <div class="col-md-9">
                <?php if (!$is_logged_in): ?>
                    <?php 
                    error_log('RC Race Manager: Caricamento template autenticazione');
                    include_once 'rc-race-manager-public-auth.php'; 
                    ?>
                <?php else: ?>
                    <div id="rc-race-content" class="card shadow-sm">
                        <div class="card-body">
                            <div class="loading-spinner d-none">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Caricamento...</span>
                                </div>
                            </div>
                            <?php include_once 'rc-race-manager-public-calendario.php'; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($is_logged_in): ?>
        <!-- Includi il modale di selezione pilota -->
        <?php include_once 'rc-race-manager-public-modal-selezione-pilota.php'; ?>
    <?php endif; ?>
</div>

<?php if ($is_logged_in): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestione logout
    const logoutButton = document.getElementById('logoutButton');
    if (logoutButton) {
        logoutButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Tentativo di logout...');
            handleLogout();
        });
    }
});
</script>
<?php endif; ?>