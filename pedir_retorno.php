<?php
session_start();
require_once 'api.php';

// Segurança: Permite apenas Operadores (1) e Gerentes (2)
if (!isset($_SESSION['logado']) || $_SESSION['nivel_conta'] == '0') { 
    header("Location: estoque.php"); 
    exit(); 
}

// Processa a solicitação enviada via POST contendo a data escolhida
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_emprestimo']) && isset($_POST['data_retorno'])) {
    chamarAPI('/pedidos/solicitar_retorno', 'POST', [
        'id_emprestimo' => $_POST['id_emprestimo'],
        'data_retorno'  => $_POST['data_retorno']
    ]);
    
    header("Location: pedir_retorno.php?msg=Solicitado");
    exit();
}

// Busca os produtos que a sua unidade emprestou para outras (aprovados)
$produtosEmprestados = chamarAPI('/pedidos/emprestados?unidade=' . urlencode($_SESSION['unidade']), 'GET');

if (!is_array($produtosEmprestados) || isset($produtosEmprestados['erro']) || isset($produtosEmprestados['mensagem'])) {
    $produtosEmprestados = [];
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Pedir Retorno de Material</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/logo_menor.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php include 'inc/sidebar.php'; ?>

    <div class="main-content">
        <h1 style="color: #1a4b9f; margin-bottom: 25px; text-align: center; font-size: 32px;">Itens Emprestados a Outras Unidades</h1>
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'Solicitado') echo "<h2 style='color: #ffffff; background-color: rgba(26, 75, 159, 0.6); padding: 12px 25px; border-radius: 15px; margin-bottom: 25px; text-align: center; font-size: 18px;'>Retorno do item solicitado com sucesso!</h2>"; ?>

        <?php if(empty($produtosEmprestados)): ?>
            <h2 style="color: #333; text-align: center; margin-top: 50px;">Sua unidade não possui itens emprestados ativos no momento.</h2>
        <?php else: ?>
            
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <?php foreach ($produtosEmprestados as $pedido): ?>
                    
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; padding: 25px; background-color: rgba(255, 255, 255, 0.6); border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); gap: 20px; border-left: 5px solid #ef5e31;">
                        
                        <div style="flex: 1; min-width: 250px;">
                            <h2 style="color: #1a4b9f; margin: 0 0 10px 0; font-size: 24px;">Emprestado para: <?= htmlspecialchars($pedido['unidade'] ?? 'Desconhecido') ?></h2>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Produto(s):</strong> <?= htmlspecialchars($pedido['nome_produto']) ?></p>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Quantidade:</strong> <span style="background-color: #1a4b9f; color: white; padding: 2px 8px; border-radius: 5px;"><?= htmlspecialchars($pedido['quant']) ?></span></p>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Responsável original:</strong> <?= htmlspecialchars($pedido['nome'] ?? 'Não informado') ?></p>
                        </div>
                        
                        <div style="display: flex; gap: 15px; flex-wrap: wrap; width: auto; align-items: center;">
                            <div id="btn-container-<?= $pedido['id_emprestimo'] ?>">
                                <button type="button" class="btn-primary" 
                                        style="font-size:18px; padding:12px 35px; border-radius: 15px; background-color: #ef5e31; box-shadow: 0 4px 6px rgba(239, 94, 49, 0.2); border: none; color: white; cursor: pointer;"
                                        onclick="mostrarSeletorData(<?= $pedido['id_emprestimo'] ?>)">
                                    Retornar
                                </button>
                            </div>

                            <form id="form-retorno-<?= $pedido['id_emprestimo'] ?>" method="POST" style="display: none; align-items: center; gap: 12px; flex-wrap: wrap;" onsubmit="return confirm('Confirmar a solicitação de devolução deste material?');">
                                <input type="hidden" name="id_emprestimo" value="<?= $pedido['id_emprestimo'] ?>">
                                
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <label style="font-size: 14px; color: #1a4b9f; font-weight: bold; text-align: left;">Data desejada para retorno:</label>
                                    <input type="date" name="data_retorno" required style="padding: 8px 12px; border-radius: 10px; border: 2px solid #ef5e31; font-size: 15px; color: #1a4b9f; font-weight: bold; background-color: #fff; outline: none;">
                                </div>
                                
                                <div style="display: flex; gap: 8px; margin-top: 18px;">
                                    <button type="submit" class="btn-primary" style="font-size:15px; padding:10px 20px; border-radius: 10px; background-color: #1a4b9f; border: none; color: white; cursor: pointer;">
                                        Confirmar
                                    </button>
                                    <button type="button" class="btn-primary" style="font-size:15px; padding:10px 20px; border-radius: 10px; background-color: #777; border: none; color: white; cursor: pointer;" onclick="esconderSeletorData(<?= $pedido['id_emprestimo'] ?>)">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>

                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>

    <script>
    function mostrarSeletorData(id) {
        document.getElementById('btn-container-' + id).style.display = 'none';
        document.getElementById('form-retorno-' + id).style.display = 'flex';
    }

    function esconderSeletorData(id) {
        document.getElementById('btn-container-' + id).style.display = 'block';
        document.getElementById('form-retorno-' + id).style.display = 'none';
    }
    </script>
</body>
</html>