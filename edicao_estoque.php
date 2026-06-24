<?php
session_start();
require_once 'api.php';

// Controle de acesso seguro para administradores e moderadores
if (!isset($_SESSION['logado']) || ($_SESSION['nivel_conta'] !== '1' && $_SESSION['nivel_conta'] !== '2')) { 
    header("Location: estoque.php"); 
    exit(); 
}

// Processa o incremento de estoque replicando metadados
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao']) && $_POST['acao'] === 'adicionar_unidades') {
    $nome_produto = $_POST['nome_produto'];
    $quantidade_novas_unidades = (int)$_POST['quant_adicionar'];

    if ($quantidade_novas_unidades > 0) {
        // Busca todos os itens para encontrar o produto modelo que servirá de espelho
        $todos_produtos = chamarAPI('/produtos', 'GET');
        if (!is_array($todos_produtos)) $todos_produtos = [];

        $produto_modelo = null;
        foreach ($todos_produtos as $p) {
            if (strcasecmp(trim($p['nome']), trim($nome_produto)) === 0) {
                $produto_modelo = $p;
                break;
            }
        }

        if ($produto_modelo) {
            $houveErro = false;
            $unidadesCadastradas = 0;

            // Insere cada nova unidade de forma individual no ecossistema
            for ($i = 0; $i < $quantidade_novas_unidades; $i++) {
                $dados = [
                    'nome' => $produto_modelo['nome'],
                    'codigo' => gerarCodigoSemp($_SESSION['unidade'], 2), // Código autogerado XXXXX-XXXXXXXXXX
                    'descricao' => $produto_modelo['descricao'],
                    'quant' => 1, // Mantém o padrão estrutural de 1 linha por item físico
                    'uni_natal' => $produto_modelo['uni_natal'],
                    'marca_ref' => $produto_modelo['marca_ref'],
                    'cor' => $produto_modelo['cor'],
                    'descricao_detalhada' => $produto_modelo['descricao_detalhada'],
                    'foto' => $produto_modelo['foto']
                ];

                $respostaAPI = chamarAPI('/produto/cadastrar', 'POST', $dados);

                if (is_array($respostaAPI) && isset($respostaAPI['erro'])) {
                    $mensagem_erro = "Erro ao registrar a unidade " . ($i + 1) . ": " . $respostaAPI['erro'];
                    $houveErro = true;
                    break;
                } elseif ($respostaAPI === null) {
                    $mensagem_erro = "Erro crítico de conexão com o servidor na unidade " . ($i + 1) . ".";
                    $houveErro = true;
                    break;
                } else {
                    $unidadesCadastradas++;
                }
            }

            if (!$houveErro) {
                $mensagem = "Sucesso: Mais $unidadesCadastradas unidades de '" . htmlspecialchars($produto_modelo['nome']) . "' foram adicionadas com códigos individuais!";
            }
        } else {
            $mensagem_erro = "Não foi possível localizar o item de referência no banco.";
        }
    } else {
        $mensagem_erro = "Por favor, selecione uma quantidade válida maior que zero.";
    }
}

// Busca a listagem completa atualizada para renderizar na tabela
$produtos_individuais = chamarAPI('/produtos', 'GET');
if (!is_array($produtos_individuais)) $produtos_individuais = [];

// Consolida os registros idênticos pelo nome para exibição limpa
$produtos_agrupados = [];
foreach ($produtos_individuais as $p) {
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
    <title>Gerenciar Unidades do Estoque</title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="icon" href="img/logo_menor.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .tabela-estoque { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .tabela-estoque th, .tabela-estoque td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .tabela-estoque th { background-color: #1a4b9f; color: white; }
        .img-mini { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .input-qtd-add { width: 70px; padding: 6px; border: 1px solid #ccc; border-radius: 6px; text-align: center; }
        .btn-add-inline { background-color: #e06c00; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn-add-inline:hover { background-color: #c45f00; }
    </style>
</head>

<body>
    <?php include 'inc/sidebar.php'; ?>
    
    <div class="main-content" style="padding: 30px; display: flex; flex-direction: column; align-items: center;">
        
        <div class="cadastro-container" style="width: 100%; max-width: 1100px; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <h1 style="color: #1a4b9f; margin-bottom: 15px; text-align: center; font-size: 28px;">Gerenciar Unidades do Estoque</h1>
            
            <?php if(isset($mensagem)) echo "<h2 style='color: #ffffff; background-color: rgba(26, 75, 159, 0.8); padding: 10px 20px; border-radius: 12px; margin-bottom: 15px; text-align: center; width: 100%; font-size: 18px;'>$mensagem</h2>"; ?>
            <?php if(isset($mensagem_erro)) echo "<h2 style='color: #ffffff; background-color: #ef5e31; padding: 10px 20px; border-radius: 12px; margin-bottom: 15px; text-align: center; width: 100%; font-size: 18px;'>$mensagem_erro</h2>"; ?>

            <table class="tabela-estoque">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nome do Produto</th>
                        <th>Marca / Ref.</th>
                        <th>Cor</th>
                        <th style="text-align: center;">Qtd Atual</th>
                        <th style="text-align: center;">Adicionar Unidades (Código Único)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($produtos_agrupados)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #666;">Nenhum produto encontrado no estoque.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($produtos_agrupados as $prod): ?>
                            <tr>
                                <td>
                                    <?php if(!empty($prod['foto'])): ?>
                                        <img src="<?= htmlspecialchars($prod['foto']) ?>" class="img-mini" alt="Produto">
                                    <?php else: ?>
                                        <img src="img/imagem.png" class="img-mini" alt="Sem Foto">
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight: bold; color: #1a4b9f;"><?= htmlspecialchars($prod['nome']) ?></td>
                                <td><?= htmlspecialchars($prod['marca_ref']) ?></td>
                                <td><?= htmlspecialchars($prod['cor'] ?: '-') ?></td>
                                <td style="text-align: center; font-weight: bold; font-size: 16px;">
                                    <span style="background: #1a4b9f; color: white; padding: 3px 10px; border-radius: 6px;">
                                        <?= $prod['quant_total'] ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <form method="POST" style="display: flex; gap: 10px; justify-content: center; align-items: center;">
                                        <input type="hidden" name="acao" value="add_unidades_rapido">
                                        <input type="hidden" name="acao" value="adicionar_unidades">
                                        <input type="hidden" name="nome_produto" value="<?= htmlspecialchars($prod['nome']) ?>">
                                        <input type="number" name="quant_adicionar" class="input-qtd-add" value="1" min="1" required>
                                        <button type="submit" class="btn-add-inline">+ Injetar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 25px; display: flex; justify-content: flex-end;">
                <a href="cadastro_produto.php" class="btn-primary" style="text-decoration: none; padding: 10px 25px; font-size: 16px; border-radius: 10px; background: #1a4b9f; color: white; font-weight: bold;">Cadastrar Novo Item</a>
            </div>
        </div>

    </div>
</body>
</html>