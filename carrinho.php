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
            if ($_GET['msg'] == 'sucesso' && isset($_SESSION['codigo_pedido'])) {
                $codigo_gerado = htmlspecialchars($_SESSION['codigo_pedido']);
                echo "
                <div id='toast-sucesso' style='position: fixed; top: 25px; right: 25px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 20px 25px; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); z-index: 10000; border-left: 6px solid #ef5e31; animation: slideIn 0.5s cubic-bezier(0.25, 0.8, 0.25, 1), fadeOut 0.5s ease-out 10s forwards; min-width: 320px;'>
                    <div style='display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;'>
                        <strong style='font-size: 18px; color: #1a4b9f;'>🎉 Pedido Concluído!</strong>
                        <button onclick=\"document.getElementById('toast-sucesso').style.display='none'\" style='background: none; border: none; color: #999; font-size: 24px; cursor: pointer; transition: 0.2s;' onmouseover=\"this.style.color='#ef5e31'\" onmouseout=\"this.style.color='#999'\">&times;</button>
                    </div>
                    <p style='margin: 0; font-size: 15px; color: #555;'>Guarde o código do seu pedido:</p>
                    <p style='margin: 8px 0 0 0; font-size: 24px; font-weight: bold; color: #ef5e31; letter-spacing: 1px; user-select: all;'>{$codigo_gerado}</p>
                </div>
                <style>
                    @keyframes slideIn { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
                    @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; visibility: hidden; } }
                </style>
                ";
                unset($_SESSION['codigo_pedido']); // Limpa a sessão após exibir
            }
            else if ($_GET['msg'] == 'sucesso') {
                echo "<h2 style='color: #ffffff; background-color: rgba(26, 75, 159, 0.6); padding: 12px 25px; border-radius: 15px; margin-bottom: 25px; text-align: center; font-size: 18px;'>Pedido solicitado com sucesso! Aguarde autorização.</h2>";
            }
            else if($_GET['msg'] == 'vazio') {
                echo "<h2 style='color: #ffffff; background-color: rgba(239, 94, 49, 0.7); padding: 12px 25px; border-radius: 15px; margin-bottom: 25px; text-align: center; font-size: 18px;'>Erro. Selecione pelo menos um produto para fazer o pedido.</h2>";
            }
            else if($_GET['msg'] == 'erro') {
                echo "<h2 style='color: #ffffff; background-color: #ef5e31; padding: 12px 25px; border-radius: 15px; margin-bottom: 25px; text-align: center; font-size: 18px;'>Erro no Banco: " . htmlspecialchars($_SESSION['erro_pedido'] ?? 'Erro desconhecido') . "</h2>";
                unset($_SESSION['erro_pedido']);
            }
        }
        ?>

        <?php if(empty($produtos_carrinho)): ?>
            <h2 style="color: #333;">Nenhum item por aqui. Continue navegando para encontrar o que precisa.</h2>
        <?php else: ?>
            <!-- FORMULÁRIO COM FLEXBOX E ALTURA MÍNIMA -->
            <form action="tela_pedido.php" method="POST" style="display: flex; flex-direction: column; min-height: calc(100vh - 180px);">
                
                <!-- CONTAINER EM GRID (Imitando a tela inicial em colunas) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px; margin-bottom: 25px;">
                    
                    <?php foreach ($produtos_carrinho as $item): ?>
                        <?php 
                            // Tratamento blindado para buscar as chaves corretas e evitar quebra do HTML
                            $nome_produto = $item['produto'] ?? ($item['nome'] ?? 'Produto sem nome');
                            $foto_produto = !empty($item['foto']) ? $item['foto'] : 'img/logo.png';
                            $qtd_selecionada = $item['quantidade'] ?? 1;
                            $estoque_max = $item['estoque_max'] ?? 1;
                        ?>
                        
                        <!-- CARD DO PRODUTO -->
                        <div class="cart-item-novo" style="display: flex; flex-direction: column; align-items: center; padding: 25px; background: #ebf2ff; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #f0f0f0; position: relative;">
                            
                            <!-- Checkbox posicionado no canto superior direito do card -->
                            <div style="position: absolute; top: 15px; right: 15px;">
                                <input type="checkbox" name="produtos_selecionados[]" value="<?= htmlspecialchars($nome_produto) ?>" class="cart-checkbox" checked style="transform: scale(1.4); cursor: pointer;">
                            </div>
                            
                            <!-- Foto -->
                            <img src="<?= htmlspecialchars($foto_produto) ?>" 
                                 onerror="this.onerror=null; this.src='img/logo.png';" 
                                 alt="Foto" class="cart-img" style="width: 130px; height: 130px; object-fit: cover; border-radius: 10px; border: 1px solid #eaeaea; margin-bottom: 15px; background-color: #fff;">
                            
                            <!-- Informações do Produto -->
                            <div class="cart-info" style="width: 100%; display: flex; flex-direction: column; align-items: center; text-align: center; flex: 1;">
                                <h2 style="margin: 0 0 15px 0; font-size: 20px; color: #1a4b9f; font-weight: bold; line-height: 1.2;"><?= htmlspecialchars($nome_produto) ?></h2>
                                
                                <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 20px; width: 100%;">
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
                            
                            <!-- Botão Remover centralizado na base do card -->
                            <a href="remover_carrinho.php?nome=<?= urlencode($nome_produto) ?>" class="btn-deletar" style="background-color: #ef5e31; color: #ffffff; padding: 12px; border-radius: 10px; text-decoration: none; font-weight: bold; transition: background 0.3s; text-align: center; width: 100%; box-shadow: 0 4px 6px rgba(239, 94, 49, 0.2); box-sizing: border-box;">Remover</a>
                        </div>
                    <?php endforeach; ?>
                    
                </div>
                
                <!-- BOTÃO DE FINALIZAR PEDIDO NO FINAL DA TELA -->
                <div class="cart-footer" style="display: flex; justify-content: center; margin-top: auto; padding-bottom: 20px;">
                    <button type="submit" class="btn-finalizar-pedido" style="padding: 15px 40px; font-size: 18px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); cursor: pointer;">Avançar para o Pedido</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>