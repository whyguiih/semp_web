<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SEMP</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/logo_menor.png" type="image/png">
</head>
<body>
    <div class="login-page">
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
                    
                    <button type="submit">Entrar</button>
                </form>

            </div>

            <div class="login-brand">
                <img src="img/SENAI_Branco.png" alt="Logo SENAI">
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['erro_login'])): ?>
    <div id="custom-alert" class="custom-alert-overlay">
        <div class="custom-alert-box">
            <div class="custom-alert-header">Aviso</div>
            <div class="custom-alert-body">
                <?php echo htmlspecialchars($_SESSION['erro_login']); ?>
            </div>
            <div class="custom-alert-footer">
                <button onclick="document.getElementById('custom-alert').style.display = 'none';">OK</button>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['erro_login']); ?>
    <?php endif; ?>

    <footer>
        Copyright &copy; Threeeo (Gabriel Artuso, Guilherme Brandalize e Larissa B. Gazoli) 2026. Todos os direitos reservados.
    </footer>

    <script>
    // Limpa a trava de exibição da sessão do navegador ao passar pelo login
    sessionStorage.removeItem('aviso_inicial_exibido');
</script>
</body>
</html>