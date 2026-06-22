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

       $tamanho = 10;
$caracteresPermitidos = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
$resultado = "";

// Pegamos o tamanho total da string menos 1 (pois o índice começa em 0)
$maxIndex = strlen($caracteresPermitidos) - 1;

for ($i = 0; $i < $tamanho; $i++) {
    // Sorteia um número entre 0 e o índice máximo
    $indexSorteado = random_int(0, $maxIndex); 
    
    // Concatena o caractere sorteado na string de resultado
    $resultado .= $caracteresPermitidos[$indexSorteado];
}

    $dados = [
        'remetente' => $nome, 
        'email' => $email,
        'data_reserva' => $data_reserva,
        'unidade' => $_SESSION['unidade'],
        'produtos' => $produtos_selecionados,
        'prioridade' => $prioridade,
        'motivo' => $motivo,
        'data_postagem' => $data_postagem,
        'codigo_pedido' => $resultado
    ];

    // Envia os dados para a API
    $resposta = chamarAPI('/pedidos/solicitar', 'POST', $dados);
    

    if (is_array($resposta) && isset($resposta['erro'])) {
        $_SESSION['erro_pedido'] = $resposta['erro'];
        header("Location: carrinho.php?msg=erro");
        exit();
    } else {
        // 1. GUARDA o resultado que você gerou na sessão
        $_SESSION['codigo_pedido'] = $resultado;
        
        // 2. REDIRECIONA para a página do carrinho
        header("Location: carrinho.php?msg=sucesso");
        exit();
    }
        exit();
    } else {
    header("Location: carrinho.php?msg=vazio");
    exit();
}
?>