<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['logado'])) {
    header("Location: index.php");
    exit();
}

// Lógica traduzida da classe carrinho.java
$stmt = $pdo->prepare("SELECT * FROM tb_estoque WHERE carrinho = '1'");
$stmt->execute();
$produtos_carrinho = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Carrinho - SEMP</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 15px;
        }
        .cart-item h2 { margin: 0; color: #1a4b9f; }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="estoque.php"><img src="img/logo_menor.png" alt="Logo"></a>
        <a href="estoque.php"><img src="img/lista.png" alt="Lista"></a>
        <a href="carrinho.php"><img src="img/carrinho.png" alt="Carrinho"></a>
        
        <div class="sidebar-bottom">
            <a href="logout.php"><img src="img/sair.png" alt="Sair"></a>
        </div>
    </div>

    <div class="main-content">
        <h1 style="color: #1a4b9f;">Seu Carrinho</h1>
        
        <?php if(empty($produtos_carrinho)): ?>
            <h2 style="color: #333;">Seu carrinho está vazio.</h2>
        <?php else: ?>
            <?php foreach ($produtos_carrinho as $item): ?>
                <div class="cart-item">
                    <h2><?= htmlspecialchars($item['nome']) ?> (Cód: <?= htmlspecialchars($item['codigo']) ?>)</h2>
                    <p>Unidade: <?= htmlspecialchars($item['uni_natal']) ?></p>
                </div>
            <?php endforeach; ?>
            
            <br>
            <button class="btn-primary">Solicitar Pedido</button>
        <?php endif; ?>
    </div>
</body>
</html>