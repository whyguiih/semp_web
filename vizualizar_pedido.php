<?php

session_start();
require_once 'api.php';


// Passa a unidade na URL da API para trazer apenas os pedidos corretos
// Seu código PHP que lista os cards continua exatamente igual...
// Traz TODOS os pedidos da unidade, para poder exibir aprovados e recusados
$pedidosPendentes = chamarAPI('/pedidos?unidade=' . urlencode($_SESSION['unidade']) . '&nivel=' . urlencode($_SESSION['nivel_conta']), 'GET');

if (!is_array($pedidosPendentes) || isset($pedidosPendentes['erro']) || isset($pedidosPendentes['mensagem'])) {
    $pedidosPendentes = [];
}


?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Visualizar Pedidos</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/logo_menor.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php
        include 'inc/sidebar.php';
    ?>

    <div class="main-content">
        <h1 style="color: #1a4b9f; margin-bottom: 25px; text-align: center; font-size: 32px;">Pedidos aguardando autorização</h1>
        
        <?php if(isset($_GET['msg'])) echo "<h2 style='color: #ffffff; background-color: rgba(26, 75, 159, 0.6); padding: 12px 25px; border-radius: 15px; margin-bottom: 25px; text-align: center; font-size: 18px;'>Estado atualizado com sucesso!</h2>"; ?>

        <?php if(empty($pedidosPendentes)): ?>
            <h2 style="color: #333; text-align: center; margin-top: 50px;">Nenhum pedido pendente.</h2>
        <?php else: ?>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; width: 100%;">
                
                <?php foreach ($pedidosPendentes as $pedido): ?>
                    
                    <div style="background-color: #f8faff; border-top: 5px solid #e06c00; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); display: flex; flex-direction: column; justify-content: space-between;">
                        
                        <div>
                            <h2 style="color: #1a4b9f; margin: 0 0 15px 0; font-size: 20px; border-bottom: 1px solid rgba(26, 75, 159, 0.2); padding-bottom: 10px;">
                                Pedido: <?= htmlspecialchars($pedido['nome'] ?? 'Desconhecido') ?>
                            </h2>
                            
                            <p style="margin: 8px 0; font-size: 15px; color: #444;">
                                <strong style="color: #222;">Produto:</strong> <?= htmlspecialchars($pedido['nome_produto'] ?? '') ?>
                            </p>
                            <p style="margin: 8px 0; font-size: 15px; color: #444;">
                                <strong style="color: #222;">Quantidade:</strong> <?= htmlspecialchars($pedido['quant'] ?? '') ?>
                            </p>
                            <p style="margin: 8px 0; font-size: 15px; color: #444;">
                                <strong style="color: #222;">Destino:</strong> <?= htmlspecialchars($pedido['destinatario'] ?? '') ?>
                            </p>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Código do pedido:</strong> <?= ucfirst(htmlspecialchars($pedido['codigo_pedido'] ?? '')) ?></p>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Prioridade:</strong> <?= ucfirst(htmlspecialchars($pedido['prioridade'])) ?></p>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Motivo:</strong> <?= ucfirst(htmlspecialchars($pedido['motivo'])) ?></p>
                        </div>
                        
                        <?php 
    $corStatus = "#e06c00"; $textoStatus = " Pendente";
    if (isset($pedido['aprovacao'])) {
        if ($pedido['aprovacao'] == 1) { $corStatus = "#27ae60"; $textoStatus = " Aprovado"; }
        elseif ($pedido['aprovacao'] == 2) { $corStatus = "#ef5e31"; $textoStatus = " Recusado"; }
    }
?>
<p style="margin: 10px 0 0 0; font-size: 16px; font-weight: bold; color: <?= $corStatus ?>; background-color: rgba(0,0,0,0.05); padding: 8px; border-radius: 8px; display: inline-block;">
    Status: <?= $textoStatus ?>
</p>
                    </div>
                    <?php endforeach; ?>
                
            </div>

        <?php endif; ?>


        <script>
document.addEventListener("DOMContentLoaded", function() {
    const totalAqui = <?= count($pedidosPendentes) ?>;
    const usuarioAtual = "<?= $_SESSION['usuario'] ?>";
    
    // Salva informando que ESTE usuário já viu
    localStorage.setItem('pedidos_vistos_' + usuarioAtual, totalAqui);
});
</script>

</body>
</html>