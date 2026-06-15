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

// ALTERAÇÃO: Buscamos a rota genérica /pedidos para trazer tanto os pendentes quanto os já confirmados
$todosPedidos = chamarAPI('/pedidos?unidade=' . urlencode($_SESSION['unidade']) . '&nivel=' . urlencode($_SESSION['nivel_conta']), 'GET');

if (!is_array($todosPedidos) || isset($todosPedidos['erro']) || isset($todosPedidos['mensagem'])) {
    $todosPedidos = [];
}

// Separando os dados em dois arrays baseados no status de aprovação
$pedidosPendentes = [];
$pedidosConfirmados = [];

foreach ($todosPedidos as $pedido) {
    if (isset($pedido['processamento']) && $pedido['processamento'] == 1) {
        if ($pedido['aprovacao'] == 0) {
            $pedidosPendentes[] = $pedido;
        } elseif ($pedido['aprovacao'] == 1) {
            $pedidosConfirmados[] = $pedido;
        }
    }
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
        <h1 style="color: #1a4b9f; margin-bottom: 25px; text-align: center; font-size: 32px;">Painel de Gerenciamento de Pedidos</h1>
        
        <?php if(isset($_GET['msg'])) echo "<h2 style='color: #ffffff; background-color: rgba(26, 75, 159, 0.6); padding: 12px 25px; border-radius: 15px; margin-bottom: 25px; text-align: center; font-size: 18px;'>Estado atualizado com sucesso!</h2>"; ?>

        <h2 style="color: #e06c00; margin-top: 30px; margin-bottom: 15px; font-size: 22px; border-bottom: 2px solid #e06c00; padding-bottom: 5px;">Pedidos aguardando autorização</h2>
        
        <?php if(empty($pedidosPendentes)): ?>
            <p style="color: #666; font-style: italic; margin-bottom: 30px;">Nenhum pedido aguardando aprovação.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 40px;">
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


        <h2 style="color: #2852fd; margin-top: 30px; margin-bottom: 15px; font-size: 22px; border-bottom: 2px solid #2734ae; padding-bottom: 5px;">Pedidos confirmados (Para preparação)</h2>
        
        <?php if(empty($pedidosConfirmados)): ?>
            <p style="color: #666; font-style: italic;">Nenhum pedido confirmado em aberto.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <?php foreach ($pedidosConfirmados as $pedido): ?>
                    
                    <div id="card-pedido-<?= $pedido['id_emprestimo'] ?>" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; padding: 25px; background-color: rgba(235, 247, 235, 0.8); border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); gap: 20px;">
                        <div style="flex: 1; min-width: 250px;">
                            <h2 style="color: #2746ae; margin: 0 0 10px 0; font-size: 24px;">Pedido de: <?= htmlspecialchars($pedido['nome'] ?? 'Desconhecido') ?></h2>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Produto(s):</strong> <?= htmlspecialchars($pedido['nome_produto']) ?></p>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Quantidade:</strong> <?= htmlspecialchars($pedido['quant']) ?></p>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Destino:</strong> <?= htmlspecialchars($pedido['destinatario']) ?></p>
                        </div>
                        <div style="display: flex; gap: 15px; flex-wrap: wrap; width: auto;">
                            <button onclick="removerDaTela(<?= $pedido['id_emprestimo'] ?>)" class="btn-primary" style="border:none; cursor:pointer; font-size:16px; padding:10px 20px; border-radius: 12px; background-color:#555; text-align: center;">Remover da Tela</button>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const totalAqui = <?= count($pedidosPendentes) ?>;
        const usuarioAtual = "<?= $_SESSION['usuario'] ?>";
        
        // Salva informando que ESTE usuário já viu as notificações pendentes
        localStorage.setItem('pedidos_vistos_' + usuarioAtual, totalAqui);

        // Oculta os cartões confirmados que o usuário removeu da tela anteriormente
        const chaveOcultos = 'pedidos_ocultos_' + usuarioAtual;
        let ocultos = JSON.parse(localStorage.getItem(chaveOcultos)) || [];
        ocultos.forEach(id => {
            const card = document.getElementById('card-pedido-' + id);
            if (card) { card.style.display = 'none'; }
        });
    });

    // Função JS para ocultar na hora sem recarregar e sem mexer no banco de dados
    function removerDaTela(id) {
        const usuarioAtual = "<?= $_SESSION['usuario'] ?>";
        const chaveOcultos = 'pedidos_ocultos_' + usuarioAtual;
        let ocultos = JSON.parse(localStorage.getItem(chaveOcultos)) || [];
        
        if (!ocultos.includes(id)) {
            ocultos.push(id);
            localStorage.setItem(chaveOcultos, JSON.stringify(ocultos));
        }
        
        const card = document.getElementById('card-pedido-' + id);
        if (card) {
            card.style.setProperty('display', 'none', 'important');
        }
    }
    </script>
</body>
</html>