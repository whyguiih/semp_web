<?php
session_start();
require_once 'conexao.php';

// Apenas Nível 1 (Admin) ou Nível 2 (Compras) podem acessar
if ($_SESSION['nivel_conta'] == '0') { header("Location: estoque.php"); exit(); }

// Lógica de Aceitar ou Recusar
if (isset($_GET['acao']) && isset($_GET['id_emprestimo'])) {
    $id = $_GET['id_emprestimo'];
    $novoStatus = ($_GET['acao'] == 'aceitar') ? 1 : 2; // 1 = Aprovado, 2 = Recusado

    $stmtUpdate = $pdo->prepare("UPDATE tb_emprestimo SET aprovacao = ? WHERE id = ?");
    $stmtUpdate->execute([$novoStatus, $id]);
    
    header("Location: autorizar_pedidos.php?msg=Atualizado");
    exit();
}

$unidade_logada = $_SESSION['unidade'];
// Traz os pedidos pendentes (processamento = 1, aprovacao = 0) para a unidade logada
$sql = "SELECT * FROM tb_emprestimo WHERE processamento = 1 AND unidade_natal = ? AND aprovacao = 0";
$stmt = $pdo->prepare($sql);
$stmt->execute([$unidade_logada]);
$pedidosPendentes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Autorizar Pedidos</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="main-content">
        <h1 style="color: #1a4b9f;">Pedidos Aguardando Autorização</h1>
        
        <?php if(empty($pedidosPendentes)): ?>
            <h2 style="color: #333;">Nenhum pedido pendente na sua unidade no momento.</h2>
        <?php else: ?>
            <?php foreach ($pedidosPendentes as $pedido): ?>
                <div class="cart-item">
                    <div>
                        <h2>Pedido de: <?= htmlspecialchars($pedido['remetente'] ?? 'Desconhecido') ?></h2>
                        <p>Produto: <?= htmlspecialchars($pedido['nome_produto']) ?> | Quantidade: <?= htmlspecialchars($pedido['quant']) ?></p>
                        <p>Destino: <?= htmlspecialchars($pedido['destinatario']) ?></p>
                    </div>
                    <div>
                        <a href="autorizar_pedidos.php?acao=aceitar&id_emprestimo=<?= $pedido['id'] ?>" class="btn-primary" style="text-decoration:none; margin-right: 10px;">Liberar (1)</a>
                        <a href="autorizar_pedidos.php?acao=recusar&id_emprestimo=<?= $pedido['id'] ?>" class="btn-primary btn-danger" style="text-decoration:none;">Deletar (2)</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>