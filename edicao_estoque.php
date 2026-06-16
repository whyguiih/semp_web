<?php
session_start();
require_once 'api.php';

// Segurança: impede usuários não logados ou que não sejam nível 1 de acessar
if (!isset($_SESSION['logado']) || $_SESSION['nivel_conta'] != '1') { 
    header("Location: estoque.php"); 
    exit(); 
}

// Busca todos os produtos atuais do estoque para popular o select
$produtos = chamarAPI('/produtos', 'GET');
if (!is_array($produtos)) {
    $produtos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Editar Estoque</title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="icon" href="img/logo_menor.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php
        include 'inc/sidebar.php';
    ?>
    
    <div class="main-content" style="display: flex; justify-content: center; align-items: center; padding: 0;">
        
        <div class="cadastro-container">
            <h1 style="color: #1a4b9f; margin-bottom: 15px; text-align: center; font-size: 28px;">Editar produto do estoque</h1>
            
            <form id="form-edicao" class="form-cadastro">
                
                <div class="form-group">
                    <label>Selecione o produto para alterar:</label>
                    <select id="select-produto" required style="width: 100%; padding: 10px; border-radius: 12px; border: 2px solid rgba(26, 75, 159, 0.3); font-size: 16px; font-weight: bold; color: #1a4b9f; background-color: rgba(255, 255, 255, 0.5); cursor: pointer;">
                        <option value="">-- Escolha um produto da lista --</option>
                        <?php foreach ($produtos as $p): ?>
                            <option value="<?= htmlspecialchars($p['id_estoque']) ?>"><?= htmlspecialchars($p['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-linha">
                    <div class="form-group">
                        <label>Nome do produto:</label>
                        <input type="text" id="input-nome" placeholder="Selecione um produto primeiro" required disabled>
                    </div>
                    <div class="form-group">
                        <label>Código do produto:</label>
                        <input type="text" id="input-codigo" placeholder="Ex: 3213" required disabled>
                    </div>
                </div>
                
                <div class="form-linha">
                    <div class="form-group">
                        <label>Quantidade disponível:</label>
                        <input type="number" id="input-quant" placeholder="0" required min="1" disabled>
                    </div>
                    <div class="form-group">
                        <label>Cor do produto:</label>
                        <input type="text" id="input-cor" placeholder="Ex: Vermelho" disabled>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descrição do produto:</label>
                    <textarea id="input-descricao" rows="2" placeholder="Adicione informações sobre o produto" disabled></textarea>
                </div>

                <div class="form-group">
    <label>URL ou Caminho da Foto:</label>
    <input type="text" id="input-foto" placeholder="Ex: uploads/foto.png ou https://..." disabled>
</div>
                <div class="form-linha">
                    <div class="form-group">
                        <label>Marca de referência:</label>
                        <input type="text" id="input-marca" placeholder="Ex: Tramontina" required disabled>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; width: 100%; margin-top: 15px;">
                    <button type="submit" id="btn-salvar" class="btn-primary" style="flex: 1; height: 50px; padding: 0; font-size: 16px; border-radius: 12px; font-weight: bold; cursor: pointer; border: none;" disabled>Salvar alterações</button>
                    
                    <button type="button" id="btn-excluir" class="btn-primary" style="flex: 1; height: 50px; padding: 0; font-size: 16px; border-radius: 12px; font-weight: bold; cursor: pointer; border: none; background-color: #ef5e31; color: white;" disabled>Excluir Produto</button>
                </div>
            
            </form>
        </div>

    </div>

    <script>
        const produtosEstoque = <?= json_encode($produtos) ?>;
        
        const selectProduto = document.getElementById('select-produto');
        const formEdicao = document.getElementById('form-edicao');
        const btnSalvar = document.getElementById('btn-salvar');
        const btnExcluir = document.getElementById('btn-excluir'); 
        
        const campos = {
            nome: document.getElementById('input-nome'),
            codigo: document.getElementById('input-codigo'),
            quant: document.getElementById('input-quant'),
            cor: document.getElementById('input-cor'),
            descricao: document.getElementById('input-descricao'),
            marca_ref: document.getElementById('input-marca'),
            foto: document.getElementById('input-foto')
        };

        selectProduto.addEventListener('change', function() {
            const idSelecionado = this.value;
            
            if (!idSelecionado) {
                Object.values(campos).forEach(input => {
                    input.value = '';
                    input.disabled = true;
                });
                btnSalvar.disabled = true;
                btnExcluir.disabled = true; 
                return;
            }

            const produto = produtosEstoque.find(p => p.id_estoque == idSelecionado);
            
            if (produto) {
                campos.nome.value = produto.nome || '';
                campos.codigo.value = produto.codigo || '';
                campos.quant.value = produto.quant || 0;
                campos.cor.value = produto.cor || '';
                campos.descricao.value = produto.descricao || '';
                campos.marca_ref.value = produto.marca_ref || '';
                campos.foto.value = produto.foto || '';
                
                Object.values(campos).forEach(input => input.disabled = false);
                btnSalvar.disabled = false;
                btnExcluir.disabled = false; 
            }
        });

        // ==========================================
        // LÓGICA DE SALVAR O PRODUTO (CORRIGIDA)
        // ==========================================
        formEdicao.addEventListener('submit', async function(evento) {
            evento.preventDefault();
            
            const idProduto = selectProduto.value;
            if(!idProduto) return;

            btnSalvar.innerText = "Atualizando dados...";
            btnSalvar.disabled = true;
            btnExcluir.disabled = true;

           // Apenas adicione "foto" no final da lista permitida
const colunasPermitidas = ["nome", "codigo", "descricao", "quant", "cor", "marca_ref", "foto"];
            let houveErro = false;

            try {
                for (const coluna of colunasPermitidas) {
                    let valorAtualizado = campos[coluna].value;
                    
                    if(coluna === 'quant') {
                        valorAtualizado = parseInt(valorAtualizado) || 0;
                    }

                    const resposta = await fetch('https://api-estoque.whyguiih.workers.dev/produto/atualizar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: parseInt(idProduto),
                            coluna: coluna,
                            valor: valorAtualizado
                        })
                    });

                    // ESSAS LINHAS FORAM RESTAURADAS PARA EVITAR O ERRO DE SINTAXE
                    const resultado = await resposta.json();
                    if (!resultado.sucesso) {
                        houveErro = true;
                    }
                }

                if (!houveErro) {
                    alert("Sucesso: Produto atualizado no estoque!");
                    window.location.href = "estoque.php";
                } else {
                    alert("Atenção: Algumas colunas podem não ter sido salvas.");
                    btnSalvar.innerText = "Salvar alterações";
                    btnSalvar.disabled = false;
                    btnExcluir.disabled = false;
                }

            } catch (erroNet) {
                alert("Erro ao conectar com a API: " + erroNet.message);
                btnSalvar.innerText = "Salvar alterações";
                btnSalvar.disabled = false;
                btnExcluir.disabled = false;
            }
        });

        // ==========================================
        // LÓGICA DE EXCLUSÃO DO PRODUTO 
        // ==========================================
        btnExcluir.addEventListener('click', async function() {
            const codigoProduto = campos.codigo.value;
            
            if(!codigoProduto) return;

            const confirmar = confirm("🚨 ATENÇÃO: Tem certeza que deseja EXCLUIR este produto definitivamente?");
            if(!confirmar) return;

            btnExcluir.innerText = "Excluindo...";
            btnExcluir.disabled = true;
            btnSalvar.disabled = true;

            try {
                const resposta = await fetch('https://api-estoque.whyguiih.workers.dev/produto/deletar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        codigo: codigoProduto
                    })
                });

                const resultado = await resposta.json();
                
                if (resultado.sucesso) {
                    alert("Sucesso: Produto excluído definitivamente do estoque!");
                    window.location.href = "estoque.php"; 
                } else {
                    alert("Erro ao excluir: " + (resultado.mensagem || "Erro desconhecido"));
                    btnExcluir.innerText = "Excluir Produto";
                    btnExcluir.disabled = false;
                    btnSalvar.disabled = false;
                }

            } catch (erroNet) {
                alert("Erro ao conectar com a API: " + erroNet.message);
                btnExcluir.innerText = "Excluir Produto";
                btnExcluir.disabled = false;
                btnSalvar.disabled = false;
            }
        });
    </script>
</body>
</html>