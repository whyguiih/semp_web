<?php
session_start();
require_once 'api.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dados = [
        'nome_produto' => $_POST['nome_produto']
    ];

    $resposta = chamarAPI('/carrinho/adicionar', 'POST', $dados);
    
    if (isset($resposta['erro'])) {
        $_SESSION['erro_pedido'] = "Falha ao adicionar: " . $resposta['erro'];
        header("Location: carrinho.php?msg=erro");
        exit();
    }

    header("Location: carrinho.php");
    exit();
}
?>