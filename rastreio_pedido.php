<?php
session_start();
require_once 'api.php';

if (!isset($_SESSION['logado'])) { 
    header("Location: index.php"); 
    exit(); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST" ){

    $original = $_POST['original'] ?? $_SESSION['unidade'];
    $codigo = $_POST['codigo'] ?? '';
    $data_saida = $_POST['data_saida'] ?? '';
    $data_entrada = $_POST['data_entrada'] ?? '';
    $destino = $_POST['destino'] ?? '';

    $dados = [
        'codigo' => $codigo, 
        'unidade_original' => $original, // <-- CORREÇÃO: Agora usa o que foi digitado no formulário
        'unidade_destino' => $destino,
        'data_saida' => $data_saida,
        'data_entrada' => $data_entrada
    ];

    // Envia os dados para a API
    $resposta = chamarAPI('/pedido/rastreio', 'POST', $dados);
    
    // CORREÇÃO: Essa é a parte que você tinha apagado sem querer!
    if (is_array($resposta) && isset($resposta['erro'])) {
        $_SESSION['erro_rastreio'] = $resposta['erro'];
        header("Location: rastreio_pedido.php?msg=erro");
        exit();
    } else {
        $_SESSION['rastreio_info'] = $resposta;
        header("Location: rastreio_pedido.php?msg=sucesso");
        exit();
    }
}
?>

<!doctype html>
<head>
    <meta charset="UTF-8">
    <title>Rastreio de Pedido</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/logo_menor.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <?php include 'inc/sidebar.php'; ?>

    <div class="main-content">
        <h1 style="color: #1a4b9f; margin-bottom: 20px;">Rastreio de Pedido</h1>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'sucesso'): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                <strong>Sucesso!</strong> Rastreio do pedido iniciado.
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'erro'): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                <strong>Erro:</strong> <?= htmlspecialchars($_SESSION['erro_rastreio'] ?? 'Não foi possível salvar o rastreio.') ?>
            </div>
        <?php endif; ?>

<form method="POST" action="">
   
    <div class="cadastro-container" style="max-width: 500px; padding: 40px;">
            <h1 style="color: #1a4b9f; margin-bottom: 25px; text-align: center; font-size: 28px;">Detalhes da entrega dos pedidos</h1>

    <div class="form-group">
                    <label>Código do pedido:</label>
                    <input type="text" name="codigo" placeholder="Fh5q37gd237" required>
                </div>
                
                <div class="form-group">
                    <label>Unidade em que se encontra:</label>
                    <input type="text" name="original" value="<?= htmlspecialchars($_SESSION['unidade']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Unidade em que se destina:</label>
                    <input type="text" name="destino" placeholder="Garibaldi" required>
                </div>
                
                <div class="form-group">
                    <label>Data de saída da unidade:</label>
                    <input type="date" name="data_saida" >
                </div>

                <div class="form-group">
                    <label>Data estimada de chegada na unidade:</label>
                    <input type="date" name="data_entrada" required>
                </div>

                 <button type="submit" id="btn-confirmar" class="btn-primary" style="padding: 12px 30px; font-size: 18px; border-radius: 15px; box-shadow: 0 5px 15px rgba(26, 75, 159, 0.3);">Confirmar</button>
</div>
</form>
 </div>
        
 