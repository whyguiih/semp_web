<?php

session_start();
require_once 'api.php';

if (!isset($_SESSION['logado']) || ($_SESSION['nivel_conta'] !== '1' && $_SESSION['nivel_conta'] !== '2')) { 
    header("Location: estoque.php"); 
    exit(); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $caminhoNoBanco = null;

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        if (!file_exists('uploads')) { mkdir('uploads', 0777, true); }
        $arquivoDestino = 'uploads/' . time() . '_' . $_FILES['foto']['name'];
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $arquivoDestino)) {
            $caminhoNoBanco = $arquivoDestino; 
        }
    }
    
    $nome_formatado = mb_strtoupper(mb_substr($_POST['nome'], 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($_POST['nome'], 1, null, 'UTF-8');
    
    $dados = [
        'nome' => $nome_formatado,
        'codigo' => $_POST['codigo'],
        'descricao' => $_POST['descricao'],
        'quant' => (int)$_POST['quant'],
        'uni_natal' => $_POST['uni_natal'],
        'marca_ref' => $_POST['marca_ref'],
        'cor' => $_POST['cor'],
        'descricao_detalhada' => $_POST['descricao_detalhada'],
        'foto' => $caminhoNoBanco
    ];

    // ===== AQUI ESTÁ A MÁGICA: CAPTURAR O RESULTADO =====
    $respostaAPI = chamarAPI('/produto/cadastrar', 'POST', $dados);
    
    // Vamos verificar o que a API respondeu
    if (is_array($respostaAPI) && isset($respostaAPI['erro'])) {
        // Se a Cloudflare enviou um erro, vamos exibi-lo!
        $mensagem_erro = "A API recusou: " . $respostaAPI['erro'];
    } elseif ($respostaAPI === null) {
        $mensagem_erro = "Erro de conexão: A API da Cloudflare não respondeu.";
    } else {
        $mensagem = "Produto cadastrado com sucesso!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Produto</title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="icon" href="img/logo_menor.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  

<?php if(isset($mensagem)) echo "<h2 style='color: #ffffff; background-color: rgba(26, 75, 159, 0.6); padding: 10px 20px; border-radius: 12px; margin-bottom: 15px; text-align: center; width: 100%; font-size: 18px;'>$mensagem</h2>"; ?>

<?php if(isset($mensagem_erro)) echo "<h2 style='color: #ffffff; background-color: #ef5e31; padding: 10px 20px; border-radius: 12px; margin-bottom: 15px; text-align: center; width: 100%; font-size: 18px;'>$mensagem_erro</h2>"; ?>
    <?php
        include 'inc/sidebar.php';
    ?>
    
  <div class="main-content" style="display: flex; justify-content: center; align-items: center; padding: 0;">
        
        <div class="cadastro-container">
            <h1 style="color: #1a4b9f; margin-bottom: 15px; text-align: center; font-size: 28px;">Cadastrar novo produto</h1>
            
            <?php if(isset($mensagem)) echo "<h2 style='color: #ffffff; background-color: rgba(26, 75, 159, 0.6); padding: 10px 20px; border-radius: 12px; margin-bottom: 15px; text-align: center; width: 100%; font-size: 18px;'>$mensagem</h2>"; ?>

            <form method="POST" enctype="multipart/form-data" class="form-cadastro">
                
                <div class="form-linha">
                    <div class="form-group">
                        <label>Nome do produto:</label>
                        <input type="text" name="nome" placeholder="Ex: Alicate de pressão" required>
                    </div>
                    <div class="form-group">
                        <label>Código do produto:</label>
                        <input type="text" name="codigo" placeholder="Ex: 3213" required>
                    </div>
                </div>
                
                <div class="form-linha">
                    <div class="form-group">
                        <label>Quantidade disponível:</label>
                        <input type="number" name="quant" placeholder="0" required min="1">
                    </div>
                    <div class="form-group">
                        <label>Unidade de origem:</label>
                        <input type="text" name="uni_natal" value="<?= htmlspecialchars($_SESSION['unidade']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descrição do produto:</label>
                    <textarea name="descricao" rows="2" placeholder="Adicione informações sobre o produto"></textarea>
                </div>

                 <div class="form-group">
                    <label>Descrição detalhada do produto:</label>
                    <textarea name="descricao_detalhada" rows="2" placeholder="Adicione detalhes sobre o produto"></textarea>
                </div>
               <div class="form-linha">
    <div class="form-group">
        <label>Cor do produto:</label>
        <input type="text" name="cor" placeholder="Ex: Vermelho">
    </div>

    <div class="form-group">
        <label>Marca de referência:</label>
        <input type="text" name="marca_ref" placeholder="Ex: Tramontina" required>
    </div>
</div>
                
                <div class="form-group">
                    <label>Foto (Obrigatório):</label>
                    <input type="file" name="foto" accept="image/png, image/jpeg" class="file-input">
                </div>
                
                <div style="display: flex; gap: 15px; width: 100%; margin-top: 10px;">
    <button type="submit" class="btn-primary btn-salvar" style="flex: 1; margin-top: 0;">Adicionar ao estoque</button>
    <a href="edicao_estoque.php" class="btn-primary btn-danger" style="flex: 1; text-decoration: none; padding: 12px 35px; font-size: 20px; border-radius: 15px; display: flex; align-items: center; justify-content: center;">Editar estoque</a>
</div>
            </form>
        </div>

    </div>
</body>
</html>