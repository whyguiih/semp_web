<?php
session_start();
require_once 'conexao.php';

if ($_SESSION['nivel_conta'] != '1') { header("Location: estoque.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $codigo = $_POST['codigo'];
    $descricao = $_POST['descricao'];
    $quant = $_POST['quant'];
    $uni_natal = $_POST['uni_natal'];
    $caminhoNoBanco = null;

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $pastaDestino = 'uploads/';
        $nomeUnico = time() . '_' . $_FILES['foto']['name'];
        $arquivoDestino = $pastaDestino . $nomeUnico;
        
        move_uploaded_file($_FILES['foto']['tmp_name'], $arquivoDestino);
        $caminhoNoBanco = $arquivoDestino;
    }

    $sql = "INSERT INTO tb_estoque (nome, codigo, descricao, quant, uni_natal, foto) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $codigo, $descricao, $quant, $uni_natal, $caminhoNoBanco]);
    
    $mensagem = "Produto cadastrado com sucesso!";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Produto</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="main-content">
        <h1 style="color: #1a4b9f; margin-bottom: 20px;">Cadastrar Novo Produto</h1>
        
        <?php if(isset($mensagem)) echo "<h2 style='color: green;'>$mensagem</h2>"; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nome do Produto:</label>
                <input type="text" name="nome" required>
            </div>
            <div class="form-group">
                <label>Código:</label>
                <input type="text" name="codigo" required>
            </div>
            <div class="form-group">
                <label>Quantidade:</label>
                <input type="number" name="quant" required>
            </div>
            <div class="form-group">
                <label>Unidade Natal (Origem):</label>
                <input type="text" name="uni_natal" value="<?php echo $_SESSION['unidade']; ?>" required>
            </div>
            <div class="form-group">
                <label>Descrição:</label>
                <textarea name="descricao" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label>Foto do Produto (Opcional):</label>
                <input type="file" name="foto" accept="image/png, image/jpeg">
            </div>
            
            <button type="submit" class="btn-primary">Salvar Produto</button>
        </form>
    </div>
</body>
</html>