<div align="center">

# SEMP Web
**Sistema de Estoque Multiplataforma — Painel Web**

<p align="center">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/Cloudflare-F38020?style=for-the-badge&logo=cloudflare&logoColor=white" alt="Cloudflare Workers" />
  <img src="https://img.shields.io/badge/SQLite_D1-003B57?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite D1" />
  <img src="https://img.shields.io/badge/Apache-D22128?style=for-the-badge&logo=apache&logoColor=white" alt="Apache" />
  <img src="https://img.shields.io/badge/XAMPP-F37626?style=for-the-badge&logo=xampp&logoColor=white" alt="XAMPP" />
</p>

*Painel administrativo web em PHP para gestão centralizada de estoque, carrinho de reservas e pedidos de empréstimos entre unidades SENAI.*

</div>

---

## Visão Geral

O **SEMP Web** é a interface administrativa do ecossistema SEMP (Sistema de Estoque Multiplataforma). Construído em **PHP puro**, comunica-se exclusivamente via **API REST** hospedada no **Cloudflare Workers** com banco de dados **SQLite D1 (serverless)**.

> **Nota:** Este repositório contém apenas o **painel web**. O aplicativo móvel nativo (Android/Kotlin) reside em repositório separado.

---

## Funcionalidades Principais

| Módulo | Descrição |
|--------|-----------|
| **Autenticação** | Login seguro via API; sessões PHP com validação de nível de acesso e unidade |
| **Estoque Vivo** | Listagem consolidada de produtos com busca em tempo real, fotos e quantidades |
| **Carrinho & Pedidos** | Seleção de itens, ajuste de quantidades e formalização de solicitações de empréstimo |
| **Aprovação (Gestão)** | Operadores e gerentes autorizam/recusam pedidos pendentes de suas filiais |
| **Cadastro de Produtos** | Inclusão de novos materiais com upload de imagem (salva em `/uploads`) |
| **Gestão de Usuários** | Criação de contas com nível de permissão e unidade vinculada (apenas Admin) |
| **Rastreamento** | Visualização do fluxo de empréstimos entre unidades (origem → destino) |
| **Notificações Inteligentes** | Toasts automáticos para: pedidos novos, aprovações, saídas/chegadas previstas, atrasos e retornos |

---

## Arquitetura

```
┌─────────────────┐      HTTPS/JSON      ┌──────────────────────┐
│   SEMP Web      │ ◄──────────────────► │  Cloudflare Workers  │
│   (PHP + JS)    │   chamarAPI()        │  (API REST + D1)     │
└─────────────────┘                      └──────────────────────┘
                                              │
                                              ▼
                                    ┌──────────────────────┐
                                    │   SQLite D1          │
                                    │  (Serverless DB)     │
                                    └──────────────────────┘
```

- **Zero banco local**: O PHP **não** possui banco de dados próprio — toda persistência é remota.
- **Comunicação**: Função `chamarAPI()` em `api.php` faz requisições cURL com header `X-Usuario-ID` para autenticação.
- **Sessões**: PHP nativo (`$_SESSION`) armazena `usuario`, `nivel_conta`, `unidade` e `logado`.

---

## Estrutura do Projeto

```
semp_web/
├── index.php                 # Tela de login
├── processa_login.php        # Validação de credenciais via API
├── logout.php                # Encerramento de sessão
├── api.php                   # Config da API + função chamarAPI() + gerarCodigoSemp()
├── conf_acesso.php           # Cadastro de usuários (Admin)
├── estoque.php               # Vitrine principal (listagem + busca + notificações)
├── produto.php               # Detalhe do produto
├── edicao_estoque.php        # Edição de produto (Operador+)
├── cadastro_produto.php      # Novo produto com upload de foto (Operador+)
├── carrinho.php              # Itens selecionados + quantidades
├── tela_pedido.php           # Finalização do pedido (origem/destino)
├── fazer_pedido.php          # Processa criação do empréstimo
├── autorizar_pedidos.php     # Lista de pedidos pendentes (Operador/Gerente)
├── vizualizar_pedido.php     # Detalhes + aprovação/recusa
├── rastreio_pedido.php       # Rastreamento de um pedido específico
├── computar_rastreio.php     # Registro de saída/chegada/retorno (Gerente)
├── pedir_retorno.php         # Solicita devolução à unidade original
├── cadastrar_unidade.php     # Cadastro de novas unidades (Admin)
├── gerar_codigo_ajax.php     # Endpoint AJAX para geração de códigos SEMP
├── acao_carrinho.php         # Adicionar/remover itens do carrinho
├── remover_carrinho.php      # Remove item específico do carrinho
├── inc/
│   ├── sidebar.php           # Menu lateral responsivo (contexto por nível)
│   └── verificar_acesso.php  # Helper de controle de acesso por nível
├── css/
│   ├── style.css             # Estilos principais
│   └── responsividade.css    # Breakpoints mobile/tablet/desktop
├── img/                      # Assets estáticos (logos, ícones)
└── uploads/                  # Fotos de produtos enviadas (precisa permissão 777)
```

