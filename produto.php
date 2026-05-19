<?php
session_start();
require_once 'conexao.php';

if (!isset($_GET['id'])) {
    header("Location: estoque.php");
    exit();
}

$id_produto = $_GET['id'];

// Busca o produto específico (Mesma lógica do controller_carrinho.java)
$stmt = $pdo->prepare("SELECT * FROM tb_estoque WHERE id_estoque = ?");
$stmt->execute([$id_produto]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    die("Produto não encontrado!");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
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
        
        <div class="sidebar-bottom">
            <a href="logout.php"><img src="img/sair.png" alt="Sair"></a>
        </div>
    </div>

    <div class="main-content">
        <div class="produto-detalhe">
            <div class="produto-detalhe-img">
                <img src="img/imagem.png" alt="Foto Produto">
            </div>
            
            <div class="produto-detalhe-info">
                <h1><?= htmlspecialchars($produto['nome']) ?></h1>
                <h3>Código: <?= htmlspecialchars($produto['codigo']) ?></h3>
                <h3>Disponível na Unidade: <?= htmlspecialchars($produto['uni_natal']) ?></h3>
                <p><?= htmlspecialchars($produto['descricao_detalhada']) ?></p>
                
                <form action="acao_carrinho.php" method="POST">
                    <input type="hidden" name="id_produto" value="<?= $produto['id_estoque'] ?>">
                    <button type="submit" class="btn-primary">Adicionar ao Carrinho</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>