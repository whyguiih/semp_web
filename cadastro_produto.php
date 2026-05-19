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
        <a href="autorizar_pedidos.php"><img src="img/controle.png" alt="Controlo"></a>
        <a href="cadastro_produto.php"><img src="img/lupa.png" alt="Cadastro"></a>
        <div class="sidebar-bottom"><a href="logout.php"><img src="img/sair.png" alt="Sair"></a></div>
    </div>
    
    <div class="main-content">
        <h1 style="color: #1a4b9f; margin-bottom: 20px;">Cadastrar Novo Produto</h1>
        <?php if(isset($mensagem)) echo "<h2 style='color: green; margin-bottom: 15px;'>$mensagem</h2>"; ?>

        <form method="POST" enctype="multipart/form-data" style="max-width: 600px;">
            <div class="form-group"><label>Nome do Produto:</label><input type="text" name="nome" required></div>
            <div class="form-group"><label>Código:</label><input type="text" name="codigo" required></div>
            <div class="form-group"><label>Quantidade:</label><input type="number" name="quant" required></div>
            <div class="form-group"><label>Unidade (Origem):</label><input type="text" name="uni_natal" value="<?= htmlspecialchars($_SESSION['unidade']) ?>" required></div>
            <div class="form-group"><label>Descrição:</label><textarea name="descricao" rows="4"></textarea></div>
            <div class="form-group"><label>Foto (Opcional):</label><input type="file" name="foto" accept="image/png, image/jpeg"></div>
            
            <button type="submit" class="btn-primary">Salvar Produto na API</button>
        </form>
    </div>
</body>
</html>