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
    // Busca os pedidos do usuário comum logado para monitorar retornos da API
    $meusPedidos = chamarAPI('/pedidos?usuario=' . urlencode($_SESSION['usuario']) . '&nivel=0', 'GET');
    if (!is_array($meusPedidos)) $meusPedidos = [];
?>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const meusPedidos = <?= json_encode($meusPedidos) ?>;
    const usuarioAtual = "<?= $_SESSION['usuario'] ?>";
    const chaveNotificados = 'status_notificados_' + usuarioAtual;
    
    // 1. Puxa o histórico de forma segura
    let notificados = {};
    try {
        const salvo = localStorage.getItem(chaveNotificados);
        if (salvo) {
            notificados = JSON.parse(salvo);
        }
    } catch (e) {
        notificados = {}; // Se der erro no navegador, limpa o histórico
    }

    let houveMudanca = false;

    meusPedidos.forEach(pedido => {
        // 2. Converte o ID e o Status forçadamente para TEXTO (String)
        // Isso evita o erro de comparar o número 1 com o texto "1"
        const id = String(pedido.id_emprestimo);
        const statusAtual = String(pedido.aprovacao); 

        // Se o status for 1 (Aprovado) ou 2 (Recusado)
        if (statusAtual === "1" || statusAtual === "2") {
            
            // 3. Compara como texto. Só entra se for realmente diferente do histórico
            if (String(notificados[id]) !== statusAtual) {
                
                let msg = statusAtual === "1" 
                    ? `🎉 Seu pedido de "${pedido.nome_produto}" foi APROVADO!` 
                    : `❌ Seu pedido de "${pedido.nome_produto}" foi RECUSADO.`;
                
                let corBg = statusAtual === "1" 
                    ? "linear-gradient(to right, #00b09b, #96c93d)" 
                    : "linear-gradient(to right, #ef5e31, #c0392b)";

                Toastify({
                    text: msg,
                    duration: 8000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    style: { background: corBg, color: "#ffffff", fontWeight: "bold", borderRadius: "8px" }
                }).showToast();

                // Grava na memória que esse ID já foi notificado com esse status
                notificados[id] = statusAtual;
                houveMudanca = true;
            }
        }
    });

    // 4. Só salva no navegador se uma notificação nova realmente apareceu
    if (houveMudanca) {
        localStorage.setItem(chaveNotificados, JSON.stringify(notificados));
    }
});
</script>
<?php endif; ?>
</html>