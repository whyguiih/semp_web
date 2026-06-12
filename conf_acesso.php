<?php

session_start();
require_once 'api.php';

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
        'usuario' => $nome_formatado,
        'senha' => $_POST['senha'],
        'nivel' => (int)$_POST['nivel'],
        'unidade' => $_POST['unidade'],
        'foto' => $caminhoNoBanco
    ];

    // ===== AQUI ESTÁ A MÁGICA: CAPTURAR O RESULTADO =====
    $respostaAPI = chamarAPI('/usuario/cadastrar', 'POST', $dados);
    
    // Vamos verificar o que a API respondeu
    if (is_array($respostaAPI) && isset($respostaAPI['erro'])) {
        // Se a Cloudflare enviou um erro, vamos exibi-lo!
        $mensagem_erro = "A API recusou: " . $respostaAPI['erro'];
    } elseif ($respostaAPI === null) {
        $mensagem_erro = "Erro de conexão: A API da Cloudflare não respondeu.";
    } else {
        $mensagem = "Usuário cadastrado com sucesso!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Usuário</title>
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
    
  <div class="main-content">
    
    <div style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
        
        <div class="form-card">
            <h1>Cadastrar novo usuário</h1>
            
            <form action="sua_api_ou_acao.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-grid">
                    <div class="input-group">
                        <label>Nome de usuário:</label>
                        <input type="text" name="usuario" placeholder="Ex: admin_garibaldi">
                    </div>

                    <div class="input-group">
                        <label>Senha:</label>
                        <input type="password" name="senha" placeholder="Ex: 3213">
                    </div>

                    <div class="input-group">
                        <label>Nível de acesso:</label>
                        <input type="number" name="nivel" placeholder="0">
                    </div>

                    <div class="input-group">
                        <label>Unidade:</label>
                        <input type="text" name="unidade" placeholder="Garibaldi">
                    </div>
                </div>

                <div class="input-group" style="margin-top: 20px;">
                    <label>Foto:</label>
                    <div class="input-file-container">
                        <input type="file" name="foto">
                    </div>
                </div>

                <button type="submit" class="btn-submit">Adicionar ao sistema</button>
                
            </form>
        </div>

    </div>
  </div>

<script>
    // Seleciona o formulário da página
    const formulario = document.querySelector('form');

    formulario.addEventListener('submit', async function(evento) {
        // Impede a página de recarregar ao clicar no botão
        evento.preventDefault(); 
        
        // Pega todos os dados preenchidos
        const formData = new FormData(formulario);
        
        // Monta o "pacote" JSON que a API está esperando
        const dados = {
            usuario: formData.get('usuario'),
            senha: formData.get('senha'),
            nivel: formData.get('nivel'),
            unidade: formData.get('unidade')
        };

        try {
            // Faz a chamada para a Rota da sua API
            // ATENÇÃO: Troque a URL abaixo pela URL real do seu Cloudflare Worker!
            const resposta = await fetch('https://api-estoque.whyguiih.workers.dev/usuario/cadastrar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(dados)
            });

            const resultado = await resposta.json();

            // Verifica se deu certo
            if (resultado.sucesso) {
                alert("Sucesso: " + resultado.mensagem);
                formulario.reset(); // Limpa os campos do formulário
            } else {
                alert("Atenção: " + resultado.mensagem);
            }
            
        } catch (erro) {
            alert("Erro na conexão com a API: " + erro.message);
        }
    });
</script>
</body>
</html>