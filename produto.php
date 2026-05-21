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
    <?php
        include 'inc/sidebar.php';
    ?>
<div class="main-content" style="position: relative;">
        <div class="produto-detalhe">
            
            <div class="produto-col-esquerda">
                <div class="produto-detalhe-img">
                    <img src="<?= !empty($produto['foto']) ? htmlspecialchars($produto['foto']) : 'https://picsum.photos/300/220?random=' . $produto['id_estoque'] ?>" 
                         onerror="this.onerror=null; this.src='https://picsum.photos/300/220?random=<?= $produto['id_estoque'] ?>';" 
                         alt="Foto do produto">
                </div>
                <h1><?= htmlspecialchars($produto['nome']) ?></h1>
                <h3>Código: <?= htmlspecialchars($produto['codigo']) ?></h3>
            </div>
            
            <div class="produto-col-right">
                <h3>Unidade Original: <?= htmlspecialchars($produto['uni_natal']) ?></h3>
                <p><strong>Descrição:</strong> <?= htmlspecialchars($produto['descricao']) ?></p>
                <p><strong>Estoque disponível:</strong> <?= htmlspecialchars($produto['quant']) ?></p>
            </div>
        </div>

        <form action="acao_carrinho.php" method="POST" class="produto-acoes-form">
            <input type="hidden" name="id_produto" value="<?= $produto['id_estoque'] ?>">
            
            <button type="submit" class="btn-primary">Adicionar ao Carrinho</button>
            
            <div class="seletor-qtd">
                <button type="button" onclick="mudarQtd(-1)">-</button>
                <input type="number" id="quantidade_tela" name="quantidade" value="1" min="1" max="<?= $produto['quant'] ?>" readonly>
                <button type="button" onclick="mudarQtd(1)">+</button>
            </div>
        </form>
    </div>

    <script>
    function mudarQtd(valor) {
        var campo = document.getElementById('quantidade_tela');
        var valorAtual = parseInt(campo.value) || 1;
        var valorNovo = valorAtual + valor;
        var min = parseInt(campo.getAttribute('min')) || 1;
        var max = parseInt(campo.getAttribute('max')) || 1;

        if (valorNovo >= min && valorNovo <= max) {
            campo.value = valorNovo;
        }
    }
    </script>
</body>
</html>