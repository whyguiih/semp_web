<?php
session_start();

require_once 'api.php';
// ... resto do código


if (!isset($_SESSION['logado'])) { header("Location: index.php"); exit(); }

$produtos_carrinho = chamarAPI('/carrinho', 'GET');

// --- NOVA CAMADA DE PROTEÇÃO AQUI ---
// Se a API retornou um erro (ex: não achou o usuário)
if (isset($produtos_carrinho['erro'])) {
    $_SESSION['erro_pedido'] = $produtos_carrinho['erro']; // Guarda o erro para mostrar na tela
    $produtos_carrinho = []; // Esvazia a variável para o foreach não quebrar
} 
// Se a resposta não for um Array válido ou vier vazia
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
    <?php
        include 'inc/sidebar.php';
    ?>

  <div class="main-content">
        <h1 style="color: #1a4b9f; margin-bottom: 20px;">Carrinho</h1>
        
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
                    <div class="cart-item-novo">
                        <input type="checkbox" name="produtos_selecionados[]" value="<?= htmlspecialchars($item['id_estoque'] ?? '') ?>" class="cart-checkbox" checked>
                        
                        <img src="<?= !empty($item['foto']) ? htmlspecialchars($item['foto']) : 'img/logo.png' ?>" 
     onerror="this.onerror=null; this.src='img/logo.png';" 
     alt="Foto" class="cart-img">
                        
                        <div class="cart-info">
                            <h2><?= htmlspecialchars($item['nome']) ?></h2>
                            <p>Código: <?= htmlspecialchars($item['codigo']) ?> | Quantidade desejada: <?= htmlspecialchars($item['quantidade'] ?? 1) ?></p>
                        </div>
                       <a href="remover_carrinho.php?nome=<?= urlencode($item['nome']) ?>" class="btn-deletar">Remover</a>
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