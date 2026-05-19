<?php
session_start();

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Estoque - SEMP</title>
</head>
<body>
    <h1>Bem-vindo ao Estoque, <?php echo htmlspecialchars($_SESSION['usuario']); ?>!</h1>
    
    <h2><?php echo htmlspecialchars($_SESSION['nivel_conta']); ?></h2>
    <h3>Unidade: <?php echo htmlspecialchars($_SESSION['unidade']); ?></h3>

    <p>Aqui ficará a tabela de estoque (tb_estoque)...</p>
    
    <a href="logout.php">Sair (Logout)</a>
</body>
</html>