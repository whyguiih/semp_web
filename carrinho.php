<?php
session_start();

require_once 'api.php';

if (!isset($_SESSION['logado'])) { header("Location: index.php"); exit(); }

$produtos_carrinho = chamarAPI('/carrinho', 'GET');

// --- NOVA CAMADA DE PROTEÇÃO AQUI ---
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
        <h1 style="color: #1a4b9f; margin-bottom: 20px;">Carrinho Teste</h1>
        
        <?php 
        if(isset($_GET['msg'])) {
            if($_GET['msg'] == 'sucesso') echo "<h2 style='color: #ffffff; background-color: rgba(26, 75, 159, 0.6); padding: 12px 25px; border-radius: 15px; margin-bottom: 25px; text-align: center; font-size: 18px;'>Pedido solicitado com sucesso! Aguarde autorização.</h2>";
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
                    <div class="cart-item-novo" style="min-height: 120px; overflow: visible !important; display: flex; align-items: center; gap: 15px; padding: 15px; margin-bottom: 15px;">
                        
                        <input type="checkbox" name="produtos_selecionados[]" value="<?= htmlspecialchars($item['id_estoque'] ?? '') ?>" class="cart-checkbox" checked>
                        
                        <img src="<?= !empty($item['foto']) ? htmlspecialchars($item['foto']) : 'img/logo.png' ?>" 
                             onerror="this.onerror=null; this.src='img/logo.png';" 
                             alt="Foto" class="cart-img" style="width: 80px; height: 80px; object-fit: cover;">
                        
                        <div class="cart-info" style="flex: 1;">
                            <h2 style="margin: 0 0 5px 0; font-size: 20px; color: #1a4b9f;"><?= htmlspecialchars($item['nome']) ?></h2>
                            <p style="margin: 0 0 10px 0; color: #555;">Código: <?= htmlspecialchars($item['codigo']) ?></p>
                            
                            <div style="background-color: #f1f5fa; padding: 8px 12px; border-radius: 8px; display: inline-flex; align-items: center; border: 1px solid #cce0ff;">
                                <span style="font-weight: bold; color: #1a4b9f; margin-right: 10px;">Qtd Desejada:</span>
                                <input type="number" name="quantidades[<?= htmlspecialchars($item['id_estoque']) ?>]" 
                                       value="<?= htmlspecialchars($item['quantidade'] ?? 1) ?>" 
                                       min="1" 
                                       max="<?= htmlspecialchars($item['estoque_max'] ?? 999) ?>" 
                                       style="width: 80px; padding: 6px; border-radius: 5px; border: 2px solid #1a4b9f; font-size: 16px; font-weight: bold; text-align: center; color: #333;">
                            </div>
                        </div>
                        
                        <a href="remover_carrinho.php?nome=<?= urlencode($item['nome']) ?>" class="btn-deletar" style="padding: 10px 20px;">Remover</a>
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