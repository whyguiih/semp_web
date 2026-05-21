<?php
session_start();
require_once 'api.php';

if (!isset($_SESSION['logado']) || $_SESSION['nivel_conta'] != '1') { header("Location: estoque.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $caminhoNoBanco = null;

    // Lógica para guardar a imagem fisicamente na tua pasta do servidor (XAMPP/Host)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        if (!file_exists('uploads')) { mkdir('uploads', 0777, true); }
        
        $arquivoDestino = 'uploads/' . time() . '_' . $_FILES['foto']['name'];
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $arquivoDestino)) {
            $caminhoNoBanco = $arquivoDestino; // Guarda o caminho para referenciar depois
        }
    }

    // Prepara o JSON para enviar para o Worker
    $dados = [
        'nome' => $_POST['nome'],
        'codigo' => $_POST['codigo'],
        'descricao' => $_POST['descricao'],
        'quant' => (int)$_POST['quant'],
        'uni_natal' => $_POST['uni_natal'],
        'foto' => $caminhoNoBanco
    ];

    chamarAPI('/produto/cadastrar', 'POST', $dados);
    $mensagem = "Produto cadastrado com sucesso na Nuvem!";
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Produto</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="sidebar">
        <a href="estoque.php"><img src="img/logo_menor.png" alt="Logo"></a>
        <a href="estoque.php"><img src="img/lista.png" alt="Lista"></a>
        <a href="carrinho.php"><img src="img/carrinho.png" alt="Carrinho"></a>
        
        <?php if ($_SESSION['nivel_conta'] == '1'): ?>
            <a href="autorizar_pedidos.php"><img src="img/controle.png" alt="Controlo"></a>
        <?php endif; ?>

        <?php if ($_SESSION['nivel_conta'] == '1' || $_SESSION['nivel_conta'] == '2'): ?>
            <a href="cadastro_produto.php"><img src="img/lupa.png" alt="Cadastro"></a>
        <?php endif; ?>
        
        <div class="sidebar-bottom">
            <a href="logout.php"><img src="img/sair.png" alt="Sair"></a>
        </div>
    </div>
    
  <div class="main-content" style="display: flex; justify-content: center; align-items: center; padding: 0;">
        
        <div class="cadastro-container">
            <h1 style="color: #1a4b9f; margin-bottom: 15px; text-align: center; font-size: 28px;">Cadastrar Novo Produto</h1>
            
            <?php if(isset($mensagem)) echo "<h2 style='color: #ffffff; background-color: rgba(26, 75, 159, 0.6); padding: 10px 20px; border-radius: 12px; margin-bottom: 15px; text-align: center; width: 100%; font-size: 18px;'>$mensagem</h2>"; ?>

            <form method="POST" enctype="multipart/form-data" class="form-cadastro">
                
                <div class="form-linha">
                    <div class="form-group">
                        <label>Nome do Produto:</label>
                        <input type="text" name="nome" placeholder="Ex: Alicate de Pressão" required>
                    </div>
                    <div class="form-group">
                        <label>Código:</label>
                        <input type="text" name="codigo" placeholder="Ex: 3213" required>
                    </div>
                </div>
                
                <div class="form-linha">
                    <div class="form-group">
                        <label>Quantidade:</label>
                        <input type="number" name="quant" placeholder="0" required min="1">
                    </div>
                    <div class="form-group">
                        <label>Unidade (Origem):</label>
                        <input type="text" name="uni_natal" value="<?= htmlspecialchars($_SESSION['unidade']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descrição:</label>
                    <textarea name="descricao" rows="2" placeholder="Adicione detalhes sobre o produto..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Foto (Opcional):</label>
                    <input type="file" name="foto" accept="image/png, image/jpeg" class="file-input">
                </div>
                
                <button type="submit" class="btn-primary btn-salvar">Salvar Produto</button>
            </form>
        </div>

    </div>
</body>
</html>