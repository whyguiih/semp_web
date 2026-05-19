<?php
session_start();
require_once 'api.php';

if (!isset($_SESSION['logado'])) { header("Location: index.php"); exit(); }

// Busca todos os produtos da API em vez da base de dados local
$produtos = chamarAPI('/produtos', 'GET');
if (!is_array($produtos)) $produtos = []; // Proteção caso a API não devolva nada
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Estoque - SEMP</title>
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
        
        <div class="sidebar-bottom">
            <a href="logout.php"><img src="img/sair.png" alt="Sair"></a>
        </div>
    </div>

    <div class="main-content">
        <div class="search-bar">
            <img src="img/lupa.png" alt="Pesquisar">
            <input type="text" placeholder="Pesquisar produtos...">
        </div>

        <div class="produtos-grid">
            <?php foreach ($produtos as $p): ?>
                <a href="produto.php?id=<?= htmlspecialchars($p['id_estoque']) ?>" class="produto-card">
                    <h2><?= htmlspecialchars($p['nome']) ?></h2>
                    <p>Código: <?= htmlspecialchars($p['codigo']) ?></p>
                    <p>Quantidade: <?= htmlspecialchars($p['quant']) ?></p>
                </a>
            <?php endforeach; ?>
            
            <?php if(empty($produtos)): ?>
                <h2 style="color: #333;">Nenhum produto cadastrado.</h2>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>