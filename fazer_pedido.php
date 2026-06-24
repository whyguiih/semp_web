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
    $prioridade = $_POST['prioridade'] ?? 'Baixo';
    $motivo = $_POST['motivo'] ?? '';
    $data_postagem = date('Y-m-d H:i:s'); 

    // Gera o código único do PEDIDO (O dígito 3 no final indica que é um pedido)
    $resultado_pedido = gerarCodigoSemp($_SESSION['unidade'], 3);

    // Converte os produtos selecionados do carrinho
    $produtos_selecionados = $_POST['produtos_selecionados'];
    $itens_carrinho = is_string($produtos_selecionados) ? json_decode($produtos_selecionados, true) : $produtos_selecionados;
    
    $produtos_formatados = [];

    // Processa cada item do pedido e gera os códigos estruturados para a tabela tb_itens_pedido
    if (is_array($itens_carrinho)) {
        foreach ($itens_carrinho as $item) {
            
            // Se o produto já possui um código vindo do estoque, usamos ele. 
            // Caso contrário (como itens a serem manufaturados/comprados), geramos um novo código de Produto (dígito 2).
            $codigo_produto = !empty($item['codigo']) ? $item['codigo'] : gerarCodigoSemp($_SESSION['unidade'], 2);

            $produtos_formatados[] = [
                'codigo_produto'              => $codigo_produto,
                'nome_produto'                => $item['nome'] ?? 'Sem nome',
                'quantidade_produto'          => (int)($item['quant'] ?? $item['quantidade'] ?? 1),
                'unidade_produto'             => $item['uni_natal'] ?? $_SESSION['unidade'],
                'descricao_produto'           => $item['descricao'] ?? '',
                'descricao_detalhada_produto' => $item['descricao_detalhada'] ?? '',
                'cor_produto'                 => $item['cor'] ?? '',
                'marca_produto'               => $item['marca_ref'] ?? '',
                'pedido_produto'              => $resultado_pedido // <-- ESTA É A PONTE (A Chave Estrangeira com a tb_emprestimo)
            ];
        }
    }

    // Prepara o pacote de dados Master para enviar à API
    $dados = [
        'remetente' => $nome, 
        'email' => $email,
        'data_reserva' => $data_reserva,
        'unidade' => $_SESSION['unidade'],
        'prioridade' => $prioridade,
        'motivo' => $motivo,
        'data_postagem' => $data_postagem,
        'codigo_pedido' => $resultado_pedido,
        'produtos' => $produtos_formatados // Array com todos os produtos já mastigados
    ];

    // Envia os dados para a API (Cloudflare Worker)
    $resposta = chamarAPI('/pedidos/solicitar', 'POST', $dados);
    
    if (is_array($resposta) && isset($resposta['erro'])) {
        $_SESSION['erro_pedido'] = $resposta['erro'];
        header("Location: carrinho.php?msg=erro");
        exit();
    } else {
        // Guarda o resultado na sessão e redireciona
        $_SESSION['codigo_pedido'] = $resultado_pedido;
        header("Location: carrinho.php?msg=sucesso");
        exit();
    }
} else {
    header("Location: carrinho.php?msg=vazio");
    exit();
}
?>