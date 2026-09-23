<?php
define('API_URL', 'https://api-estoque.whyguiih.workers.dev');

function chamarAPI($endpoint, $metodo = 'GET', $dados = null) {
    $url = API_URL . $endpoint;
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $metodo);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $headers = array();

    if (isset($_SESSION['usuario'])) {
        $headers[] = 'X-Usuario-ID: ' . $_SESSION['usuario'];
    }
    
    if ($dados !== null) {
        $json_dados = json_encode($dados);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_dados);
        
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Content-Length: ' . strlen($json_dados);
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $resposta = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($resposta, true);
}
function gerarCodigoSemp($nomeUnidade, $tipoEntidade) {
    $unidadeFormatada = mb_strtolower(trim($nomeUnidade), 'UTF-8');
    
    $mapaUnidades = [
        'garibaldi'            => ['est' => 'A', 'reg' => 'c', 'tipo' => 0],
        'farroupilha'          => ['est' => 'A', 'reg' => 'c', 'tipo' => 0],
        'encantado'            => ['est' => 'A', 'reg' => 'l', 'tipo' => 0],
        
        'ceit'                 => ['est' => 'A', 'reg' => 'c', 'tipo' => 1],
        'galvanotek'           => ['est' => 'A', 'reg' => 'c', 'tipo' => 1],
        
        'default'              => ['est' => 'A', 'reg' => 'a', 'tipo' => 0]
    ];

    $dados = isset($mapaUnidades[$unidadeFormatada]) ? $mapaUnidades[$unidadeFormatada] : $mapaUnidades['default'];
    
    $caracteresPermitidos = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $maxIndex = strlen($caracteresPermitidos) - 1;

    $c1 = $dados['est'];
    $c2 = $dados['reg'];
    $c3 = $caracteresPermitidos[random_int(0, $maxIndex)];
    $c4 = $dados['tipo'];
    $c5 = $tipoEntidade;
    
    $prefixo = "{$c1}{$c2}{$c3}{$c4}{$c5}";

    $parteAleatoria = "";
    for ($i = 0; $i < 10; $i++) {
        $parteAleatoria .= $caracteresPermitidos[random_int(0, $maxIndex)];
    }

    return "{$prefixo}-{$parteAleatoria}";
}
?>