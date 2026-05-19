<?php
session_start();
require_once 'api.php';

if (!isset($_GET['id']) || !isset($_SESSION['logado'])) {
    header("Location: estoque.php"); exit();
}

$id_produto = $_GET['id'];
$produto = chamarAPI('/produtos/' . $id_produto, 'GET');

if (!$produto || isset($produto['erro'])) {
    die("Produto não encontrado!");
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Produto - <?= htmlspecialchars($produto['nome']) ?></title>
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
        <div class="produto-detalhe">
            <div class="produto-detalhe-img">
                <?php if(!empty($produto['foto'])): ?>
                    <img src="<?= htmlspecialchars($produto['foto']) ?>" alt="Foto">
                <?php else: ?>
                    <img src="img/imagem.png" alt="Sem Foto">
                <?php endif; ?>
            </div>
            
            <div class="produto-detalhe-info">
                <h1><?= htmlspecialchars($produto['nome']) ?></h1>
                <h3>Código: <?= htmlspecialchars($produto['codigo']) ?></h3>
                <h3>Unidade Original: <?= htmlspecialchars($produto['uni_natal']) ?></h3>
                <p><?= htmlspecialchars($produto['descricao']) ?></p>
                <p><strong>Em stock:</strong> <?= htmlspecialchars($produto['quant']) ?></p>
                
                <form action="acao_carrinho.php" method="POST">
                    <input type="hidden" name="id_produto" value="<?= $produto['id_estoque'] ?>">
                    <button type="submit" class="btn-primary">Adicionar ao Carrinho</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>