<div class="sidebar">
    
    <div class="sidebar-top">   
        <a href="estoque.php"><img src="img/logo_menor.png" alt="Logo"></a>
    </div>
    
    <a href="estoque.php"><img src="img/lista.png" alt="Lista"></a>
    <a href="carrinho.php"><img src="img/carrinho.png" alt="Carrinho"></a>
    
    <?php if ($_SESSION['nivel_conta'] == '1' || $_SESSION['nivel_conta'] == '2'): ?>
        <a href="cadastro_produto.php"><img src="img/lupa.png" alt="Cadastro"></a>
    <?php endif; ?>

    <?php if ($_SESSION['nivel_conta'] == '2'): ?>
        <a href="autorizar_pedidos.php"><img src="img/controle.png" alt="Autorizar"></a>
        <a href="rastreio_pedido.php"><img src="img/marker.png" alt="Ratreiar"></a>
        <a href="computa_rastreio.php"><img src="img/envelope.png" alt="Dia"></a>
        
        <a href="pedir_retorno.php"><img src="img/retorno.png" alt="Pedir Retorno"></a>
    <?php endif; ?>

    <?php if ($_SESSION['nivel_conta'] == '1'): ?>
        <a href="vizualizar_pedido.php"><img src="img/visual.png" alt="Visualizar"></a>
    <?php endif; ?>

    <?php if ($_SESSION['nivel_conta'] == '3'): ?>
         <a href="conf_acesso.php"><img src="img/acesso.png" alt="Usuarios"></a>
    <?php endif; ?>
    
    <div class="sidebar-bottom">
        <a href="logout.php"><img src="img/sair.png" alt="Sair"></a>
    </div>
</div>