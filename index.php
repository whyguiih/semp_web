<?php
// Inicia a sessão para podermos pegar mensagens de erro
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SEMP</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h1>Login</h1>

            <form action="processa_login.php" method="POST">
                <div class="input-group">
                    <label for="usuario">Usuário</label>
                    <input type="text" id="usuario" name="usuario" required>
                </div>
                
                <div class="input-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                </div>
                
                <?php
            if (isset($_SESSION['erro_login'])) {
                echo '<p style="color: red; font-weight: bold; text-align: center; margin-bottom: 15px;">' . $_SESSION['erro_login'] . '</p>';
                unset($_SESSION['erro_login']); // Limpa a mensagem após exibir
            }
            ?>

                <button type="submit">Entrar</button>
            </form>

        </div>

        <div class="login-brand">
            <img src="img/SENAI_Branco.png" alt="Logo SENAI">
        </div>
    </div>

    <footer>
        Copyright &copy; Threeeo (Gabriel Artuso, Guilherme Brandalize e Larissa Gazoli) 2026. Todos os direitos reservados.
    </footer>
</body>
</html>