<?php
session_start();

require_once 'api.php';


// 1. Criamos uma variável de controle (falsa por padrão)
$exibirLogicaToast = false;
$totalAtual = 0;

// 2. VERIFICAÇÃO DE SEGURANÇA: Só entra no IF se o usuário for nível 1 ou nível 2
// (Substitua 'status' pelo nome da variável que você usa para guardar o nível no login)
if (isset($_SESSION['nivel_conta']) && ($_SESSION['nivel_conta'] == 1 || $_SESSION['nivel_conta'] == 2)) {
    
    $exibirLogicaToast = true; // Ativa a permissão para renderizar o Toastify

    // A API só será chamada se o usuário tiver a permissão acima
    $pedidosPendentes = chamarAPI('/pedidos/pendentes?unidade=' . urlencode($_SESSION['unidade']), 'GET');
    
    if (!is_array($pedidosPendentes) || isset($pedidosPendentes['erro']) || isset($pedidosPendentes['mensagem'])) {
        $pedidosPendentes = [];
    }
    
    $totalAtual = count($pedidosPendentes);
}


if (!isset($_SESSION['logado'])) { header("Location: index.php"); exit(); }

$produtos_individuais = chamarAPI('/produtos', 'GET');
if (!is_array($produtos_individuais)) $produtos_individuais = [];

// Agrupa os produtos pelo nome para exibição consolidada na vitrine
$produtos = [];
foreach ($produtos_individuais as $p) {
    $nomeChave = mb_strtolower(trim($p['nome']), 'UTF-8');
    
    if (!isset($produtos[$nomeChave])) {
        // Usa a primeira unidade encontrada como modelo visual do card
        $produtos[$nomeChave] = $p;
        $produtos[$nomeChave]['quant'] = 0;
    }
    // Soma 1 para cada registro individual com o mesmo nome encontrado no estoque
    $produtos[$nomeChave]['quant'] += 1;
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Estoque</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/logo_menor.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
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

    <?php if ($exibirLogicaToast): ?>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const totalAtual = <?= $totalAtual ?>;
           const usuarioAtual = "<?= $_SESSION['usuario'] ?>";
const chaveStorage = 'pedidos_vistos_' + usuarioAtual;

// Puxa o valor salvo específico para este usuário
const pedidosVistos = parseInt(localStorage.getItem(chaveStorage)) || 0;
const novosPedidos = totalAtual - pedidosVistos;

// ... resto do if (novosPedidos > 0) igual ...

// Dentro do IF, na hora de salvar a trava da sessão, também use o nome:

            if (novosPedidos > 0 && !sessionStorage.getItem('aviso_inicial_exibido')) {
                
                let mensagem = novosPedidos === 1 
                    ? "Você tem 1 novo pedido pendente aguardando verificação." 
                    : `Você tem ${novosPedidos} novos pedidos pendentes aguardando verificação.`;

                Toastify({
                    text: mensagem,
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #1a4b9f, #e06c00)",
                        color: "#ffffff",
                        borderRadius: "8px",
                        fontWeight: "bold"
                    }
                }).showToast();

                sessionStorage.setItem('aviso_inicial_' + usuarioAtual, 'true');
            }
        });
        </script>
    <?php endif; ?>

</body>

<?php if (isset($_SESSION['nivel_conta']) && $_SESSION['nivel_conta'] == '0'): 
    // Busca os pedidos do usuário comum logado (tabela tb_emprestimo)
    $meusPedidos = chamarAPI('/pedidos?usuario=' . urlencode($_SESSION['usuario']) . '&nivel=0', 'GET');
    if (!is_array($meusPedidos)) $meusPedidos = [];