---

## Pré-requisitos

| Requisito | Versão Mínima |
|-----------|---------------|
| **PHP** | 7.4+ |
| **Extensões PHP** | `curl`, `session`, `mbstring`, `json`, `fileinfo` |
| **Servidor Web** | Apache (XAMPP recomendado) ou Nginx + PHP-FPM |
| **HTTPS** | Obrigatório para produção (API Cloudflare exige TLS) |

---

## Instalação Rápida (XAMPP)

```bash
# 1. Clone o repositório na pasta htdocs
cd C:\xampp\htdocs
git clone <url-do-repo> semp_web

# 2. Configure a URL da API em api.php
# Edite a constante API_URL para apontar para seu Cloudflare Worker
define('API_URL', 'https://seu-worker.seu-subdominio.workers.dev');

# 3. Crie a pasta de uploads com permissão de escrita
mkdir C:\xampp\htdocs\semp_web\uploads
# No Windows: clique direito > Propriedades > Segurança > Editar > Usuários > Controle Total

# 4. Inicie Apache no XAMPP Control Panel

# 5. Acesse http://localhost/semp_web
```

---

## Níveis de Acesso

| Nível | Nome | Permissões |
|-------|------|------------|
| **0** | **Comum** | Visualizar estoque, gerenciar carrinho, fazer pedidos, ver status dos próprios pedidos |
| **1** | **Operador** | Tudo do Comum + **Cadastrar/Editar produtos** + **Autorizar pedidos** da sua unidade |
| **2** | **Gerente** | Tudo do Operador + **Ver estoque global** + **Aprovar/Recusar pedidos gerenciais** + **Registrar rastreio (saída/chegada/retorno)** + **Notificações de atraso/chegada** |
| **3** | **Admin** | Acesso total + **Cadastrar usuários** + **Cadastrar unidades** + **Painel administrativo global** |

> O controle é feito via `inc/verificar_acesso.php` — ex: `verificarAcesso([1,2,3])` permite Operador, Gerente e Admin.

---

## Esquema do Banco (Cloudflare D1)

### `tb_estoque` — Itens de estoque
| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id_estoque` | TEXT (PK) | Código SEMP único (ex: `Ac302-abc123XYZ9`) |
| `nome` | TEXT | Nome do produto |
| `quant` | INTEGER | Quantidade total (somatória de registros) |
| `unidade` | TEXT | Unidade de medida (un, kg, m, etc.) |
| `foto` | TEXT | Caminho relativo da imagem (`uploads/...`) |
| `carrinho` | INTEGER | 0/1 — item está em algum carrinho |
| `pedido` | INTEGER | 0/1 — item está em algum empréstimo ativo |

### `tb_emprestimo` — Empréstimos entre unidades
| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id_emprestimo` | TEXT (PK) | Código SEMP do pedido |
| `usuario` | TEXT | Usuário solicitante |
| `nome_produto` | TEXT | Nome do item |
| `quantidade` | INTEGER | Qtd solicitada |
| `unidade_origem` | TEXT | Unidade de retirada |
| `unidade_destino` | TEXT | Unidade de entrega |
| `processamento` | INTEGER | 0=Pendente, 1=Autorizado Operador, 2=Autorizado Gerente |
| `aprovacao` | INTEGER | -1=Recusado, 0=Pendente, 1=Aprovado |
| `data_postagem` | DATETIME | Quando foi postado |
| `data_saida_prevista` | DATE | Previsão de saída |
| `data_entrada_prevista` | DATE | Previsão de chegada |

