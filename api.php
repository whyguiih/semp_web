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

    // Se houver dados (POST), converte para JSON e envia
    if ($dados !== null) {
        $json_dados = json_encode($dados);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_dados);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json_dados)
        ));
    }

    $resposta = curl_exec($ch);
    curl_close($ch);
    
    // Devolve os dados já convertidos de JSON para Array do PHP
    return json_decode($resposta, true);
}
?>