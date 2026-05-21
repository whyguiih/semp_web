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
        
        <div class="sidebar-bottom">
            <a href="logout.php"><img src="img/sair.png" alt="Sair"></a>
        </div>
    </div>

  <div class="main-content">
        <h1 style="color: #1a4b9f; margin-bottom: 20px;">O teu Carrinho</h1>
        
        <?php 
        if(isset($_GET['msg'])) {
            if($_GET['msg'] == 'sucesso') echo "<h2 style='color: #ffffff; background-color: rgba(26, 75, 159, 0.6); padding: 12px 25px; border-radius: 15px; margin-bottom: 25px; text-align: center; font-size: 18px;'>Pedido solicitado com sucesso! A aguardar autorização.</h2>";
            if($_GET['msg'] == 'vazio') echo "<h2 style='color: #ffffff; background-color: rgba(239, 94, 49, 0.7); padding: 12px 25px; border-radius: 15px; margin-bottom: 25px; text-align: center; font-size: 18px;'>Erro: Selecione pelo menos um produto para fazer o pedido.</h2>";
        }
        ?>

        <?php if(empty($produtos_carrinho)): ?>
            <h2 style="color: #333;">O teu carrinho está vazio.</h2>
        <?php else: ?>
            <form action="tela_pedido.php" method="POST">
                
                <?php foreach ($produtos_carrinho as $item): ?>
                    <div class="cart-item-novo">
                        <input type="checkbox" name="produtos_selecionados[]" value="<?= htmlspecialchars($item['id_estoque'] ?? '') ?>" class="cart-checkbox" checked>
                        
                        <img src="<?= !empty($item['foto']) ? htmlspecialchars($item['foto']) : 'https://picsum.photos/100/100?random=' . ($item['id_estoque'] ?? rand()) ?>" 
                             onerror="this.onerror=null; this.src='https://picsum.photos/100/100?random=<?= $item['id_estoque'] ?? rand() ?>';" 
                             alt="Foto" class="cart-img">
                        
                        <div class="cart-info">
                            <h2><?= htmlspecialchars($item['nome']) ?></h2>
                            <p>Cód: <?= htmlspecialchars($item['codigo']) ?> | Qtd Desejada: <?= htmlspecialchars($item['quantidade'] ?? 1) ?></p>
                        </div>
                        <a href="remover_carrinho.php?id=<?= htmlspecialchars($item['id_estoque'] ?? '') ?>" class="btn-deletar">Remover</a>
                    </div>
                <?php endforeach; ?>
                
                <div class="cart-footer">
                    <button type="submit" class="btn-finalizar-pedido">Avançar para o Pedido</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>