<?php
session_start();
require_once 'api.php';

// Verifica se o nome do produto foi passado pela URL
if (isset($_GET['nome'])) {
    
    // Prepara os dados do jeito que a nossa API do Cloudflare espera
    $dados = [
        'nome_produto' => $_GET['nome']
    ];

    // Dispara a requisição para a API deletar do banco
    chamarAPI('/carrinho/remover', 'POST', $dados);
}

// Independentemente de dar certo ou errado, volta para o carrinho
header("Location: carrinho.php");
exit();
?>