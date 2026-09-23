<?php
session_start();
require_once 'api.php';

if (isset($_GET['tipo']) && isset($_SESSION['unidade'])) {
    $tipo = (int)$_GET['tipo'];
    $codigo = gerarCodigoSemp($_SESSION['unidade'], $tipo);
    echo $codigo;
} else {
    echo "ERRO_SESSAO";
}
?>