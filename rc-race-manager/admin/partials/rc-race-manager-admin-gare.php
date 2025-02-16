<?php
// Protezione accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_gare = $wpdb->prefix . 'rc_gare';
$table_piste = $wpdb->prefix . 'rc_piste';

// Gestione approvazione/rifiuto
if (isset($_POST['action']) && isset($_POST['gara_id'])) {
    $gara_id = intval($_POST['gara_id']);
    $action = sanitize_text_field($_POST['action']);
    
    if ($action === 'approva') {
        $wpdb->update(
            $table_gare,
            array('approvato' => 1),
            array('id' => $gara_id),
            array('%d'),
            array('%d')
        );
    } elseif ($action === 'rifiuta') {
        $wpdb->update(
            $table_gare,
            array('approvato' => 0),
            array('id' => $gara_id),
            array('%d'),
            array('%d')
        );
    }
}

// Query per ottenere l'elenco delle gare
$gare = $wpdb->get_results("
    SELECT g.*, p.nome as pista_nome 
    FROM $table_gare g 
    LEFT JOIN $table_piste p ON g.pista_id = p.id 
    ORDER BY g.data DESC
");
?>

<div class="wrap">
    <h1>Gestione Gare</h1>
    
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Titolo</th>
                        <th>Data</th>
                        <th>Pista</th>
                        <th>Stato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gare as $gara): ?>
                    <tr>
                        <td><?php echo esc_html($gara->titolo); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($gara->data)); ?></td>
                        <td><?php echo esc_html($gara->pista_nome); ?></td>
                        <td>
                            <?php if ($gara->approvato): ?>
                                <span class="badge bg-success">Approvata</span>
                            <?php else: ?>
                                <span class="badge bg-warning">In attesa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" style="display: inline">
                                <input type="hidden" name="gara_id" value="<?php echo $gara->id; ?>">
                                <?php if (!$gara->approvato): ?>
                                    <button type="submit" name="action" value="approva" class="button button-primary">
                                        Approva
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="action" value="rifiuta" class="button button-secondary">
                                        Rifiuta
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
