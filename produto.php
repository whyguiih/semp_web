<?php
session_start();
require_once 'api.php';

if (!isset($_GET['id']) || !isset($_SESSION['logado'])) {
    header("Location: estoque.php"); exit();
}

$id_produto = $_GET['id'];

$todos_produtos = chamarAPI('/produtos', 'GET');
$produto = null;

// Encontra a unidade específica clicada para carregar os metadados (Foto, descrição, etc)
foreach ($todos_produtos as $p) {
    if ($p['id_estoque'] == $id_produto) { $produto = $p; break; }
}

if (!$produto || isset($produto['erro'])) {
    die("Produto não encontrado!");
}

// Calcula o estoque FÍSICO e o estoque REAL somando todas as unidades idênticas
$quantidade_total_disponivel = 0;
$estoque_real = 0;

foreach ($todos_produtos as $p) {
    if (strcasecmp(trim($p['nome']), trim($produto['nome'])) === 0) {
        // Soma 1 para cada item encontrado (Total Físico)
        $quantidade_total_disponivel++;
        
        // Verifica o estoque real de cada unidade (1 se disponível, 0 se emprestado/bloqueado)
        // Se a API não devolver 'estoque_real' individual, assumimos que está disponível (1)
        $valor_real_desta_unidade = $p['estoque_real'] ?? 1;
        
        // Soma ao montante do estoque dinâmico
        $estoque_real += $valor_real_desta_unidade;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Produto - <?= htmlspecialchars($produto['nome']) ?></title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="icon" href="img/logo_menor.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php include 'inc/sidebar.php'; ?>
    
<div class="main-content" style="display: flex; justify-content: center; align-items: center; padding: 20px;">
    
    <div style="background-color: rgba(229, 231, 255, 0.85); backdrop-filter: blur(10px); border-radius: 20px; padding: 40px; width: 100%; max-width: 950px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);">
        
        <div style="display: flex; gap: 40px; flex-wrap: wrap; margin-bottom: 10px;">
            
            <div style="width: 320px; display: flex; flex-direction: column; align-items: center; text-align: center; flex-shrink: 0;">
                <img src="<?= !empty($produto['foto']) ? htmlspecialchars($produto['foto']) : 'img/logo.png' ?>" 
                     onerror="this.onerror=null; this.src='img/logo.png';" 
                     alt="Foto do produto"
                     style="width: 100%; height: 280px; object-fit: contain; background: white; padding: 15px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 20px;">
                
                <h1 style="color: #1a4b9f; font-size: 28px; line-height: 1.2; text-transform: uppercase; margin-bottom: 5px;">
                    <?= htmlspecialchars($produto['nome']) ?>
                </h1>
                <h3 style="color: #555; font-size: 18px; font-weight: bold;">
                    Ref: <?= htmlspecialchars($produto['codigo']) ?>
                </h3>
            </div>
            
            <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; justify-content: center;">
                
                <h3 style="color: #1a4b9f; font-size: 22px; border-bottom: 2px solid rgba(26, 75, 159, 0.2); padding-bottom: 10px; margin-bottom: 20px;">Detalhes do produto</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <p style="font-size: 16px; color: #333;"><strong>Unidade natal:</strong><br><?= htmlspecialchars($produto['uni_natal'] ?? 'Não informada') ?></p>
                    <p style="font-size: 16px; color: #333;"><strong>Unidade atual:</strong><br><?= htmlspecialchars($produto['uni_atual'] ?? 'Não informada') ?></p>
                    <p style="font-size: 16px; color: #333;"><strong>Marca:</strong><br><?= htmlspecialchars($produto['marca_ref'] ?? 'Não informada') ?></p>
                    <p style="font-size: 16px; color: #333;"><strong>Cor:</strong><br><?= htmlspecialchars($produto['cor'] ?? 'Não informada') ?></p>
                    <p style="font-size: 16px; color: #333;"><strong>Código físico:</strong><br><?=htmlspecialchars($produto['codigo_rfid'] ?? 'Não informado') ?></p>
                    <p style="font-size: 16px; color: #333;"><strong>Altura:</strong><br><?= htmlspecialchars($produto['altura'] ?? 'Não informada') ?></p>
                    <p style="font-size: 16px; color: #333;"><strong>Comprimento:</strong><br><?=htmlspecialchars($produto['comprimento'] ?? 'Não informado') ?></p>
                    <p style="font-size: 16px; color: #333;"><strong>Status:</strong><br><?=htmlspecialchars($produto['codigo_rfid'] ?? 'Não informado') ?></p>
                </div>

                <p style="font-size: 18px; color: #1a4b9f; margin-bottom: 15px;"><strong>Total físico na base:</strong> <span style="font-size: 22px; font-weight: bold; background: #e06c00; color: white; padding: 2px 10px; border-radius: 8px;"><?= $quantidade_total_disponivel ?></span></p>
                
                <div style="background-color: rgba(26, 75, 159, 0.05); padding: 15px; border-radius: 12px; border: 1px solid rgba(26, 75, 159, 0.1);">
                    <p style="font-size: 16px; color: #333; margin-bottom: 8px;"><strong>Descrição:</strong> <?= htmlspecialchars($produto['descricao'] ?? 'Não informada') ?></p>
                    <p style="font-size: 15px; color: #555; line-height: 1.5; margin-bottom: 15px;"><strong>Detalhes:</strong> <?= nl2br(htmlspecialchars($produto['descricao_detalhada'] ?? 'Não informada')) ?></p>
                    
                    <?php if ($estoque_real > 0): ?>
                        <p style="color: white; background-color: #27ae60; padding: 10px; border-radius: 8px; font-weight: bold; text-align: center; margin: 0;">
                            STATUS: DISPONÍVEL (<?= $estoque_real ?> unidades agora)
                        </p>
                    <?php else: ?>
                        <p style="color: white; background-color: #c0392b; padding: 10px; border-radius: 8px; font-weight: bold; text-align: center; margin: 0;">
                            STATUS: INDISPONÍVEL HOJE
                        </p>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <form action="acao_carrinho.php" method="POST" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; border-top: 2px solid rgba(26, 75, 159, 0.2); padding-top: 25px; margin-top: 20px;">
            
            <input type="hidden" name="nome_produto" value="<?= htmlspecialchars($produto['nome']) ?>">
            
            <!-- Botão travado caso o estoque real seja 0 -->
            <button type="submit" <?= $estoque_real <= 0 ? 'disabled' : '' ?> style="background-color: <?= $estoque_real <= 0 ? '#999' : '#1a4b9f' ?>; color: white; border: none; padding: 15px 35px; font-size: 18px; font-weight: bold; border-radius: 15px; cursor: <?= $estoque_real <= 0 ? 'not-allowed' : 'pointer' ?>; transition: 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                Adicionar ao Carrinho
            </button>
            
            <div style="display: flex; align-items: center; background-color: <?= $estoque_real <= 0 ? '#999' : '#1a4b9f' ?>; border-radius: 15px; overflow: hidden; height: 50px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <button type="button" onclick="mudarQtd(-1)" <?= $estoque_real <= 0 ? 'disabled' : '' ?> style="background: transparent; border: none; color: white; font-size: 24px; font-weight: bold; width: 45px; height: 100%; cursor: pointer;">-</button>
                
                <!-- Input atualizado para ter como MÁXIMO o estoque_real e ficar desativado se for 0 -->
                <input type="number" id="quantidade_tela" name="quantidade" value="<?= $estoque_real > 0 ? 1 : 0 ?>" min="1" max="<?= $estoque_real ?>" onblur="validarQtd()" <?= $estoque_real <= 0 ? 'disabled' : '' ?> style="width: 60px; height: 100%; border: none; text-align: center; font-size: 18px; font-weight: bold; color: #1a4b9f; outline: none;">
                
                <button type="button" onclick="mudarQtd(1)" <?= $estoque_real <= 0 ? 'disabled' : '' ?> style="background: transparent; border: none; color: white; font-size: 24px; font-weight: bold; width: 45px; height: 100%; cursor: pointer;">+</button>
            </div>
        </form>

    </div>
</div>

    <script>
    function mudarQtd(valor) {
        var campo = document.getElementById('quantidade_tela');
        if (campo.disabled) return;
        
        var valorAtual = parseInt(campo.value) || 1;
        var valorNovo = valorAtual + valor;
        var min = parseInt(campo.getAttribute('min')) || 1;
        var max = parseInt(campo.getAttribute('max')) || 1;

        if (valorNovo >= min && valorNovo <= max) {
            campo.value = valorNovo;
        }
    }

    function validarQtd() {
        var campo = document.getElementById('quantidade_tela');
        if (campo.disabled) return;
        
        var valorDigitado = parseInt(campo.value);
        var min = parseInt(campo.getAttribute('min')) || 1;
        var max = parseInt(campo.getAttribute('max')) || 1;

        if (isNaN(valorDigitado) || valorDigitado < min) {
            campo.value = min;
        } 
        else if (valorDigitado > max) {
            campo.value = max;
        } 
        else {
            campo.value = valorDigitado;
        }
    }
    </script>
</body>
</html>