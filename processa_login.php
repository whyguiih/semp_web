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
        header("Location: index.php?erro=1");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>