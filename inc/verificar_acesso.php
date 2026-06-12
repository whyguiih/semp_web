<?php
// Exemplo de uso: require_once 'inc/verificar_acesso.php';
// verificarAcesso([0]); // Apenas nível 0 pode entrar

function verificarAcesso($niveisPermitidos) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    $nivelUsuario = isset($_SESSION['nivel']) ? (int)$_SESSION['nivel'] : -1;
    
    if (!in_array($nivelUsuario, $niveisPermitidos)) {
        header("Location: estoque.php?erro=sem_permissao");
        exit();
    }
}
?>