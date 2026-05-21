<?php
session_start();
require_once 'api.php'; // Chama a sua função de comunicação com a API

// Verifica se o usuário está logado
if (!isset($_SESSION['logado'])) { 
    header("Location: index.php"); 
    exit(); 
}

// Verifica se a página recebeu o ID do produto pela URL (via GET)
if (isset($_GET['id'])) {
    $id_produto = $_GET['id'];
    
    // Avisa a API para remover este produto do carrinho do usuário atual.
    // *Lembrete: Você precisará garantir que a rota '/carrinho/remover' 
    // exista no seu código do Cloudflare Worker!
    chamarAPI('/carrinho/remover', 'POST', [
        'id_produto' => $id_produto,
        'usuario' => $_SESSION['usuario']
    ]);
}

// Depois de remover, manda o usuário de volta para a tela do carrinho
header("Location: carrinho.php");
exit();
?>