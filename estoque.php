<?php
session_start();

require_once 'api.php';

if (!isset($_SESSION['logado'])) { header("Location: index.php"); exit(); }

// Busca todos os produtos da API em vez da base de dados local
$produtos = chamarAPI('/produtos', 'GET');
if (!is_array($produtos)) $produtos = []; // Proteção caso a API não devolva nada
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Estoque</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/logo_menor.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php
        include 'inc/sidebar.php';
    ?>

    <div class="main-content">
        <div class="search-bar">
            <img src="img/lupa.png" alt="Pesquisar">
            <input type="text" id="input-pesquisa" placeholder="Pesquisar produtos">
        </div>

        <div class="produtos-grid">
            <?php foreach ($produtos as $p): ?>
                <a href="produto.php?id=<?= htmlspecialchars($p['id_estoque']) ?>" class="produto-card">
                    
                  <img 
    src="<?= !empty($p['foto']) ? htmlspecialchars($p['foto']) : 'img/logo.png' ?>" 
    onerror="this.onerror=null; this.src='img/logo.png';" 
    alt="Foto do produto">
                    
                    <h2><?= htmlspecialchars($p['nome']) ?></h2>
                    <p>Código: <?= htmlspecialchars($p['codigo']) ?></p>
                    <p>Quantidade: <?= htmlspecialchars($p['quant']) ?></p>
                </a>
            <?php endforeach; ?>
            
            <?php if(empty($produtos)): ?>
                <h2 style="color: #333;">Nenhum produto cadastrado.</h2>
            <?php endif; ?>
        </div>
    </div>
</body>
<script>
    // O equivalente ao DocumentListener: Ouve cada vez que o usuário digita algo
    document.getElementById('input-pesquisa').addEventListener('input', function() {
        // Pega o termo digitado e converte para minúsculas
        let termo = this.value.toLowerCase();
        
        // Seleciona todos os "cards" de produtos na tela
        let produtos = document.querySelectorAll('.produto-card');

        produtos.forEach(function(produto) {
            // Procura a tag <h2> dentro do card (que é onde está o nome do produto)
            let nomeProduto = produto.querySelector('h2').innerText.toLowerCase();
            
            // Lógica do Java (LIKE %termo%): Se o nome incluir o termo digitado, exibe. Senão, esconde.
            if (nomeProduto.includes(termo)) {
                produto.style.display = 'block'; // Mostra o card
            } else {
                produto.style.display = 'none';  // Esconde o card
            }
        });
    });
    </script>
</html>