<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $apiUrl = "https://api-estoque.whyguiih.workers.dev/login";
    
    $dados_post = json_encode([
        'usuario' => $usuario,
        'senha' => $senha
    ]);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dados_post);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $resposta = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $resultado = json_decode($resposta, true);

    if ($http_code == 200 && isset($resultado['sucesso']) && $resultado['sucesso'] == true) {
        
        $_SESSION['logado'] = true;
        $_SESSION['usuario'] = $usuario;
        $_SESSION['nivel_conta'] = $resultado['nivel_conta'];
        header("Location: estoque.php");
        exit();

    } else {
        $_SESSION['erro_login'] = "Usuário ou senha inválidos!";
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>