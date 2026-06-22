<?php
session_start();
require_once 'api.php';

// Segurança: Permite apenas Operadores (1) e Gerentes (2)
if (!isset($_SESSION['logado']) || $_SESSION['nivel_conta'] == '0') { 
    header("Location: estoque.php"); 
    exit(); 
}

// Verifica se o usuário clicou no botão "Pedir de volta"
if (isset($_GET['acao']) && $_GET['acao'] == 'solicitar' && isset($_GET['id_emprestimo'])) {
    
    // Chama a nova rota da API que fará a atualização no banco
    chamarAPI('/pedidos/solicitar_retorno', 'POST', [
        'id_emprestimo' => $_GET['id_emprestimo']
    ]);
    
    // Redireciona com mensagem de sucesso
    header("Location: pedir_retorno.php?msg=Solicitado");
    exit();
}

// Busca os produtos que a SUA unidade emprestou para outras (e que já foram aprovados)
$produtosEmprestados = chamarAPI('/pedidos/emprestados?unidade=' . urlencode($_SESSION['unidade']), 'GET');

// Tratamento de erro caso a API falhe
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
    <?php
        include 'inc/sidebar.php';
    ?>

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
                            <h2 style="color: #1a4b9f; margin: 0 0 10px 0; font-size: 24px;">Emprestado para: <?= htmlspecialchars($pedido['destinatario'] ?? 'Desconhecido') ?></h2>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Produto(s):</strong> <?= htmlspecialchars($pedido['nome_produto']) ?></p>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Quantidade:</strong> <span style="background-color: #1a4b9f; color: white; padding: 2px 8px; border-radius: 5px;"><?= htmlspecialchars($pedido['quant']) ?></span></p>
                            <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Motivo do empréstimo:</strong> <?= ucfirst(htmlspecialchars($pedido['motivo'] ?? 'Não informado')) ?></p>
                        </div>
                        
                        <div style="display: flex; gap: 15px; flex-wrap: wrap; width: auto;">
                            <a href="pedir_retorno.php?acao=solicitar&id_emprestimo=<?= $pedido['id_emprestimo'] ?>" 
                                class="btn-primary" 
                                style="text-decoration:none; font-size:18px; padding:12px 30px; border-radius: 15px; text-align: center; flex: 1; min-width: 120px; background-color: #ef5e31; box-shadow: 0 4px 6px rgba(239, 94, 49, 0.2);"
                                onclick="return confirm('Tem certeza que deseja exigir a devolução deste item?');">
                            </a>
                        </div>

                    </div>

                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
</body>
</html>