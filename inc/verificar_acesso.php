<?php

function verificarAcesso($niveisPermitidos) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    $nivelUsuario = isset($_SESSION['nivel']) ? (int)$_SESSION['nivel'] : -1;
    
    if (!in_array($nivelUsuario, $niveisPermitidos)) {
        header("Location: estoque.php?erro=sem_permissao");
        exit();
    }
}
?>