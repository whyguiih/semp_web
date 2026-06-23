<?php
session_start();
require_once 'api.php';

if (!isset($_SESSION['logado']) || $_SESSION['nivel_conta'] == '0') { header("Location: estoque.php"); exit(); }

if (isset($_GET['acao']) && isset($_GET['id_emprestimo'])) {
    // Se a ação for ciente, mudamos o status para 4 (Deletado do painel ativo/Finalizado)
    if ($_GET['acao'] == 'ciente') {
        $novoStatus = 4;
    } else {
        $novoStatus = ($_GET['acao'] == 'aceitar') ? 1 : 2; 
    }
    
    chamarAPI('/pedidos/autorizar', 'POST', [
        'id_emprestimo' => $_GET['id_emprestimo'], 
        'novoStatus' => $novoStatus
    ]);
    header("Location: autorizar_pedidos.php?msg=Atualizado");
    exit();
}

// Passa a unidade para buscar tanto os normais quanto as solicitações de retorno
$pedidosPendentes = chamarAPI('/pedidos/pendentes?unidade=' . urlencode($_SESSION['unidade']), 'GET');

if (!is_array($pedidosPendentes) || isset($pedidosPendentes['erro']) || isset($pedidosPendentes['mensagem'])) {
    $pedidosPendentes = [];
}

// Separa os arrays dinamicamente de acordo com o status
$pedidosNormais = [];
$pedidosRetorno = [];

foreach ($pedidosPendentes as $pedido) {
    if (isset($pedido['aprovacao']) && $pedido['aprovacao'] == 3) {
        $pedidosRetorno[] = $pedido;
    } else {
        $pedidosNormais[] = $pedido;
    }
}

// --- LÓGICA DE ORDENAÇÃO POR PRIORIDADE (Apenas para Pedidos Normais) ---
function obterPesoPrioridade($prioridade) {
    $p = strtolower(trim($prioridade ?? ''));
    if ($p === 'alto') return 1;
    if ($p === 'intermediário' || $p === 'intermediario' || $p === 'médio' || $p === 'medio') return 2;
    if ($p === 'baixo') return 3;
    return 4;
}

usort($pedidosNormais, function($a, $b) {
    return obterPesoPrioridade($a['prioridade'] ?? '') <=> obterPesoPrioridade($b['prioridade'] ?? '');
});
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
    <?php include 'inc/sidebar.php'; ?>

    <div class="main-content">
        <h1 style="color: #1a4b9f; margin-bottom: 25px; text-align: center; font-size: 32px;">Painel de Gerenciamento e Autorizações</h1>
        
        <?php if(isset($_GET['msg'])) echo "<h2 style='color: #ffffff; background-color: rgba(26, 75, 159, 0.6); padding: 12px 25px; border-radius: 15px; margin-bottom: 25px; text-align: center; font-size: 18px;'>Estado atualizado com sucesso!</h2>"; ?>

        <!-- ================= BLOCO 1: SOLICITAÇÕES DE RETORNO ================= -->
        <h2 style="color: #1a4b9f; font-size: 22px; margin-bottom: 15px; border-bottom: 2px solid #1a4b9f; padding-bottom: 8px; text-align: left;">
            Devoluções Exigidas pelas Unidades Natais
        </h2>
        
        <?php if(empty($pedidosRetorno)): ?>
            <p style="color: #666; font-style: italic; margin-bottom: 35px;">Nenhuma solicitação de retorno ativa no momento.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 45px;">
                <?php foreach ($pedidosRetorno as $pedido): ?>
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; padding: 25px; background-color: rgba(255, 255, 255, 0.7); border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); gap: 20px; border-left: 6px solid #1a4b9f;">
                        <div style="flex: 1; min-width: 250px;">
                            <!-- AQUI ESTÁ A ALTERAÇÃO -->
                            <h2 style="color: #1a4b9f; margin: 0 0 10px 0; font-size: 22px;">A unidade <?= htmlspecialchars($pedido['unidade_natal'] ?? 'Origem') ?> pediu o item de volta!</h2>
                            
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Produto(s):</strong> <?= htmlspecialchars($pedido['nome_produto']) ?></p>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Quantidade emprestada:</strong> <?= htmlspecialchars($pedido['quant']) ?></p>
                            
                            <p style="margin: 8px 0 0 0; font-size: 16px; color: white; background-color: #ef5e31; display: inline-block; padding: 4px 12px; border-radius: 8px; font-weight: bold;">
                                📅 Deve estar de volta até: <?= !empty($pedido['data_reserva']) ? date('d/m/Y', strtotime($pedido['data_reserva'])) : 'Imediato' ?>
                            </p>
                        </div>
                        <div style="display: flex; width: auto;">
                            <a href="autorizar_pedidos.php?acao=ciente&id_emprestimo=<?= $pedido['id_emprestimo'] ?>" class="btn-primary" style="text-decoration:none; font-size:18px; padding:12px 40px; border-radius: 15px; text-align: center; background-color: #1a4b9f; color: white; font-weight: bold; box-shadow: 0 4px 6px rgba(26, 75, 159, 0.2);">
                                Ciente
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ================= BLOCO 2: PEDIDOS TRADICIONAIS ================= -->
        <h2 style="color: #1a4b9f; font-size: 22px; margin-bottom: 15px; border-bottom: 2px solid rgba(26, 75, 159, 0.3); padding-bottom: 8px; text-align: left;">
            📦 Novos Pedidos de Empréstimo Pendentes
        </h2>

        <?php if(empty($pedidosNormais)): ?>
            <p style="color: #666; font-style: italic;">Nenhum pedido de empréstimo aguardando revisão.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <?php foreach ($pedidosNormais as $pedido): ?>
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