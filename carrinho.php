<?php
session_start();

require_once 'api.php';

if (!isset($_SESSION['logado'])) { header("Location: index.php"); exit(); }

$produtos_carrinho = chamarAPI('/carrinho', 'GET');

// --- CAMADA DE PROTEÇÃO ---
if (isset($produtos_carrinho['erro'])) {
    $_SESSION['erro_pedido'] = $produtos_carrinho['erro'];
    $produtos_carrinho = []; 
} 
else if (!is_array($produtos_carrinho)) {
    $produtos_carrinho = [];
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Carrinho</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/logo_menor.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php include 'inc/sidebar.php'; ?>

  <div class="main-content">
        <h1 style="color: #1a4b9f; margin-bottom: 20px;">Carrinho</h1>
        
        <?php 
        if(isset($_GET['msg'])) {
            if ($_GET['msg'] == 'sucesso') {
                echo "<h2 style='color: #ffffff; background-color: rgba(26, 75, 159, 0.6); padding: 12px 25px; border-radius: 15px; margin-bottom: 25px; text-align: center; font-size: 18px;'>Pedido solicitado com sucesso! Aguarde autorização.</h2>";
                
                if (isset($_SESSION['codigo_pedido'])) {
                    echo "<p>O código do seu pedido é: <strong>" . htmlspecialchars($_SESSION['codigo_pedido']) . "</strong></p>";
                    unset($_SESSION['codigo_pedido']);
                }
            }
            if($_GET['msg'] == 'vazio') echo "<h2 style='color: #ffffff; background-color: rgba(239, 94, 49, 0.7); padding: 12px 25px; border-radius: 15px; margin-bottom: 25px; text-align: center; font-size: 18px;'>Erro. Selecione pelo menos um produto para fazer o pedido.</h2>";
            if($_GET['msg'] == 'erro') {
                echo "<h2 style='color: #ffffff; background-color: #ef5e31; padding: 12px 25px; border-radius: 15px; margin-bottom: 25px; text-align: center; font-size: 18px;'>Erro no Banco: " . htmlspecialchars($_SESSION['erro_pedido'] ?? 'Erro desconhecido') . "</h2>";
                unset($_SESSION['erro_pedido']);
            }
        }
        ?>

        <?php if(empty($produtos_carrinho)): ?>
            <h2 style="color: #333;">Nenhum item por aqui. Continue navegando para encontrar o que precisa.</h2>
        <?php else: ?>
            <form action="tela_pedido.php" method="POST">
                
                <?php foreach ($produtos_carrinho as $item): ?>
                    <?php 
                        // Tratamento blindado para buscar as chaves corretas e evitar quebra do HTML
                        $nome_produto = $item['produto'] ?? ($item['nome'] ?? 'Produto sem nome');
                        $foto_produto = !empty($item['foto']) ? $item['foto'] : 'img/logo.png';
                        $qtd_selecionada = $item['quantidade'] ?? 1;
                        $estoque_max = $item['estoque_max'] ?? 1;
                    ?>
                    <div class="cart-item-novo" style="display: flex; flex-wrap: wrap; align-items: center; gap: 20px; padding: 20px; margin-bottom: 15px; background: #ebf2ff; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;">
                        
                        <input type="checkbox" name="produtos_selecionados[]" value="<?= htmlspecialchars($nome_produto) ?>" class="cart-checkbox" checked style="transform: scale(1.3); cursor: pointer;">
                        
                        <img src="<?= htmlspecialchars($foto_produto) ?>" 
                             onerror="this.onerror=null; this.src='img/logo.png';" 
                             alt="Foto" class="cart-img" style="width: 90px; height: 90px; object-fit: cover; border-radius: 10px; border: 1px solid #eaeaea;">
                        
                        <div class="cart-info" style="flex: 1; min-width: 220px;">
                            <h2 style="margin: 0 0 5px 0; font-size: 22px; color: #1a4b9f; font-weight: bold;"><?= htmlspecialchars($nome_produto) ?></h2>
                            
                            <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                                <span style="font-weight: bold; color: #444; font-size: 15px;">Qtd:</span>
                                <input type="number" name="quantidades[<?= htmlspecialchars($nome_produto) ?>]" 
                                       value="<?= htmlspecialchars($qtd_selecionada) ?>" 
                                       min="1" 
                                       max="<?= htmlspecialchars($estoque_max) ?>" 
                                       style="width: 80px; padding: 8px 12px; border-radius: 8px; border: 1px solid #005be3; font-size: 16px; font-weight: bold; text-align: center; color: #333; background-color: #f9fafb; outline: none; transition: all 0.3s ease;"
                                       onfocus="this.style.borderColor='#1a4b9f'; this.style.boxShadow='0 0 0 3px rgba(26, 75, 159, 0.2)';" 
                                       onblur="this.style.borderColor='#065ada'; this.style.boxShadow='none';">
                            </div>
                        </div>
                        
                        <a href="remover_carrinho.php?nome=<?= urlencode($nome_produto) ?>" class="btn-deletar" style="background-color: #ef5e31; color: #ffffff; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: bold; transition: background 0.3s; white-space: nowrap; box-shadow: 0 4px 6px rgba(239, 94, 49, 0.2);">Remover</a>
                    </div>
                <?php endforeach; ?>
                
                <div class="cart-footer">
                    <button type="submit" class="btn-finalizar-pedido" style="margin-top: 15px;">Avançar para o Pedido</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>