### `tb_usuarios` — Contas do sistema
| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `usuario` | TEXT (PK) | Login único |
| `senha` | TEXT | Hash (bcrypt/argon2) |
| `nivel` | INTEGER | 0 a 3 |
| `unidade` | TEXT | Unidade física vinculada |
| `foto` | TEXT | Caminho da foto de perfil |

### `tb_rastreio` — Histórico de movimentação
| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | INTEGER (PK) | Auto-incremento |
| `codigo` | TEXT | FK → `tb_emprestimo.id_emprestimo` |
| `unidade_original` | TEXT | Unidade de origem do empréstimo |
| `unidade_destino` | TEXT | Unidade de destino atual |
| `data_saida` | DATE | Data real de saída |
| `data_entrada` | DATE | Data real de chegada |
| `status` | TEXT | `em_transito`, `entregue`, `retornado` |

---

## Endpoints da API (Cloudflare Worker)

| Método | Endpoint | Descrição | Auth |
|--------|----------|-----------|------|
| `POST` | `/login` | Autentica usuário | ❌ |
| `GET` | `/produtos` | Lista todos itens (um por registro) | ✅ |
| `GET` | `/produtos/{id}` | Detalhe de um item | ✅ |
| `POST` | `/produtos` | Cadastra novo produto (com foto base64) | ✅ (Operador+) |
| `PUT` | `/produtos/{id}` | Atualiza produto | ✅ (Operador+) |
| `GET` | `/carrinho` | Itens no carrinho do usuário | ✅ |
| `POST` | `/carrinho` | Adiciona item ao carrinho | ✅ |
| `DELETE` | `/carrinho/{nome}` | Remove item do carrinho | ✅ |
| `POST` | `/pedidos` | Cria novo empréstimo | ✅ |
| `GET` | `/pedidos` | Lista pedidos (filtros: usuario, nivel, unidade) | ✅ |
| `GET` | `/pedidos/pendentes` | Pedidos aguardando aprovação por unidade | ✅ (Operador+) |
| `PUT` | `/pedidos/{id}/aprovar` | Aprova pedido | ✅ (Gerente+) |
| `PUT` | `/pedidos/{id}/recusar` | Recusa pedido | ✅ (Gerente+) |
| `GET` | `/rastreio/todos` | Histórico completo de rastreio | ✅ (Gerente+) |
| `POST` | `/rastreio` | Registra saída/chegada/retorno | ✅ (Gerente+) |
| `POST` | `/usuario/cadastrar` | Cria novo usuário | ✅ (Admin) |
| `POST` | `/unidade/cadastrar` | Cadastra nova unidade | ✅ (Admin) |

> **Autenticação**: Header `X-Usuario-ID: <login>` enviado automaticamente por `chamarAPI()`.

---

## Códigos SEMP (Padrão de Identificação)

Formato: `{ESTADO}{REGIÃO}{ALEATÓRIO}{TIPO_UNIDADE}{TIPO_ENTIDADE}-{SUFIXO_10}`

| Posição | Significado | Exemplo |
|---------|-------------|---------|
| 1 | Estado (A=RS) | `A` |
| 2 | Região (a=Metrop1, c=Serra1, l=Vale Taquari2) | `c` |
| 3 | Caractere aleatório (A-Z, a-z, 0-9) | `k` |
| 4 | Tipo unidade (0=Unidade, 1=Adendo) | `0` |
| 5 | Tipo entidade (2=Produto, 3=Pedido) | `2` |
| 6-15 | Sufixo alfanumérico 10 chars | `mN8pQ2rL5x` |

**Exemplo Produto:** `Ack02-mN8pQ2rL5x`  
**Exemplo Pedido:** `Ack03-mN8pQ2rL5x`

Gerado automaticamente por `gerarCodigoSemp()` em `api.php`.

---

## Sistema de Notificações (Toastify)

Notificações **client-side** via `localStorage` + `sessionStorage` para evitar spam:

