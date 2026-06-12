<?php
session_start();
require_once 'api.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pega os dados enviados pelo formulário da tela do produto
    $dados = [
        'nome_produto' => $_POST['nome_produto'], 
        'quantidade' => $_POST['quantidade']
    ];

    // 1. Em vez de só chamar a API, nós guardamos a resposta dela
    $resposta = chamarAPI('/carrinho/adicionar', 'POST', $dados);
    
    // 2. Verificamos se a API do Cloudflare devolveu algum "erro"
    if (isset($resposta['erro'])) {
        // Guarda o erro exato na sessão e manda para o carrinho exibir a faixa vermelha
        $_SESSION['erro_pedido'] = "Falha ao adicionar: " . $resposta['erro'];
        header("Location: carrinho.php?msg=erro");
        exit();
    }

    // 3. Se não teve erro, redireciona normalmente
    header("Location: carrinho.php");
    exit();
}
?>