<?php
// Protezione accesso diretto
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>RC Race Manager</h1>
    <div class="card">
        <h2>Benvenuto in RC Race Manager</h2>
        <p>Gestisci le tue gare RC, piloti e piste da questa dashboard.</p>
        
        <div class="stats-container">
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <?php
                            global $wpdb;
                            $piloti_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rc_piloti");
                            ?>
                            <h5>Piloti Registrati</h5>
                            <h2><?php echo $piloti_count; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <?php
                            $gare_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rc_gare");
                            ?>
                            <h5>Gare Totali</h5>
                            <h2><?php echo $gare_count; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white mb-4">
                        <div class="card-body">
                            <?php
                            $piste_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rc_piste");
                            ?>
                            <h5>Piste Registrate</h5>
                            <h2><?php echo $piste_count; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
