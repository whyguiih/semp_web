<?php
session_start();
require_once 'api.php';

if (!isset($_SESSION['logado'])) { 
    header("Location: index.php"); 
    exit(); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['produtos_selecionados'])) {
    
    // Captura os novos campos que vieram da tela_pedido.php
    $nome = $_POST['nome'] ?? $_SESSION['usuario'];
    $email = $_POST['email'] ?? '';
    $data_reserva = $_POST['data_reserva'] ?? '';
    $produtos_selecionados = $_POST['produtos_selecionados'];
    
    // Pacote completo com as informações baseadas no seu pedido.java
    $dados = [
        'remetente' => $nome, 
        'email' => $email,
        'data_reserva' => $data_reserva,
        'unidade' => $_SESSION['unidade'],
        'produtos' => $produtos_selecionados
    ];

    // Manda para a nuvem
    $resposta = chamarAPI('/pedidos/solicitar', 'POST', $dados);
    
    header("Location: carrinho.php?msg=sucesso");
    exit();
} else {
    header("Location: carrinho.php?msg=vazio");
    exit();
}
?>