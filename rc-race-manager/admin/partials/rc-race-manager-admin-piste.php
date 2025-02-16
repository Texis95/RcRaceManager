<?php
// Protezione accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_piste = $wpdb->prefix . 'rc_piste';

// Gestione approvazione/rifiuto
if (isset($_POST['action']) && isset($_POST['pista_id'])) {
    $pista_id = intval($_POST['pista_id']);
    $action = sanitize_text_field($_POST['action']);
    
    if ($action === 'approva') {
        $wpdb->update(
            $table_piste,
            array('approvato' => 1),
            array('id' => $pista_id),
            array('%d'),
            array('%d')
        );
    } elseif ($action === 'rifiuta') {
        $wpdb->update(
            $table_piste,
            array('approvato' => 0),
            array('id' => $pista_id),
            array('%d'),
            array('%d')
        );
    }
}

// Query per ottenere l'elenco delle piste
$piste = $wpdb->get_results("
    SELECT * FROM $table_piste 
    ORDER BY nome ASC
");
?>

<div class="wrap">
    <h1>Gestione Piste</h1>
    
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Località</th>
                        <th>Descrizione</th>
                        <th>Stato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($piste as $pista): ?>
                    <tr>
                        <td><?php echo esc_html($pista->nome); ?></td>
                        <td><?php echo esc_html($pista->localita); ?></td>
                        <td><?php echo esc_html($pista->descrizione); ?></td>
                        <td>
                            <?php if ($pista->approvato): ?>
                                <span class="badge bg-success">Approvata</span>
                            <?php else: ?>
                                <span class="badge bg-warning">In attesa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" style="display: inline">
                                <input type="hidden" name="pista_id" value="<?php echo $pista->id; ?>">
                                <?php if (!$pista->approvato): ?>
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
