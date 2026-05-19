<?php
session_start();
require_once 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_produto'])) {
    $id = $_POST['id_produto'];

    // Lógica traduzida da função acaoAdicionarAoCarrinho()
    $sql = "UPDATE tb_estoque SET carrinho = '1' WHERE id_estoque = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    // Redireciona para o carrinho após adicionar
    header("Location: carrinho.php");
    exit();
} else {
    header("Location: estoque.php");
    exit();
}
?>