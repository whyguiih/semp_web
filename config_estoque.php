<?php
session_start();
require_once 'api.php';

if (!isset($_SESSION['logado']) || ($_SESSION['nivel_conta'] !== '1' && $_SESSION['nivel_conta'] !== '2')) { 
    header("Location: estoque.php"); 
    exit(); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST['acao']) && $_POST['acao'] === 'atualizar') {
        $caminhoNoBanco = null;

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            if (!file_exists('uploads')) { mkdir('uploads', 0777, true); }
            $arquivoDestino = 'uploads/' . time() . '_' . $_FILES['foto']['name'];
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $arquivoDestino)) {
                $caminhoNoBanco = $arquivoDestino; 
            }
        }

        $dados_atualizacao = [
            'id_estoque'          => (int)$_POST['id_estoque'],
            'nome'                => $_POST['nome'],
            'codigo'              => $_POST['codigo'],
            'codigo_rfid'         => $_POST['codigo_fisico'] ?? '',
            'descricao'           => $_POST['descricao'],
            'quant'               => (int)$_POST['quant'],
            'uni_natal'           => $_POST['uni_natal'],
            'uni_atual'           => $_POST['uni_atual'],
            'cor'                 => $_POST['cor'],
            'marca_ref'           => $_POST['marca_ref'],
            'descricao_detalhada' => $_POST['descricao_detalhada']
        ];

        if ($caminhoNoBanco !== null) {
            $dados_atualizacao['foto'] = $caminhoNoBanco;
        } else {
            $dados_atualizacao['foto'] = $_POST['foto_atual'];
        }

        $respostaAPI = chamarAPI('/produto/atualizar', 'POST', $dados_atualizacao);
        
        if (is_array($respostaAPI) && isset($respostaAPI['sucesso']) && $respostaAPI['sucesso']) {
            $mensagem = "Produto atualizado com sucesso!";
        } else {
            $mensagem_erro = "Erro ao atualizar: " . ($respostaAPI['mensagem'] ?? 'Falha na API');
        }
    }
    
    elseif (isset($_POST['acao']) && $_POST['acao'] === 'deletar') {
        $dados_delecao = [
            'codigo' => $_POST['codigo_deletar']
        ];

        $respostaAPI = chamarAPI('/produto/deletar', 'POST', $dados_delecao);
        
        if (is_array($respostaAPI) && isset($respostaAPI['sucesso']) && $respostaAPI['sucesso']) {
            $mensagem = "Produto deletado com sucesso!";
        } else {
            $mensagem_erro = "Erro ao deletar: " . ($respostaAPI['mensagem'] ?? 'Falha na API');
        }
    }
}

$todos_produtos = chamarAPI('/produtos', 'GET');
if (!is_array($todos_produtos)) $todos_produtos = [];

