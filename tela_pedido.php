<?php
session_start();
require_once 'api.php';

if (!isset($_SESSION['logado'])) { header("Location: index.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] != "POST" || empty($_POST['produtos_selecionados'])) {
    header("Location: carrinho.php?msg=vazio");
    exit();
}

$produtos_selecionados = $_POST['produtos_selecionados'];
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Finalizar Pedido</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php
        include 'inc/sidebar.php';
    ?>

    <div class="main-content" style="display: flex; justify-content: center; align-items: center; padding: 0;">
        <div class="cadastro-container" style="max-width: 500px; padding: 40px;">
            <h1 style="color: #1a4b9f; margin-bottom: 25px; text-align: center; font-size: 28px;">Detalhes da Reserva</h1>
            
            <form action="fazer_pedido.php" method="POST" class="form-cadastro">
                
                <?php foreach ($produtos_selecionados as $id): ?>
                    <input type="hidden" name="produtos_selecionados[]" value="<?= htmlspecialchars($id) ?>">
                <?php endforeach; ?>
                
                <div class="form-group">
                    <label>Nome:</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($_SESSION['usuario'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" placeholder="seu@email.com" required>
                </div>
                
                <div class="form-group">
                    <label>Data de Reserva:</label>
                    <input type="date" name="data_reserva" required>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 25px; width: 100%; justify-content: center;">
                    <a href="carrinho.php" class="btn-deletar" style="text-decoration: none; padding: 12px 30px; border-radius: 15px; text-align: center; display: flex; align-items: center;">Voltar</a>
                    <button type="submit" class="btn-primary" style="padding: 12px 30px; font-size: 18px; border-radius: 15px; box-shadow: 0 5px 15px rgba(26, 75, 159, 0.3);">Confirmar Pedido</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>