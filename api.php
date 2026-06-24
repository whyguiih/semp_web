<?php
// api.php
// SUBSTITUI AQUI PELA TUA URL DO CLOUDFLARE WORKER!
define('API_URL', 'https://api-estoque.whyguiih.workers.dev');

function chamarAPI($endpoint, $metodo = 'GET', $dados = null) {
    $url = API_URL . $endpoint;
    $ch = curl_init($url);
    
    // Configura o cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $metodo);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // 1. Criamos um array para guardar nossos cabeçalhos (Headers)
    $headers = array();

    // 2. O SEGREDO: Se o usuário estiver logado na sessão, anexa o nome dele!
    if (isset($_SESSION['usuario'])) {
        $headers[] = 'X-Usuario-ID: ' . $_SESSION['usuario'];
    }
    
    // 3. Se houver dados (POST), converte para JSON e prepara para enviar
    if ($dados !== null) {
        $json_dados = json_encode($dados);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_dados);
        
        // Adiciona os cabeçalhos obrigatórios do JSON na nossa lista
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Content-Length: ' . strlen($json_dados);
    }

    // 4. Se a nossa lista de cabeçalhos não estiver vazia, aplicamos no cURL
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $resposta = curl_exec($ch);
    curl_close($ch);
    
    // Devolve os dados já convertidos de JSON para Array do PHP
    return json_decode($resposta, true);
}

function gerarCodigoSemp($nomeUnidade, $tipoEntidade) {
    // Normaliza para letras minúsculas para evitar erros de digitação (ex: CEIT vs ceit)
    $unidadeFormatada = mb_strtolower(trim($nomeUnidade), 'UTF-8');

    // Mapeamento geográfico e de identificação única do ecossistema SEMP
    // Estrutura: ['est' => Estado, 'reg' => Região, 'id' => Nº Próprio, 'tipo' => 0=Adendo / 1=Sede]
    $mapaUnidades = [
        // ================= SEDES (4º dígito = 1) =================
        'garibaldi'            => ['est' => 9, 'reg' => 5, 'id' => 1, 'tipo' => 1],
        'farroupilha'          => ['est' => 9, 'reg' => 5, 'id' => 2, 'tipo' => 1],
        'encantado'            => ['est' => 9, 'reg' => 5, 'id' => 3, 'tipo' => 1],

        // ================= ADENDOS (4º dígito = 0) =================
        // Adendos vinculados a Garibaldi
        'ceit'                 => ['est' => 9, 'reg' => 5, 'id' => 1, 'tipo' => 0], // Adendo único 1
        'galvanotek'           => ['est' => 9, 'reg' => 5, 'id' => 2, 'tipo' => 0], // Adendo único 2
        
        // Adendos vinculados a Farroupilha (exemplo de nomes genéricos)
        'adendo farroupilha 1' => ['est' => 9, 'reg' => 5, 'id' => 3, 'tipo' => 0], // Adendo único 3
        'adendo farroupilha 2' => ['est' => 9, 'reg' => 5, 'id' => 4, 'tipo' => 0], // Adendo único 4
        
        // Configuração padrão de segurança caso surja uma unidade não mapeada
        'default'              => ['est' => 9, 'reg' => 5, 'id' => 9, 'tipo' => 0] 
    ];

    // Busca as configurações da unidade atual
    $dados = isset($mapaUnidades[$unidadeFormatada]) ? $mapaUnidades[$unidadeFormatada] : $mapaUnidades['default'];

    $d1 = $dados['est'];
    $d2 = $dados['reg'];
    $d3 = $dados['id'];
    $d4 = $dados['tipo'];
    $d5 = $tipoEntidade; // 2 para Produto, 3 para Pedido

    // Concatena os 5 dígitos iniciais de reconhecimento
    $prefixo = "{$d1}{$d2}{$d3}{$d4}{$d5}";

    // Canal com 62 caracteres possíveis (A-Z, a-z, 0-9) para a parte aleatória
    $caracteresPermitidos = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $parteAleatoria = "";
    $maxIndex = strlen($caracteresPermitidos) - 1;

    // Gera os 10 caracteres aleatórios finais (podendo haver repetição interna)
    for ($i = 0; $i < 10; $i++) {
        $parteAleatoria .= $caracteresPermitidos[random_int(0, $maxIndex)];
    }

    // Retorna a composição exata de 16 caracteres com o hífen separador
    return "{$prefixo}-{$parteAleatoria}";
}
?>