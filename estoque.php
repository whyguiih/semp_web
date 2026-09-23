<?php
session_start();

require_once 'api.php';


$exibirLogicaToast = false;
$totalAtual = 0;

if (isset($_SESSION['nivel_conta']) && ($_SESSION['nivel_conta'] == 1 || $_SESSION['nivel_conta'] == 2)) {
    
    $exibirLogicaToast = true;

    $pedidosPendentes = chamarAPI('/pedidos/pendentes?unidade=' . urlencode($_SESSION['unidade']), 'GET');
    
    if (!is_array($pedidosPendentes) || isset($pedidosPendentes['erro']) || isset($pedidosPendentes['mensagem'])) {
        $pedidosPendentes = [];
    }
    
    $totalAtual = count($pedidosPendentes);
}


if (!isset($_SESSION['logado'])) { header("Location: index.php"); exit(); }

$produtos_individuais = chamarAPI('/produtos', 'GET');
if (!is_array($produtos_individuais)) $produtos_individuais = [];

$produtos = [];
foreach ($produtos_individuais as $p) {
    $nomeChave = mb_strtolower(trim($p['nome']), 'UTF-8');
    
    if (!isset($produtos[$nomeChave])) {
        $produtos[$nomeChave] = $p;
        $produtos[$nomeChave]['quant'] = 0;
    }
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
    document.getElementById('input-pesquisa').addEventListener('input', function() {
        let termo = this.value.toLowerCase();
        
        let produtos = document.querySelectorAll('.produto-card');

        produtos.forEach(function(produto) {
            let nomeProduto = produto.querySelector('h2').innerText.toLowerCase();
            
            if (nomeProduto.includes(termo)) {
                produto.style.display = 'block';
            } else {
                produto.style.display = 'none';
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

const pedidosVistos = parseInt(localStorage.getItem(chaveStorage)) || 0;
const novosPedidos = totalAtual - pedidosVistos;



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
        
        let dataPostagem = "";
        if (pedido.data_postagem) {
            dataPostagem = pedido.data_postagem.split(' ')[0];
        }

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
    if (isset($_SESSION['nivel_conta']) && $_SESSION['nivel_conta'] == '2'): 
        
        $todos_rastreios = chamarAPI('/rastreio/todos', 'GET');
        if (!is_array($todos_rastreios)) $todos_rastreios = [];
    ?>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const rastreios = <?= json_encode($todos_rastreios) ?>;
        const minhaUnidade = "<?= $_SESSION['unidade'] ?>";
        const hoje = new Date().toISOString().split('T')[0];
        
        let vistos = {};
        try { vistos = JSON.parse(localStorage.getItem('notificacoes_rastreio')) || {}; } catch(e) {}

        const formataDataBR = (dataStr) => dataStr.split('-').reverse().join('/');

        function dispararAviso(idUnico, mensagem, corGradient) {
            if (vistos[idUnico] !== hoje) {
                Toastify({
                    text: mensagem,
                    duration: 10000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    style: { background: corGradient, color: "#fff", fontWeight: "bold", borderRadius: "8px" }
                }).showToast();
                
                vistos[idUnico] = hoje;
                localStorage.setItem('notificacoes_rastreio', JSON.stringify(vistos));
            }
        }

        const pacotes = {};
        rastreios.forEach(r => {
            if (!pacotes[r.codigo]) pacotes[r.codigo] = [];
            pacotes[r.codigo].push(r);
        });

        for (const codigo in pacotes) {
            const historico = pacotes[codigo];
            
            const viagemAtual = historico[historico.length - 1];

            const souOrigem = (viagemAtual.unidade_original === minhaUnidade);
            const souDestino = (viagemAtual.unidade_destino === minhaUnidade);

            if (!souOrigem && !souDestino) continue;

            if (historico.length > 1) {
                if (viagemAtual.data_saida === hoje || viagemAtual.data_entrada === hoje) {
                    
                    if (viagemAtual.unidade_original === viagemAtual.unidade_destino) {
                        dispararAviso(
                            `retorno_${codigo}`, 
                            `🔄 O pedido ${codigo} RETORNOU à unidade original (${viagemAtual.unidade_original}).`, 
                            "linear-gradient(to right, #e06c00, #f39c12)" // Laranja
                        );
                    } else {
                        if (souDestino || souOrigem) {
                            dispararAviso(
                                `chegou_${codigo}`, 
                                `✅ O pedido ${codigo} CHEGOU com sucesso à unidade ${viagemAtual.unidade_destino}.`, 
                                "linear-gradient(to right, #27ae60, #2ecc71)" // Verde
                            );
                        }
                    }
                }
                continue;
            }

            
            if (hoje > viagemAtual.data_entrada) {
                dispararAviso(
                    `atraso_${codigo}`, 
                    `⚠️ ATRASO: O pedido ${codigo} não chegou à unidade ${viagemAtual.unidade_destino} no dia ${formataDataBR(viagemAtual.data_entrada)}!`, 
                    "linear-gradient(to right, #c0392b, #e74c3c)" // Vermelho
                );
            } 
            else if (viagemAtual.data_saida === hoje && souOrigem) {
                dispararAviso(
                    `saida_${codigo}`, 
                    `📦 O pedido ${codigo} deve SAIR HOJE da sua unidade para ${viagemAtual.unidade_destino}.`, 
                    "linear-gradient(to right, #8e44ad, #9b59b6)" // Roxo
                );
            }
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





