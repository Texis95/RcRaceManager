<?php
$current_user = $this->auth->get_current_user();
$is_logged_in = $this->auth->is_user_logged_in();
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

    <div class="container-fluid">
        <?php if (!$is_logged_in): ?>
            <?php include_once 'rc-race-manager-public-auth.php'; ?>
        <?php else: ?>
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3 d-none d-lg-block">
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush rounded-0">
                                <a href="#" class="list-group-item list-group-item-action d-flex align-items-center active" data-section="gare">
                                    <i class="fas fa-trophy fa-fw me-2"></i> Gare
                                </a>
                                <a href="#" class="list-group-item list-group-item-action d-flex align-items-center" data-section="piste">
                                    <i class="fas fa-road fa-fw me-2"></i> Piste
                                </a>
                                <a href="#" class="list-group-item list-group-item-action d-flex align-items-center" data-section="piloti">
                                    <i class="fas fa-user fa-fw me-2"></i> Piloti
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenuto principale -->
                <div class="col-lg-9">
                    <div id="rc-race-content" class="card shadow-sm">
                        <div class="card-body">
                            <div class="loading-spinner text-center d-none">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Caricamento...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($is_logged_in): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const logoutButton = document.getElementById('logoutButton');

    logoutButton.addEventListener('click', function(e) {
        e.preventDefault();

        const formData = new FormData();
        formData.append('action', 'rc_race_manager_logout');
        formData.append('security', '<?php echo wp_create_nonce("rc_race_manager_auth_nonce"); ?>');

        fetch(rcRaceManager.ajaxurl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    });
});
</script>
<?php endif; ?>