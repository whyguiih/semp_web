<?php
session_start();
require_once 'api.php';

if (!isset($_SESSION['logado'])) { header("Location: index.php"); exit(); }

$produtos_carrinho = chamarAPI('/carrinho', 'GET');
if (!is_array($produtos_carrinho)) $produtos_carrinho = [];
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Carrinho - SEMP</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="sidebar">
        <a href="estoque.php"><img src="img/logo_menor.png" alt="Logo"></a>
        <a href="estoque.php"><img src="img/lista.png" alt="Lista"></a>
        <a href="carrinho.php"><img src="img/carrinho.png" alt="Carrinho"></a>
        <?php if ($_SESSION['nivel_conta'] == '1' || $_SESSION['nivel_conta'] == '2'): ?>
            <a href="autorizar_pedidos.php"><img src="img/controle.png" alt="Controlo"></a>
        <?php endif; ?>
        <?php if ($_SESSION['nivel_conta'] == '1'): ?>
            <a href="cadastro_produto.php"><img src="img/lupa.png" alt="Cadastro"></a>
        <?php endif; ?>
        <div class="sidebar-bottom"><a href="logout.php"><img src="img/sair.png" alt="Sair"></a></div>
    </div>

    <div class="main-content">
        <h1 style="color: #1a4b9f; margin-bottom: 20px;">O teu Carrinho</h1>
        
        <?php if(empty($produtos_carrinho)): ?>
            <h2 style="color: #333;">O teu carrinho está vazio.</h2>
        <?php else: ?>
            <?php foreach ($produtos_carrinho as $item): ?>
                <div class="cart-item">
                    <div>
                        <h2 style="color: #1a4b9f; margin: 0;"><?= htmlspecialchars($item['nome']) ?></h2>
                        <p style="margin: 5px 0 0 0;">Cód: <?= htmlspecialchars($item['codigo']) ?> | Unidade: <?= htmlspecialchars($item['uni_natal']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            <br>
            <button class="btn-primary">Solicitar Pedido</button>
        <?php endif; ?>
    </div>
</body>
</html>