$produtos_agrupados = [];
foreach ($todos_produtos as $p) {
    $nomeChave = mb_strtolower(trim($p['nome']), 'UTF-8');
    if (!isset($produtos_agrupados[$nomeChave])) {
        $produtos_agrupados[$nomeChave] = $p;
        $produtos_agrupados[$nomeChave]['quant_total'] = 0;
    }
    $produtos_agrupados[$nomeChave]['quant_total'] += 1;
}
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Configurações do Estoque</title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="icon" href="img/logo_menor.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php include 'inc/sidebar.php'; ?>
    
    <div class="main-content" style="display: flex; flex-direction: column; align-items: center; padding: 20px;">
        
        <div class="cadastro-container" style="max-width: 800px; width: 100%; margin-bottom: 30px;">
            <h1 style="color: #1a4b9f; margin-bottom: 15px; text-align: center; font-size: 28px;">Atualizar Produto</h1>
            
            <?php if(isset($mensagem)) echo "<h2 style='color: white; background-color: #27ae60; padding: 10px; border-radius: 12px; margin-bottom: 15px; text-align: center;'>$mensagem</h2>"; ?>
            <?php if(isset($mensagem_erro)) echo "<h2 style='color: white; background-color: #e74c3c; padding: 10px; border-radius: 12px; margin-bottom: 15px; text-align: center;'>$mensagem_erro</h2>"; ?>

            
            <div class="form-group" style="background: rgba(26,75,159,0.05); padding: 15px; border-radius: 10px; border: 1px solid #1a4b9f;">
                <label style="color: #1a4b9f; font-weight: bold;">Selecione o Produto para Editar:*</label>
                <select id="seletor_produto" onchange="preencherFormulario()" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; font-size: 16px;">
                    <option value="">-- Escolha um produto --</option>
                    <?php foreach($produtos_agrupados as $chave => $prod): ?>
                        <option value="<?= htmlspecialchars($chave) ?>"><?= htmlspecialchars($prod['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            
            <form method="POST" enctype="multipart/form-data" class="form-cadastro" id="form_atualizar" style="display: none; margin-top: 20px;">
                <input type="hidden" name="acao" value="atualizar">
                <input type="hidden" name="id_estoque" id="id_estoque">
                <input type="hidden" name="foto_atual" id="foto_atual">

                <div class="form-group">
                    <label>Nome do produto:*</label>
                    <input type="text" name="nome" id="nome" required>
                </div>
                
                <div class="form-linha">
                    <div class="form-group">
                        <label>Código da Referência:*</label>
                        <input type="text" name="codigo" id="codigo" required>
                    </div>
                    <div class="form-group">
                        <label>Código Físico (RFID):</label>
                        <input type="text" name="codigo_fisico" id="codigo_fisico">
                    </div>
                </div>

                <div class="form-linha">
                    <div class="form-group">
                        <label>Quantidade:*</label>
                        <input type="number" name="quant" id="quant" required min="1">
                    </div>
                    <div class="form-group">
                        <label>Unidade Natal:*</label>
                        <input type="text" name="uni_natal" id="uni_natal" required>
                    </div>
                    <div class="form-group">
                        <label>Unidade Atual:*</label>
                        <input type="text" name="uni_atual" id="uni_atual" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descrição:*</label>
                    <textarea name="descricao" id="descricao" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Descrição Detalhada:*</label>
                    <textarea name="descricao_detalhada" id="descricao_detalhada" rows="2"></textarea>
                </div>

                <div class="form-linha">
                    <div class="form-group">
                        <label>Cor:</label>
                        <input type="text" name="cor" id="cor">
                    </div>
                    <div class="form-group">
                        <label>Marca:*</label>
                        <input type="text" name="marca_ref" id="marca_ref" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Nova Foto (Deixe em branco para manter a atual):</label>
                    <input type="file" name="foto" accept="image/png, image/jpeg" class="file-input">
                </div>
                
                <button type="submit" class="btn-primary btn-salvar" style="width: 100%;">Atualizar Produto</button>
            </form>
        </div>

        
        <div class="cadastro-container" style="max-width: 800px; width: 100%; border-top: 5px solid #e74c3c;">
            <h2 style="color: #e74c3c; margin-bottom: 15px; text-align: center;">Zona de Perigo: Excluir Produto</h2>
            <form method="POST" onsubmit="return confirm('ATENÇÃO: Tem certeza que deseja excluir esta unidade permanentemente?');">
                <input type="hidden" name="acao" value="deletar">
                
                <div class="form-linha" style="align-items: flex-end;">
                    <div class="form-group" style="flex: 1;">
                        <label>Selecione o produto para deletar:*</label>
                        <select name="codigo_deletar" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; font-size: 16px;">
                            <option value="">-- Escolha um produto --</option>
                            <?php foreach($todos_produtos as $prod): ?>
                                <option value="<?= htmlspecialchars($prod['codigo']) ?>">
                                    <?= htmlspecialchars($prod['nome']) ?> (Ref: <?= htmlspecialchars($prod['codigo']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="width: auto;">
                        <button type="submit" style="background: #e74c3c; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer;">Deletar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <script>
        const produtosData = <?= json_encode($produtos_agrupados) ?>;

        function preencherFormulario() {
            const seletor = document.getElementById('seletor_produto').value;
            const form = document.getElementById('form_atualizar');

            if (seletor && produtosData[seletor]) {
                const p = produtosData[seletor];
                
                form.style.display = 'block';

                document.getElementById('id_estoque').value = p.id_estoque || '';
                document.getElementById('foto_atual').value = p.foto || '';
                document.getElementById('nome').value = p.nome || '';
                document.getElementById('codigo').value = p.codigo || '';
                document.getElementById('codigo_fisico').value = p.codigo_rfid || '';
                document.getElementById('descricao').value = p.descricao || '';
                document.getElementById('quant').value = p.quant_total || 1;
                document.getElementById('uni_natal').value = p.uni_natal || '';
                document.getElementById('uni_atual').value = p.uni_intermediarias || p.uni_atual || '';
                document.getElementById('cor').value = p.cor || '';
                document.getElementById('marca_ref').value = p.marca_ref || '';
                document.getElementById('descricao_detalhada').value = p.descricao_detalhada || '';
                
            } else {
                form.style.display = 'none';
            }
        }
    </script>
</body>
</html>