?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const meusPedidos = <?= json_encode($meusPedidos) ?>;
    const usuarioAtual = "<?= $_SESSION['usuario'] ?>";
    const chaveNotificados = 'status_notificados_' + usuarioAtual;
    
    let notificados = {};
    try { notificados = JSON.parse(localStorage.getItem(chaveNotificados)) || {}; } catch (e) {}

    let houveMudanca = false;
    const hoje = new Date().toISOString().split('T')[0];

    meusPedidos.forEach(pedido => {
        const id = String(pedido.id_emprestimo);
        
        // Como tb_emprestimo não tem data_entrada, usamos a data_postagem (ou data_reserva)
        // Pegamos só a parte da data "YYYY-MM-DD" cortando a hora
        let dataPostagem = "";
        if (pedido.data_postagem) {
            dataPostagem = pedido.data_postagem.split(' ')[0];
        }

        // Se o status for "Aprovado" (1) e a postagem foi feita hoje
        if (String(pedido.aprovacao) === "1" && dataPostagem === hoje && String(notificados[id]) !== "avisado_hoje") {
    
            let msg = `🎉 O seu pedido de "${pedido.nome_produto}" foi aprovado e postado hoje!`;
                    
            Toastify({
                text: msg,
                duration: 8000,
                close: true,
                gravity: "top",
                position: "right",
                style: { 
                    background: "linear-gradient(to right, #00b09b, #96c93d)", 
                    color: "#ffffff", 
                    fontWeight: "bold", 
                    borderRadius: "8px" 
                }
            }).showToast();

            notificados[id] = "avisado_hoje";
            houveMudanca = true;
        }
    });

    if (houveMudanca) {
        localStorage.setItem(chaveNotificados, JSON.stringify(notificados));
    }
});
</script>
<?php endif; ?>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <?php 
    // =========================================================================
    // LÓGICA DE NOTIFICAÇÕES PARA USUÁRIOS NÍVEL 2 (GERENTES)
    // =========================================================================
    if (isset($_SESSION['nivel_conta']) && $_SESSION['nivel_conta'] == '2'): 
        
        // Busca TODOS os rastreios para podermos analisar o histórico (Atrasos e Retornos)
        $todos_rastreios = chamarAPI('/rastreio/todos', 'GET');
        if (!is_array($todos_rastreios)) $todos_rastreios = [];
    ?>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const rastreios = <?= json_encode($todos_rastreios) ?>;
        const minhaUnidade = "<?= $_SESSION['unidade'] ?>";
        const hoje = new Date().toISOString().split('T')[0]; // Pega YYYY-MM-DD
        
        // 1. Puxa a memória do navegador para não repetir Toasts (Spam)
        let vistos = {};
        try { vistos = JSON.parse(localStorage.getItem('notificacoes_rastreio')) || {}; } catch(e) {}

        // Função para formatar a data de 2024-10-25 para 25/10/2024
        const formataDataBR = (dataStr) => dataStr.split('-').reverse().join('/');

        // Função auxiliar para exibir o Toast e salvar na memória
        function dispararAviso(idUnico, mensagem, corGradient) {
            // Só exibe se ainda não mostramos ESSE aviso específico hoje
            if (vistos[idUnico] !== hoje) {
                Toastify({
                    text: mensagem,
                    duration: 10000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    style: { background: corGradient, color: "#fff", fontWeight: "bold", borderRadius: "8px" }
                }).showToast();
                
                vistos[idUnico] = hoje; // Grava que avisou hoje
                localStorage.setItem('notificacoes_rastreio', JSON.stringify(vistos));
            }
        }

        // 2. AGRUPAR O HISTÓRICO POR CÓDIGO DO PEDIDO
        // Isso permite saber se o pacote está na viagem 1 ou se já foi recadastrado (viagem 2)
        const pacotes = {};
        rastreios.forEach(r => {
            if (!pacotes[r.codigo]) pacotes[r.codigo] = [];
            pacotes[r.codigo].push(r);
        });

        // 3. ANALISAR CADA PACOTE
        for (const codigo in pacotes) {
            const historico = pacotes[codigo];
            
            // O último registro inserido no banco para este código é o status atual dele
            const viagemAtual = historico[historico.length - 1];

            // Variáveis para facilitar a leitura
            const souOrigem = (viagemAtual.unidade_original === minhaUnidade);
            const souDestino = (viagemAtual.unidade_destino === minhaUnidade);

            // Se minha unidade não tem nada a ver com esse pacote no momento, ignora e vai pro próximo
            if (!souOrigem && !souDestino) continue;

            // --- LÓGICA A: PACOTE ACABOU DE SER CONFIRMADO (TEM MAIS DE 1 REGISTRO NO BANCO) ---
            if (historico.length > 1) {
                // Apenas verificamos confirmações se elas aconteceram recentemente (hoje)
                if (viagemAtual.data_saida === hoje || viagemAtual.data_entrada === hoje) {
                    
                    if (viagemAtual.unidade_original === viagemAtual.unidade_destino) {
                        // REGRA: Retornou para a unidade original
                        dispararAviso(
                            `retorno_${codigo}`, 
                            `🔄 O pedido ${codigo} RETORNOU à unidade original (${viagemAtual.unidade_original}).`, 
                            "linear-gradient(to right, #e06c00, #f39c12)" // Laranja
                        );
                    } else {
                        // REGRA: Chegou ao destino
                        if (souDestino || souOrigem) {
                            dispararAviso(
                                `chegou_${codigo}`, 
                                `✅ O pedido ${codigo} CHEGOU com sucesso à unidade ${viagemAtual.unidade_destino}.`, 
                                "linear-gradient(to right, #27ae60, #2ecc71)" // Verde
                            );
                        }
                    }
                }
                continue; // Como já foi confirmado/entregue, não precisa checar atrasos dessa viagem.
            }

            // --- LÓGICA B: PACOTE ESTÁ EM TRÂNSITO (SÓ TEM 1 REGISTRO) ---
            
            // REGRA: Atrasado (Passou do dia de entrada e ainda só tem 1 registro no banco)
            if (hoje > viagemAtual.data_entrada) {
                dispararAviso(
                    `atraso_${codigo}`, 
                    `⚠️ ATRASO: O pedido ${codigo} não chegou à unidade ${viagemAtual.unidade_destino} no dia ${formataDataBR(viagemAtual.data_entrada)}!`, 
                    "linear-gradient(to right, #c0392b, #e74c3c)" // Vermelho
                );
            } 
            // REGRA: Sai hoje (Avisa a Origem)
            else if (viagemAtual.data_saida === hoje && souOrigem) {
                dispararAviso(
                    `saida_${codigo}`, 
                    `📦 O pedido ${codigo} deve SAIR HOJE da sua unidade para ${viagemAtual.unidade_destino}.`, 
                    "linear-gradient(to right, #8e44ad, #9b59b6)" // Roxo
                );
            }
            // REGRA: Chega hoje (Avisa o Destino)
            else if (viagemAtual.data_entrada === hoje && souDestino) {
                dispararAviso(
                    `chegada_${codigo}`, 
                    `🚚 O pedido ${codigo} deve CHEGAR HOJE na sua unidade.`, 
                    "linear-gradient(to right, #005c97, #363795)" // Azul
                );
            }
        }
    });
    </script>
    <?php endif; ?>





