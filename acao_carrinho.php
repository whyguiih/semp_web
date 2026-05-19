<?php
session_start();
require_once 'api.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_produto'])) {
    // Comunica à API que o carrinho deste produto virou "1"
    chamarAPI('/carrinho/adicionar', 'POST', ['id_produto' => $_POST['id_produto']]);
    header("Location: carrinho.php");
    exit();
} else {
    header("Location: estoque.php");
    exit();
}
?>