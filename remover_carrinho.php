<?php
session_start();
require_once 'api.php';

if (isset($_GET['nome'])) {
    
    $dados = [
        'nome_produto' => $_GET['nome']
    ];

    chamarAPI('/carrinho/remover', 'POST', $dados);
}

header("Location: carrinho.php");
exit();
?>