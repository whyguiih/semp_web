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
?>