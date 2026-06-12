<?php

session_start();
require_once 'api.php';

if (!isset($_SESSION['logado']) || $_SESSION['nivel_conta'] == '0') { header("Location: estoque.php"); exit(); }

if (isset($_GET['acao']) && isset($_GET['id_emprestimo'])) {
    $novoStatus = ($_GET['acao'] == 'aceitar') ? 1 : 2; 
    
    chamarAPI('/pedidos/autorizar', 'POST', [
        'id_emprestimo' => $_GET['id_emprestimo'], 
        'novoStatus' => $novoStatus
    ]);
    header("Location: autorizar_pedidos.php?msg=Atualizado");
    exit();
}

// Passa a unidade na URL da API para trazer apenas os pedidos corretos
$pedidosPendentes = chamarAPI('/pedidos/pendentes?unidade=' . urlencode($_SESSION['unidade']), 'GET');

// Verificação de segurança (Impede o erro de Falso Positivo)
if (!is_array($pedidosPendentes) || isset($pedidosPendentes['erro']) || isset($pedidosPendentes['mensagem'])) {
    $pedidosPendentes = [];
}


?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Autorizar Pedidos</title>
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
            
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <?php foreach ($pedidosPendentes as $pedido): ?>
                    
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; padding: 25px; background-color: rgba(255, 255, 255, 0.6); border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); gap: 20px;">
                        
                        <div style="flex: 1; min-width: 250px;">
                            <h2 style="color: #1a4b9f; margin: 0 0 10px 0; font-size: 24px;">Pedido de: <?= htmlspecialchars($pedido['nome'] ?? 'Desconhecido') ?></h2>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Produto(s):</strong> <?= htmlspecialchars($pedido['nome_produto']) ?></p>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Quantidade:</strong> <?= htmlspecialchars($pedido['quant']) ?></p>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Destino:</strong> <?= htmlspecialchars($pedido['destinatario']) ?></p>
                               <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Prioridade:</strong> <?= ucfirst(htmlspecialchars($pedido['prioridade'])) ?></p>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Motivo:</strong> <?= ucfirst(htmlspecialchars($pedido['motivo'])) ?></p>
                        </div>
                        
                        <div style="display: flex; gap: 15px; flex-wrap: wrap; width: auto;">
                            <a href="autorizar_pedidos.php?acao=aceitar&id_emprestimo=<?= $pedido['id_emprestimo'] ?>" class="btn-primary" style="text-decoration:none; font-size:18px; padding:12px 30px; border-radius: 15px; text-align: center; flex: 1; min-width: 120px;">Liberar</a>
                            
                            <a href="autorizar_pedidos.php?acao=recusar&id_emprestimo=<?= $pedido['id_emprestimo'] ?>" class="btn-primary btn-danger" style="text-decoration:none; font-size:18px; padding:12px 30px; border-radius: 15px; background-color:#ef5e31; text-align: center; flex: 1; min-width: 120px;">Recusar</a>
                        </div>

                    </div>

                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
</body>
</html>