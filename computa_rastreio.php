<?php
session_start();
require_once 'api.php';

// Segurança: Permite apenas Nível 2
if (!isset($_SESSION['logado']) || $_SESSION['nivel_conta'] != '2') { 
    header("Location: index.php"); 
    exit(); 
}

// Busca TODOS os rastreios da API
$todos_rastreios = chamarAPI('/rastreio/todos', 'GET');

if (!is_array($todos_rastreios) || isset($todos_rastreios['erro'])) {
    $todos_rastreios = [];
}

// Pega a data de hoje no fuso horário exato do Brasil
date_default_timezone_set('America/Sao_Paulo');
$hoje = date('Y-m-d');

// Pega a unidade de quem está acessando
$minha_unidade = $_SESSION['unidade'];

// =========================================================
// MÁGICA 1: AGRUPAR O HISTÓRICO PARA EVITAR DUPLICATAS
// =========================================================
$pacotes = [];
foreach ($todos_rastreios as $rastreio) {
    $codigo = $rastreio['codigo'];
    if (!isset($pacotes[$codigo])) {
        $pacotes[$codigo] = [];
    }
    $pacotes[$codigo][] = $rastreio;
}

$saidas_hoje = [];
$chegadas_hoje = [];

foreach ($pacotes as $codigo => $historico) {
    // Pega APENAS a última viagem registrada para esse pacote
    $viagemAtual = end($historico);

    // Se o pacote tem mais de um registro e a origem é igual ao destino, ele retornou.
    // Como o ciclo finalizou, nós o RETIRAMOS da lista ignorando ele (continue).
    if (count($historico) > 1 && strcasecmp($viagemAtual['unidade_original'], $viagemAtual['unidade_destino']) == 0) {
        continue; 
    }

    // TABELA 1: É saída hoje E a minha unidade é a de origem?
    if ($viagemAtual['data_saida'] === $hoje && strcasecmp($viagemAtual['unidade_original'], $minha_unidade) == 0) {
        $saidas_hoje[] = $viagemAtual;
    }
    
    // TABELA 2: É entrada hoje E a minha unidade é o destino?
    if ($viagemAtual['data_entrada'] === $hoje && strcasecmp($viagemAtual['unidade_destino'], $minha_unidade) == 0) {
        $chegadas_hoje[] = $viagemAtual;
    }
}
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Movimentações do Dia</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/logo_menor.png" type="image/png">
</head>
<body>
    <?php include 'inc/sidebar.php'; ?>

    <div class="main-content">
        <h1 style="color: #1a4b9f; margin-bottom: 20px;">Movimentações de Hoje - Unidade <?= htmlspecialchars(ucfirst($minha_unidade)) ?></h1>

        <div class="cadastro-container" style="padding: 30px; max-width: 900px; width: 100%;">
            
            <h2 style="color: #8e44ad; font-size: 22px; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px; text-align: left;">
                📦 Encomendas saindo hoje da unidade
            </h2>
            
            <?php if (empty($saidas_hoje)): ?>
                <p style="color: #666; font-size: 16px; margin-bottom: 40px;">Nenhum pacote programado para sair hoje.</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; text-align: left; margin-bottom: 40px;">
                    <thead>
                        <tr style="background-color: #f4f7f6; color: #1a4b9f;">
                            <th style="padding: 12px; border-bottom: 2px solid #ddd;">Código do Pedido</th>
                            <th style="padding: 12px; border-bottom: 2px solid #ddd;">Indo para: (Destino)</th>
                            <th style="padding: 12px; border-bottom: 2px solid #ddd;">Previsão de Chegada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($saidas_hoje as $rastreio): ?>
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #eee; font-size: 18px;"><strong><?= htmlspecialchars($rastreio['codigo']) ?></strong></td>
                                <td style="padding: 12px; border-bottom: 1px solid #eee; text-transform: capitalize;"><?= htmlspecialchars($rastreio['unidade_destino']) ?></td>
                                
                                <td style="padding: 12px; border-bottom: 1px solid #eee;">
                                    <?= !empty($rastreio['data_entrada']) ? date('d/m/Y', strtotime($rastreio['data_entrada'])) : '<span style="color:#999;">Pendente</span>' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h2 style="color: #005c97; font-size: 22px; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px; text-align: left;">
                🚚 Encomendas chegando hoje na unidade
            </h2>
            
            <?php if (empty($chegadas_hoje)): ?>
                <p style="color: #666; font-size: 16px;">Nenhum pacote programado para chegar hoje.</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background-color: #f4f7f6; color: #1a4b9f;">
                            <th style="padding: 12px; border-bottom: 2px solid #ddd;">Código do Pedido</th>
                            <th style="padding: 12px; border-bottom: 2px solid #ddd;">Vindo de: (Origem)</th>
                            <th style="padding: 12px; border-bottom: 2px solid #ddd;">Data de Saída</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chegadas_hoje as $rastreio): ?>
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #eee; font-size: 18px;"><strong><?= htmlspecialchars($rastreio['codigo']) ?></strong></td>
                                <td style="padding: 12px; border-bottom: 1px solid #eee; text-transform: capitalize;"><?= htmlspecialchars($rastreio['unidade_original']) ?></td>
                                
                                <td style="padding: 12px; border-bottom: 1px solid #eee;">
                                    <?= !empty($rastreio['data_saida']) ? date('d/m/Y', strtotime($rastreio['data_saida'])) : '<span style="color:#999;">Em branco</span>' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>