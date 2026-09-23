<?php
session_start();
require_once 'api.php';

if (!isset($_SESSION['logado']) || $_SESSION['nivel_conta'] !== '3') {
    header("Location: estoque.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $dados = [
        'nome_unidade'  => $_POST['nome_unidade'],
        'estado'        => $_POST['estado'],
        'regiao'        => $_POST['regiao'],
        'identificacao' => $_POST['identificacao']
    ];

    $respostaAPI = chamarAPI('/unidade/cadastrar', 'POST', $dados);

    if (is_array($respostaAPI) && isset($respostaAPI['sucesso']) && $respostaAPI['sucesso'] === true) {
        $mensagem = $respostaAPI['mensagem'];
    } elseif (is_array($respostaAPI) && isset($respostaAPI['erro'])) {
        $mensagem_erro = "A API recusou: " . $respostaAPI['erro'];
    } elseif (is_array($respostaAPI) && isset($respostaAPI['mensagem'])) {
        $mensagem_erro = $respostaAPI['mensagem'];
    } else {
        $mensagem_erro = "Erro de conexão: A API não respondeu corretamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Nova Unidade</title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="icon" href="img/logo_menor.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php include 'inc/sidebar.php'; ?>
    
    <div class="main-content" style="display: flex; justify-content: center; align-items: center; padding: 0;">
        
        <div class="cadastro-container" style="max-width: 600px; width: 100%;">
            <h1 style="color: #1a4b9f; margin-bottom: 15px; text-align: center; font-size: 28px;">Cadastrar Unidade / Adendo</h1>
            
            
            <?php if(isset($mensagem)) echo "<h2 style='color: #ffffff; background-color: #27ae60; padding: 10px 20px; border-radius: 12px; margin-bottom: 15px; text-align: center; font-size: 18px;'>$mensagem</h2>"; ?>
            <?php if(isset($mensagem_erro)) echo "<h2 style='color: #ffffff; background-color: #ef5e31; padding: 10px 20px; border-radius: 12px; margin-bottom: 15px; text-align: center; font-size: 18px;'>$mensagem_erro</h2>"; ?>
            
            <form method="POST" class="form-cadastro">
                
                <div class="form-group">
                    <label>Nome da Unidade:*</label>
                    <input type="text" name="nome_unidade" placeholder="Ex: SENAI Carlos Barbosa" required>
                </div>
                
                <div class="form-linha">
                    <div class="form-group">
                        <label>Identificação:*</label>
                        <select name="identificacao" required style="width: 100%; padding: 10px; border-radius: 12px; border: 2px solid rgba(26, 75, 159, 0.3); font-size: 16px; color: #1a4b9f; outline: none; cursor: pointer;">
                            <option value="Unidade">Unidade</option>
                            <option value="Adendo">Adendo</option>
                        </select>
                    </div>
                </div>

                <div class="form-linha">
                    <div class="form-group">
                        <label>Estado:*</label>
                        <select name="estado" id="estado" required onchange="atualizarRegioes()" style="width: 100%; padding: 10px; border-radius: 12px; border: 2px solid rgba(26, 75, 159, 0.3); font-size: 16px; color: #1a4b9f; outline: none; cursor: pointer;">
                            <option value="">Selecione um Estado</option>
                            
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Região:*</label>
                        <select name="regiao" id="regiao" required style="width: 100%; padding: 10px; border-radius: 12px; border: 2px solid rgba(26, 75, 159, 0.3); font-size: 16px; color: #1a4b9f; outline: none; cursor: pointer;">
                            
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; width: 100%; margin-top: 20px;">
                    <button type="submit" class="btn-primary btn-salvar" style="flex: 1; margin-top: 0; border-radius: 15px; font-size: 18px; padding: 15px;">Cadastrar Unidade</button>
                </div>
            </form>
        </div>
    </div>

    
    <script>
        const listaEstados = [
            "Rio Grande do Sul", "Santa Catarina", "Paraná", "São Paulo", "Rio de Janeiro",
            "Espiríto Santo", "Minas Gerais", "Goiás", "Mato Grosso", "Mato Grosso do Sul",
            "Rio Grande do Norte", "Acre", "Amapá", "Amazonas", "Pará", "Rondônia",
            "Roraima", "Tocantins", "Alagoas", "Bahia", "Ceará", "Maranhão",
            "Paraíba", "Pernambuco", "Piauí", "Sergipe"
        ];

        const listaRegioesRS = [
            "Metropolitana", "Metropolitana 3", "Serra 1", "Metropolitana 2", "Vale dos Sinos 2",
            "Noroeste 2", "Noroeste 1", "Vale dos Sino 1", "Central", "Vale do Rio Pardo",
            "Serra 3", "Vale do Taquari 2", "Norte 1", "Sul 1", "Vale dos Sinos 3",
            "Norte 2", "Vale do Taquari 1", "Serra 2", "Encosta da Serra", "Sul 2"
        ];

        const listaRegioesPadrao = ["Metropolitana"];

        window.onload = function() {
            const selectEstado = document.getElementById('estado');
            listaEstados.forEach(estado => {
                let option = document.createElement('option');
                option.value = estado;
                option.text = estado;
                selectEstado.appendChild(option);
            });
            atualizarRegioes();
        };

        function atualizarRegioes() {
            const estadoSelecionado = document.getElementById('estado').value;
            const selectRegiao = document.getElementById('regiao');
            
            selectRegiao.innerHTML = '';

            let regioesParaMostrar = [];

            if (estadoSelecionado === "Rio Grande do Sul") {
                regioesParaMostrar = listaRegioesRS;
            } else if (estadoSelecionado !== "") {
                regioesParaMostrar = listaRegioesPadrao;
            }

            regioesParaMostrar.forEach(regiao => {
                let option = document.createElement('option');
                option.value = regiao;
                option.text = regiao;
                selectRegiao.appendChild(option);
            });
        }
    </script>
</body>
</html>