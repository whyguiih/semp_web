<?php
session_start();
require_once 'api.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    // Chama a rota /login do Worker
    $resposta = chamarAPI('/login', 'POST', ['usuario' => $usuario, 'senha' => $senha]);
   
    if (isset($resposta['sucesso']) && $resposta['sucesso'] === true) {
        $_SESSION['logado'] = true;
        $_SESSION['usuario'] = $resposta['usuario'];
        $_SESSION['nivel_conta'] = $resposta['nivel_conta'];
        $_SESSION['unidade'] = $resposta['unidade'];
        
        header("Location: estoque.php");
        exit();
    } else {
        // Pega a mensagem exata que o banco de dados/API enviou
        $mensagem_erro = isset($resposta['mensagem']) ? $resposta['mensagem'] : "Erro desconhecido ao realizar login.";
        $_SESSION['erro_login'] = $mensagem_erro;
        
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>