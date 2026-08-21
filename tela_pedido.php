<?php
session_start();
require_once 'api.php';

if (!isset($_SESSION['logado'])) { header("Location: index.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] != "POST" || empty($_POST['produtos_selecionados'])) {
    header("Location: carrinho.php?msg=vazio");
    exit();
}

$produtos_selecionados = $_POST['produtos_selecionados'];
// CORREÇÃO: Capturamos também o array de quantidades enviado pelo carrinho
$quantidades = $_POST['quantidades'] ?? [];
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Finalizar pedido</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/logo_menor.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php
        include 'inc/sidebar.php';
    ?>

    <div class="main-content" style="display: flex; justify-content: center; align-items: center; padding: 0;">
        <div class="cadastro-container" style="max-width: 500px; padding: 40px;">
            <h1 style="color: #1a4b9f; margin-bottom: 25px; text-align: center; font-size: 28px;">Detalhes da Reserva</h1>
            
            <form action="fazer_pedido.php" method="POST" class="form-cadastro" onsubmit="document.getElementById('btn-confirmar').disabled = true; document.getElementById('btn-confirmar').innerText = 'Processando...';">
                
                <?php foreach ($produtos_selecionados as $nome_produto): ?>
                    <input type="hidden" name="produtos_selecionados[]" value="<?= htmlspecialchars($nome_produto) ?>">
                <?php endforeach; ?>
                
                <?php foreach ($quantidades as $nome => $qtd): ?>
                    <input type="hidden" name="quantidades[<?= htmlspecialchars($nome) ?>]" value="<?= htmlspecialchars($qtd) ?>">
                <?php endforeach; ?>
                
                <div class="form-group">
                    <label>Nome:*</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($_SESSION['usuario'] ?? '') ?>" required>
                </div>
                
                <div class="form-linha" style="align-items: flex-end;">
    <div class="form-group" style="flex: 1;">
        <label>Código do Pedido:*</label>
        <input type="text" name="codigo_pedido" id="codigo_pedido" required readonly style="background: #f0f0f0;">
    </div>
    <div class="form-group" style="width: auto;">
        <button type="button" onclick="gerarCodigoFrontend(3, 'codigo_pedido')" class="btn-primary" style="margin: 0; padding: 12px 20px; background: #e06c00;">Gerar Código</button>
    </div>
</div>
                <div class="form-group">
                    <label>Email:*</label>
                    <input type="email" name="email" placeholder="seu@email.com" required>
                </div>
                
                <div class="form-group">
    <label>Período de Reserva:*</label>
    <div style="display: flex; gap: 15px;">
        <input type="date" name="data_reserva_inicio" required style="flex: 1;" title="Data de retirada">
        <span style="display: flex; align-items: center; color: #1a4b9f; font-weight: bold;">até</span>
        <input type="date" name="data_reserva_fim" required style="flex: 1;" title="Data de devolução">
    </div>
</div>
                
                <div class="form-group">
                    <label>Prioridade:</label>
                    <select name="prioridade" required style="width: 100%; padding: 10px; border-radius: 12px; border: 2px solid rgba(26, 75, 159, 0.3); font-size: 16px; font-weight: bold; color: #1a4b9f; background-color: rgba(255, 255, 255, 0.5); text-align: center; cursor: pointer;">
                        <option value="Baixo">Baixo</option>
                        <option value="Intermediário">Intermediário</option>
                        <option value="Alto">Alto</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Motivo da prioridade:</label>
                    <textarea name="motivo" rows="2" placeholder="Explique brevemente o motivo" required></textarea>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 25px; width: 100%; justify-content: center;">
                    <a href="carrinho.php" class="btn-deletar" style="text-decoration: none; padding: 12px 30px; border-radius: 15px; text-align: center; display: flex; align-items: center;">Voltar</a>
                   
                    <button type="submit" id="btn-confirmar" class="btn-primary" style="padding: 12px 30px; font-size: 18px; border-radius: 15px; box-shadow: 0 5px 15px rgba(26, 75, 159, 0.3);">Confirmar Pedido</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    async function gerarCodigoFrontend(tipo, inputId) {
        try {
            // Chama o arquivo PHP gerador AJAX
            const resposta = await fetch(`gerar_codigo_ajax.php?tipo=${tipo}`);
            const codigo = await resposta.text();
            
            // Verifica se a resposta não está vazia ou retornou erro HTML do PHP
            if(codigo && !codigo.includes("<br") && !codigo.includes("<b>")) {
                document.getElementById(inputId).value = codigo.trim();
                alert("Código Gerado!");
            } else {
                console.error("Retorno do PHP:", codigo);
                alert("Falha no servidor ao gerar o código. Verifique se a unidade está na sessão.");
            }
        } catch (erro) {
            console.error("Erro na requisição:", erro);
            alert("Erro de conexão ao gerar código.");
        }
    }
    </script>
</body>
</html>
</body>
</html>