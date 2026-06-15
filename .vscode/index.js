export default {
  async fetch(request, env) {
    // Configurações de CORS para aceitar requisições do seu PHP ou App Java
    const corsHeaders = {
      "Access-Control-Allow-Origin": "*",
      "Access-Control-Allow-Methods": "GET, POST, PUT, DELETE, OPTIONS",
      "Access-Control-Allow-Headers": "Content-Type",
    };

    if (request.method === "OPTIONS") return new Response(null, { headers: corsHeaders });

    const url = new URL(request.url);
    const path = url.pathname;

    try {
      // --------------------------------------------------------
      // 1. ROTA DE LOGIN
      // --------------------------------------------------------
      if (request.method === "POST" && path === "/login") {
        const { usuario, senha } = await request.json();
        if (!usuario || !senha) {
          return new Response(JSON.stringify({ sucesso: false, mensagem: "Usuário/senha vazios." }), { status: 400, headers: { "Content-Type": "application/json", ...corsHeaders } });
        }
        
        // 1º PASSO: Busca apenas pelo nome de usuário
        const stmtUser = env.DB.prepare("SELECT * FROM tb_usuarios WHERE usuario = ?").bind(usuario);
        const { results } = await stmtUser.all();

        // 2º PASSO: Verifica se encontrou alguém com esse nome
        if (results && results.length > 0) {
          const user = results[0];
          
          // Verifica se a senha que a pessoa digitou bate com a do banco
          if (user.senha === senha) {
            return new Response(JSON.stringify({ sucesso: true, mensagem: "Login realizado com sucesso!", usuario: user.usuario, nivel_conta: user.nivel_conta, unidade: user.unidade }), { headers: { "Content-Type": "application/json", ...corsHeaders } });
          } else {
            // Conta existe, mas errou a senha
            return new Response(JSON.stringify({ sucesso: false, mensagem: "Usuário ou senha incorretos." }), { headers: { "Content-Type": "application/json", ...corsHeaders } });
          }
          
        } else {
          // Não encontrou o usuário cadastrado no sistema
          return new Response(JSON.stringify({ sucesso: false, mensagem: "Esta conta não existe." }), { headers: { "Content-Type": "application/json", ...corsHeaders } });
        }
      }

      // --------------------------------------------------------
      // 2. BUSCAR TODOS OS PRODUTOS (Para exibir na vitrine)
      // --------------------------------------------------------
      else if (request.method === "GET" && path === "/produtos") {
        const { results } = await env.DB.prepare("SELECT * FROM tb_estoque").all();
        return new Response(JSON.stringify(results), { headers: { "Content-Type": "application/json", ...corsHeaders } });
      }

      // --------------------------------------------------------
      // 3. BUSCAR UM PRODUTO ESPECÍFICO (Para exibir detalhes)
      // Ex: GET /produtos/21
      // --------------------------------------------------------
      else if (request.method === "GET" && path.startsWith("/produtos/")) {
        const id = path.split("/").pop(); // Pega o número do final da URL
        const { results } = await env.DB.prepare("SELECT * FROM tb_estoque WHERE id_estoque = ?").bind(id).all();
        
        if (results.length > 0) {
            return new Response(JSON.stringify(results[0]), { headers: { "Content-Type": "application/json", ...corsHeaders } });
        }
        return new Response(JSON.stringify({ erro: "Produto não encontrado" }), { status: 404, headers: { "Content-Type": "application/json", ...corsHeaders } });
      }

      // --------------------------------------------------------
      // 4. ADICIONAR PRODUTO AO CARRINHO (Atualiza carrinho = 1)
      // --------------------------------------------------------
      else if (request.method === "POST" && path === "/carrinho/adicionar") {
        const { id_produto } = await request.json();
        await env.DB.prepare("UPDATE tb_estoque SET carrinho = 1 WHERE id_estoque = ?").bind(id_produto).run();
        return new Response(JSON.stringify({ sucesso: true }), { headers: { "Content-Type": "application/json", ...corsHeaders } });
      }

      // --------------------------------------------------------
      // 5. BUSCAR PRODUTOS NO CARRINHO
      // --------------------------------------------------------
      else if (request.method === "GET" && path === "/carrinho") {
        const { results } = await env.DB.prepare("SELECT * FROM tb_estoque WHERE carrinho = 1").all();
        return new Response(JSON.stringify(results), { headers: { "Content-Type": "application/json", ...corsHeaders } });
      }

      // --------------------------------------------------------
      // 6. CADASTRAR NOVO PRODUTO (Nível Admin)
      // --------------------------------------------------------
      else if (request.method === "POST" && path === "/produto/cadastrar") {
        const { nome, codigo, descricao, quant, uni_natal, foto } = await request.json();
        const sql = `INSERT INTO tb_estoque 
                     (nome, codigo, descricao, descricao_detalhada, cor, quant, uni_intermediarias, marca_ref, uni_natal, carrinho, pedido, foto) 
                     VALUES (?, ?, ?, '', NULL, ?, '', '', ?, 0, '0', ?)`;
        
        await env.DB.prepare(sql).bind(nome, codigo, descricao, quant, uni_natal, foto || null).run();
        return new Response(JSON.stringify({ sucesso: true }), { headers: { "Content-Type": "application/json", ...corsHeaders } });
      }

      // --------------------------------------------------------
      // 7. LISTAR PEDIDOS PARA APROVAÇÃO (Nível Compras/Logística)
      // --------------------------------------------------------
      else if (request.method === "GET" && path === "/pedidos/pendentes") {
        const unidade = url.searchParams.get("unidade"); // Pega o ?unidade=SENAI
        const { results } = await env.DB.prepare("SELECT * FROM tb_emprestimo WHERE processamento = 1 AND unidade_natal = ? AND aprovacao = 0").bind(unidade).all();
        return new Response(JSON.stringify(results), { headers: { "Content-Type": "application/json", ...corsHeaders } });
      }

      // --------------------------------------------------------
      // 8. AUTORIZAR OU RECUSAR PEDIDO
      // --------------------------------------------------------
      else if (request.method === "POST" && path === "/pedidos/autorizar") {
        const { id_emprestimo, novoStatus } = await request.json(); // novoStatus: 1 para Liberar, 2 para Deletar
        await env.DB.prepare("UPDATE tb_emprestimo SET aprovacao = ? WHERE id_emprestimo = ?").bind(novoStatus, id_emprestimo).run();
        return new Response(JSON.stringify({ sucesso: true }), { headers: { "Content-Type": "application/json", ...corsHeaders } });
      }

      // --------------------------------------------------------
      // 4. ADICIONAR PRODUTO AO CARRINHO (Atualiza carrinho = 1)
      // --------------------------------------------------------
      else if (request.method === "POST" && path === "/carrinho/remover") {
        const { id_produto } = await request.json();
        await env.DB.prepare("UPDATE tb_estoque SET carrinho = 0 WHERE id_estoque = ?").bind(id_produto).run();
        return new Response(JSON.stringify({ sucesso: true }), { headers: { "Content-Type": "application/json", ...corsHeaders } });
      }

      // Se a rota não existir
      return new Response(JSON.stringify({ erro: "Rota não encontrada na API." }), { status: 404, headers: { "Content-Type": "application/json", ...corsHeaders } });

    } catch (e) {
      // Captura qualquer erro de código ou SQL
      return new Response(JSON.stringify({ sucesso: false, mensagem: "Erro interno no servidor Cloudflare: " + e.message }), { status: 500, headers: { "Content-Type": "application/json", ...corsHeaders } });
    }
  }
};