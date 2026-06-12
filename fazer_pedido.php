<?php
session_start();
require_once 'api.php';

if (!isset($_SESSION['logado'])) { 
    header("Location: index.php"); 
    exit(); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['produtos_selecionados'])) {
    
    date_default_timezone_set('America/Sao_Paulo');

    $nome = $_POST['nome'] ?? $_SESSION['usuario'];
    $email = $_POST['email'] ?? '';
    $data_reserva = $_POST['data_reserva'] ?? '';
    $produtos_selecionados = $_POST['produtos_selecionados'];
    $prioridade = $_POST['prioridade'] ?? 'Baixo';
    $motivo = $_POST['motivo'] ?? '';
    $data_postagem = date('Y-m-d H:i:s'); 

    $dados = [
        'remetente' => $nome, 
        'email' => $email,
        'data_reserva' => $data_reserva,
        'unidade' => $_SESSION['unidade'],
        'produtos' => $produtos_selecionados,
        'prioridade' => $prioridade,
        'motivo' => $motivo,
        'data_postagem' => $data_postagem
    ];

    // Envia os dados para a API
    $resposta = chamarAPI('/pedidos/solicitar', 'POST', $dados);
    
    // VERIFICAÇÃO DE ERRO: Só direciona para sucesso se a API não retornar um campo de erro
    if (is_array($resposta) && isset($resposta['erro'])) {
        $_SESSION['erro_pedido'] = $resposta['erro'];
        header("Location: carrinho.php?msg=erro");
        exit();
    } else {
        header("Location: carrinho.php?msg=sucesso");
        exit();
    }
} else {
    header("Location: carrinho.php?msg=vazio");
    exit();
}
?>