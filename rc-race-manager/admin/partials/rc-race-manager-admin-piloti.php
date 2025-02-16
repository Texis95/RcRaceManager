<?php
// Protezione accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_piloti = $wpdb->prefix . 'rc_piloti';
$table_categorie = $wpdb->prefix . 'rc_categorie';

// Gestione approvazione/rifiuto
if (isset($_POST['action']) && isset($_POST['pilota_id'])) {
    $pilota_id = intval($_POST['pilota_id']);
    $action = sanitize_text_field($_POST['action']);
    
    if ($action === 'approva') {
        $wpdb->update(
            $table_piloti,
            array('approvato' => 1),
            array('id' => $pilota_id),
            array('%d'),
            array('%d')
        );
    } elseif ($action === 'rifiuta') {
        $wpdb->update(
            $table_piloti,
            array('approvato' => 0),
            array('id' => $pilota_id),
            array('%d'),
            array('%d')
        );
    }
}

// Query per ottenere l'elenco dei piloti
$piloti = $wpdb->get_results("
    SELECT p.*, c.nome as categoria_nome 
    FROM $table_piloti p 
    LEFT JOIN $table_categorie c ON p.categoria_id = c.id 
    ORDER BY p.data_registrazione DESC
");
?>

<div class="wrap">
    <h1>Gestione Piloti</h1>
    
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Cognome</th>
                        <th>Email</th>
                        <th>Telefono</th>
                        <th>Categoria</th>
                        <th>Trasponder</th>
                        <th>Stato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($piloti as $pilota): ?>
                    <tr>
                        <td><?php echo esc_html($pilota->nome); ?></td>
                        <td><?php echo esc_html($pilota->cognome); ?></td>
                        <td><?php echo esc_html($pilota->email); ?></td>
                        <td><?php echo esc_html($pilota->telefono); ?></td>
                        <td><?php echo esc_html($pilota->categoria_nome); ?></td>
                        <td><?php echo esc_html($pilota->trasponder); ?></td>
                        <td>
                            <?php if ($pilota->approvato): ?>
                                <span class="badge bg-success">Approvato</span>
                            <?php else: ?>
                                <span class="badge bg-warning">In attesa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" style="display: inline">
                                <input type="hidden" name="pilota_id" value="<?php echo $pilota->id; ?>">
                                <?php if (!$pilota->approvato): ?>
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
