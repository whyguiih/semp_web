<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Login - SEMP</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .login-container { display: flex; justify-content: center; align-items: center; height: 100vh; width: 100vw; background: linear-gradient(to bottom, #1a4b9f 0%, #ef5e31 100%); }
        .login-box { background: rgba(255,255,255,0.9); padding: 40px; border-radius: 20px; text-align: center; width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .login-box input { width: 100%; padding: 15px; margin: 10px 0; border: 1px solid #ccc; border-radius: 10px; font-size: 16px; box-sizing: border-box; }
        .login-box img { width: 150px; margin-bottom: 20px; }
        .erro { color: red; font-weight: bold; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <img src="img/logo.png" alt="Logo SENAI">
            <form action="processa_login.php" method="POST">
                <?php if(isset($_GET['erro'])) echo '<p class="erro">Utilizador ou palavra-passe incorretos!</p>'; ?>
                <input type="text" name="usuario" placeholder="Utilizador" required>
                <input type="password" name="senha" placeholder="Palavra-passe" required>
                <button type="submit" class="btn-primary" style="margin-top: 20px;">Entrar</button>
            </form>
        </div>
    </div>
</body>
</html>