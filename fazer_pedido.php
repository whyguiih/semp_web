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
   $data_reserva_inicio = $_POST['data_reserva_inicio'] ?? '';
$data_reserva_fim = $_POST['data_reserva_fim'] ?? '';
    $prioridade = $_POST['prioridade'] ?? 'Baixo';
    $motivo = $_POST['motivo'] ?? '';
    $data_postagem = date('Y-m-d H:i:s'); 

   $resultado_pedido = $_POST['codigo_pedido'];

    $produtos_selecionados = $_POST['produtos_selecionados'];
    $quantidades = $_POST['quantidades'] ?? [];

    $produtos_formatados = [];

    if (is_array($produtos_selecionados)) {
        foreach ($produtos_selecionados as $nome_produto) {
            $qtd = isset($quantidades[$nome_produto]) ? (int)$quantidades[$nome_produto] : 1;
            
            $produtos_formatados[] = [
                'codigo_produto'              => gerarCodigoSemp($_SESSION['unidade'], 2),
                'nome_produto'                => $nome_produto,
                'quantidade_produto'          => $qtd,
                'unidade_produto'             => $_SESSION['unidade'],
                'descricao_produto'           => '',
                'descricao_detalhada_produto' => '',
                'cor_produto'                 => '',
                'marca_produto'               => '',
                'pedido_produto'              => $resultado_pedido 
            ];
        }
    }

    $dados = [
        'remetente' => $nome, 
        'email' => $email,
        'data_reserva' => $data_reserva_inicio,
        'data_devolucao' => $data_reserva_fim,
        'unidade' => $_SESSION['unidade'],
        'prioridade' => $prioridade,
        'motivo' => $motivo,
        'data_postagem' => $data_postagem,
        'codigo_pedido' => $resultado_pedido,
        'produtos' => $produtos_formatados 
    ];

    $resposta = chamarAPI('/pedidos/solicitar', 'POST', $dados);
    
    if (is_array($resposta) && isset($resposta['erro'])) {
        $_SESSION['erro_pedido'] = $resposta['erro'];
        header("Location: carrinho.php?msg=erro");
        exit();
    } else {
        $_SESSION['codigo_pedido'] = $resultado_pedido;
        header("Location: carrinho.php?msg=sucesso");
        exit();
    }
} else {
    header("Location: carrinho.php?msg=vazio");
    exit();
}
?>