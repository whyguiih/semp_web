<?php
session_start();
require_once 'api.php';

// O tipo 2 é Produto, 3 é Pedido
if (isset($_GET['tipo']) && isset($_SESSION['unidade'])) {
    $tipo = (int)$_GET['tipo'];
    $codigo = gerarCodigoSemp($_SESSION['unidade'], $tipo);
    echo $codigo;
} else {
    echo "ERRO_SESSAO";
}
?>