| Evento | Público | Trigger |
|--------|---------|---------|
| Novo pedido pendente | Operador/Gerente | `estoque.php` carrega + diff `localStorage` |
| Pedido aprovado/postado hoje | Comum (solicitante) | `estoque.php` verifica `aprovacao=1` + `data_postagem=hoje` |
| Saída prevista hoje | Gerente (origem) | `rastreio.data_saida === hoje` |
| Chegada prevista hoje | Gerente (destino) | `rastreio.data_entrada === hoje` |
| **Atraso** (passou data_entrada sem confirmação) | Gerente (origem/destino) | `hoje > data_entrada` + apenas 1 registro |
| Confirmação de chegada | Gerente (origem/destino) | 2º registro inserido no rastreio |
| Retorno à origem | Gerente | `unidade_original === unidade_destino` no 2º registro |

---

## Interface & UX

- **Sidebar responsiva**: Colapsa em mobile (`☰` hamburger), itens visíveis conforme `nivel_conta`
- **Busca instantânea**: Filtro `input` no `estoque.php` faz `includes()` no `h2` dos cards
- **Cards de produto**: Imagem, nome, código, quantidade — clique abre `produto.php?id=...`
- **Carrinho em grid**: Checkbox por item, input `number` com `max=estoque_max`, botão "Remover"
- **Toastify JS** (CDN): Toasts animados, coloridos por severidade (verde=sucesso, vermelho=erro/atraso, laranja=retorno, roxo=saída, azul=chegada)

---

## Deploy em Produção

### Apache (Linux/Windows)
```apache
<VirtualHost *:80>
    DocumentRoot /var/www/semp_web
    <Directory /var/www/semp_web>
        AllowOverride All
        Require all granted
    </Directory>
    # Força HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>
```

### Variáveis de Ambiente (Recomendado)
```bash
# .env (não versionar)
API_URL=https://api-estoque.seu-dominio.workers.dev
```

Altere `api.php` para ler `getenv('API_URL')` com fallback.

### Checklist Produção
- [ ] HTTPS válido (Let's Encrypt / Cloudflare SSL)
- [ ] `uploads/` com permissão escrita apenas pelo user do PHP-FPM
- [ ] `display_errors = Off` no `php.ini`
- [ ] `session.cookie_secure = 1` + `session.cookie_httponly = 1`
- [ ] Backup automático do D1 (Cloudflare faz snapshot, mas exporte periodicamente)

---

## Testes Manuais Sugeridos

1. **Login** → Comum, Operador, Gerente, Admin
2. **Estoque** → Busca, paginação visual, cards com/sem foto
3. **Carrinho** → Add/remove, alterar qtd > estoque (deve bloquear), finalizar
4. **Pedido** → Criar → ver em "Autorizar Pedidos" (Operador) → Aprovar (Gerente) → Rastrear
5. **Rastreio** → Registrar saída → chegar → verificar notificações
6. **Cadastro Produto** → Com/sem foto, validação campos obrigatórios
7. **Cadastro Usuário** → Admin cria Operador/Gerente/Comum
8. **Permissões** → Acessar URL direta de página restrita (deve redirecionar)

---

## Troubleshooting Comum

| Problema | Causa Provável | Solução |
|----------|----------------|---------|
| `chamarAPI()` retorna `null` | cURL falhou / SSL / DNS | Verifique `curl_error()`, `CURLOPT_SSL_VERIFYPEER`, DNS do Worker |
| Sessão expira rápido | `session.gc_maxlifetime` baixo | Aumente no `php.ini` (ex: 3600) |
| Upload falha | Permissão `uploads/` / `post_max_size` | `chmod 777 uploads` + `upload_max_filesize=10M` |
| Notificações não aparecem | Toastify não carregou / JS error | Abra DevTools Console, verifique CDN `cdn.jsdelivr.net` |
| Código SEMP duplicado | Colisão aleatória (raro) | `gerarCodigoSemp()` usa `random_int` — probabilidade ínfima |

---

## Créditos

Desenvolvido por **Threeeo** — 2026  
**Gabriel Artuso**, **Guilherme Brandalize**, **Larissa B. Gazoli**

---

## Licença

Uso interno SENAI. Todos os direitos reservados.
