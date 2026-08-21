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
    // Normaliza para letras minúsculas para evitar erros de digitação
    $unidadeFormatada = mb_strtolower(trim($nomeUnidade), 'UTF-8');
    
    // NOVO MAPEAMENTO DE UNIDADES
    // 'est' = Identificador do Estado (Ex: A = RS)
    // 'reg' = Identificador da Região (Ex: c = Serra 1, a = Metropolitana 1)
    // 'tipo' = 0 para Unidade, 1 para Adendo
    $mapaUnidades = [
        'garibaldi'            => ['est' => 'A', 'reg' => 'c', 'tipo' => 0], // RS, Serra 1, Unidade
        'farroupilha'          => ['est' => 'A', 'reg' => 'c', 'tipo' => 0], // RS, Serra 1, Unidade
        'encantado'            => ['est' => 'A', 'reg' => 'l', 'tipo' => 0], // RS, Vale do Taquari 2, Unidade
        
        // Adendos
        'ceit'                 => ['est' => 'A', 'reg' => 'c', 'tipo' => 1], // RS, Serra 1, Adendo
        'galvanotek'           => ['est' => 'A', 'reg' => 'c', 'tipo' => 1], // RS, Serra 1, Adendo
        
        // Padrão de segurança
        'default'              => ['est' => 'A', 'reg' => 'a', 'tipo' => 0]  // RS, Metrop 1, Unidade
    ];

    // Busca as configurações da unidade atual
    $dados = isset($mapaUnidades[$unidadeFormatada]) ? $mapaUnidades[$unidadeFormatada] : $mapaUnidades['default'];
    
    $caracteresPermitidos = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $maxIndex = strlen($caracteresPermitidos) - 1;

    // --- CONSTRUÇÃO DO PREFIXO DE 5 DÍGITOS ---
    $c1 = $dados['est']; // 1º: Identificador do estado
    $c2 = $dados['reg']; // 2º: Identificador da região
    $c3 = $caracteresPermitidos[random_int(0, $maxIndex)]; // 3º: Caractere aleatório
    $c4 = $dados['tipo']; // 4º: Diferenciador (0=Unidade, 1=Adendo)
    $c5 = $tipoEntidade; // 5º: 2=Produto, 3=Pedido
    
    $prefixo = "{$c1}{$c2}{$c3}{$c4}{$c5}";

    // --- CONSTRUÇÃO DO SUFIXO ALEATÓRIO DE 10 DÍGITOS ---
    $parteAleatoria = "";
    for ($i = 0; $i < 10; $i++) {
        $parteAleatoria .= $caracteresPermitidos[random_int(0, $maxIndex)];
    }

    // Retorna o código final separado por hífen
    return "{$prefixo}-{$parteAleatoria}";
}
?>