<?php
// Protezione accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_gare = $wpdb->prefix . 'rc_gare';
$table_piste = $wpdb->prefix . 'rc_piste';

// Query per ottenere l'elenco delle gare approvate
$gare = $wpdb->get_results("
    SELECT g.*, p.nome as pista_nome 
    FROM $table_gare g 
    LEFT JOIN $table_piste p ON g.pista_id = p.id 
    WHERE g.approvato = 1 
    AND g.data >= CURDATE()
    ORDER BY g.data ASC
");
?>

<div class="container">
    <h2 class="mb-4">Calendario Gare</h2>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Gara</th>
                    <th>Pista</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($gare as $gara): ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($gara->data)); ?></td>
                    <td>
                        <strong><?php echo esc_html($gara->titolo); ?></strong>
                        <?php if ($gara->descrizione): ?>
                            <p class="small text-muted mb-0"><?php echo esc_html($gara->descrizione); ?></p>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($gara->pista_nome); ?></td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm" onclick="iscrivitiGara(<?php echo $gara->id; ?>)">
                            Iscriviti
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function iscrivitiGara(garaId) {
    // Qui implementeremo la logica di iscrizione tramite AJAX
    alert('Funzionalità di iscrizione in sviluppo');
}
</script>
