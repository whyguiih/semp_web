<?php
session_start();
require_once 'api.php';

if (!isset($_SESSION['logado']) || $_SESSION['nivel_conta'] == '0') { header("Location: estoque.php"); exit(); }

if (isset($_GET['acao']) && isset($_GET['id_emprestimo'])) {
    $novoStatus = ($_GET['acao'] == 'aceitar') ? 1 : 2; 
    
    chamarAPI('/pedidos/autorizar', 'POST', [
        'id_emprestimo' => $_GET['id_emprestimo'], 
        'novoStatus' => $novoStatus
    ]);
    header("Location: autorizar_pedidos.php?msg=Atualizado");
    exit();
}

// Passa a unidade na URL da API para trazer apenas os pedidos corretos
$pedidosPendentes = chamarAPI('/pedidos/pendentes?unidade=' . urlencode($_SESSION['unidade']), 'GET');
if (!is_array($pedidosPendentes)) $pedidosPendentes = [];
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Autorizar Pedidos</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php
        include 'inc/sidebar.php';
    ?>

    <div class="main-content">
        <h1 style="color: #1a4b9f; margin-bottom: 20px;">Pedidos a Aguardar Autorização</h1>
        
        <?php if(isset($_GET['msg'])) echo "<p style='color: green;'>Estado atualizado com sucesso!</p>"; ?>

        <?php if(empty($pedidosPendentes)): ?>
            <h2 style="color: #333;">Nenhum pedido pendente para a tua unidade.</h2>
        <?php else: ?>
            <?php foreach ($pedidosPendentes as $pedido): ?>
                <div class="cart-item">
                    <div>
                        <h2 style="color: #1a4b9f; margin: 0;">Pedido de: <?= htmlspecialchars($pedido['remetente'] ?? 'Desconhecido') ?></h2>
                        <p>Produto: <?= htmlspecialchars($pedido['nome_produto']) ?> | Qtd: <?= htmlspecialchars($pedido['quant']) ?></p>
                        <p>Destino: <?= htmlspecialchars($pedido['destinatario']) ?></p>
                    </div>
                    <div>
                        <a href="autorizar_pedidos.php?acao=aceitar&id_emprestimo=<?= $pedido['id_emprestimo'] ?>" class="btn-primary" style="text-decoration:none; margin-right:10px; font-size:18px; padding:10px 20px;">Liberar</a>
                        <a href="autorizar_pedidos.php?acao=recusar&id_emprestimo=<?= $pedido['id_emprestimo'] ?>" class="btn-primary btn-danger" style="text-decoration:none; font-size:18px; padding:10px 20px; background-color:#ef5e31;">Deletar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>