<div align="center">

# SEMP
**Sistema de Estoque Multiplataforma**
<p align="center">
  <img src="https://img.shields.io/badge/Android-3DDC84?style=for-the-badge&logo=android&logoColor=white" alt="Android" />
  <img src="https://img.shields.io/badge/Kotlin-0095D5?style=for-the-badge&logo=kotlin&logoColor=white" alt="Kotlin" />
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/Cloudflare-F38020?style=for-the-badge&logo=cloudflare&logoColor=white" alt="Cloudflare" />
  <img src="https://img.shields.io/badge/SQLite_D1-003B57?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite D1" />
</p>

*Plataforma para gestão centralizada de estoque, carrinho de reservas e pedidos de empréstimos entre unidades SENAI.*

</div>

---

##  Visão Geral

O **SEMP** é uma solução completa para controle de inventário. O projeto conta com um sistema web (painel em PHP) e um aplicativo nativo para dispositivos móveis. Todo o ecossistema se comunica através de uma API REST hospedada nativamente no **Cloudflare Workers**.

###  Principais Funcionalidades

| Funcionalidade | Descrição |
| :--- | :--- |
| **Autenticação** | Login integrado via API; gestão de sessões por unidade e níveis de acesso. |
| **Estoque Vivo** | Listagem de produtos (chaves, alicates, tijolos, etc.) com atualização e pesquisa em tempo real. |
| **Carrinho & Pedidos** | Seleção de itens, manipulação de carrinho e formalização de reservas de empréstimos inter-unidades. |
| **Aprovação (Gestão)** | Gestores autorizam ou recusam as solicitações pendentes para as suas respectivas filiais. |
| **Gestão de Itens** | Cadastramento de novos materiais (com captura/upload de fotos). |

---

##  Aplicativo Android

O aplicativo Android foi construído nativamente em **Kotlin** para fornecer agilidade nas operações das unidades (como Garibaldi, Farroupilha e Encantado).

* **SDKs Suportados:** Mínimo SDK 29, com o projeto focado (Target) na API 36.
* **Comunicação Web:** Implementação com a biblioteca `Retrofit2` (versão 2.9.0) com conversor GSON para consumo rápido da API REST.
* **Módulos do App (Telas principais):** O aplicativo cobre de forma fluida os fluxos web através das atividades `MainActivity`, `EstoqueActivity`, `ProdutoDetalheActivity`, `CarrinhoActivity`, `FazerPedidoActivity`, `ConfigEstoqueActivity`, `AutorizarPedidosActivity` e `CadastrarUsuarioActivity`.

---

##  Aplicação Web

O dashboard na web requer os seguintes pré-requisitos:
1. **PHP 7.4+** com extensões `curl` e `session` habilitadas.
2. Um servidor web compatível (Apache, XAMPP, Nginx).
3. Diretórios com permissão correta (`/uploads` para salvar anexos e `/img` para interface).

### Arquitetura de Comunicação
A interface PHP não armazena e nem consulta o banco de dados localmente. Todas as requisições de negócio são disparadas via requisições HTTP (utilizando a função interna `chamarAPI()`) com formato de transporte **JSON**, processadas pela camada de servidor do **Cloudflare**.

---

##  Estrutura de Dados (Cloudflare D1)

A base de dados remota utiliza migrações D1 para SQLite Serverless. Ela é fundamentada em três grandes coleções principais:

* `tb_estoque`: Salva as referências, quantidades em tempo real, unidade natural do objeto e rastreia itens atualmente em carrinhos (`carrinho`) ou sob encomenda (`pedido`).
* `tb_emprestimo`: Relaciona nome, e-mail, origem e destino dos empréstimos, com colunas para `processamento` e `aprovacao` das diretorias.
* `tb_usuarios`: Mantém as credenciais cadastradas e a vinculação de cada usuário com o nível de conta e sua respectiva unidade física.

---

##  Níveis de Conta

O sistema segmenta de forma rigorosa as permissões na aplicação:

<div align="center">

| Nível | Nomenclatura | Permissões Concedidas |
| :---: | :--- | :--- |
| **0** | **Comum** | Visualizar estoque, gerir carrinho e fazer pedidos de material. Não tem painel administrativo. |
| **1** | **Operador** | Permissões Comuns + Acesso livre ao Cadastro de novos Produtos + Autorizar Pedidos da base. |
| **2** | **Gerente** | Visualização do estoque global + Aprovar e recusar pedidos gerenciais de movimentação. |
| **3** | **Admin** | Permissão administrativa ampla global da plataforma e cadastros de usuários raiz (ex: Administrador). |

</div>

<div align="center">

# Terminal IoT de Triagem
**Controle de Estoque Físico — Módulo SEMP**
<p align="center">
  <img src="https://img.shields.io/badge/Raspberry%20Pi-C51A4A?style=for-the-badge&logo=Raspberry%20Pi&logoColor=white" alt="Raspberry Pi Pico" />
  <img src="https://img.shields.io/badge/C%2FC%2B%2B-00599C?style=for-the-badge&logo=c%2B%2B&logoColor=white" alt="C/C++" />
  <img src="https://img.shields.io/badge/RFID_RC522-4B0082?style=for-the-badge&logo=nfc&logoColor=white" alt="RFID" />
  <img src="https://img.shields.io/badge/I2C%20%26%20SPI-000000?style=for-the-badge&logo=microchip&logoColor=white" alt="Protocolos" />
  <img src="https://img.shields.io/badge/USB_CDC-005C97?style=for-the-badge&logo=usb&logoColor=white" alt="USB" />
</p>

*Subsistema embarcado de identificação por RFID, integrado como módulo periférico ao Sistema de Estoque Multiplataforma, com comunicação serial via cabo USB.*

</div>

---

## 📌 Informações do Projeto

| Propriedade | Detalhe |
| :--- | :--- |
| **Projeto de origem** | SEMP — Sistema de Estoque Multiplataforma |
| **Escopo deste doc.** | Especificações de hardware, custos e ligações elétricas do terminal embarcado |
| **Versão** | 1.1 |

---

## 🎯 Posicionamento do Módulo no Projeto SEMP

Este documento descreve o **Terminal IoT de Triagem e Controle de Estoque**, um subsistema de hardware embarcado que compõe o Projeto SEMP. É importante destacar que este terminal não corresponde ao SEMP como um todo: o SEMP é o sistema completo de gerenciamento de estoque, do qual este dispositivo é apenas o módulo físico responsável pela coleta de dados em campo, atuando como ponta de entrada (*front-end* físico) que alimenta o banco de dados central do sistema.

A arquitetura do terminal foi concebida de forma simplificada, com processamento local de periféricos (RFID, display e botões) realizado por um único microcontrolador, que se conecta diretamente a um computador por meio de um cabo USB. Essa abordagem elimina a necessidade de um coprocessador dedicado à comunicação sem fio, simplificando o hardware e tornando a transferência de dados mais direta e estável, uma vez que o terminal opera como um periférico serial conectado fisicamente ao *host*.

---

## 🔄 Visão Geral Funcional

O fluxo de operação do terminal ocorre da seguinte maneira:

1. O operador seleciona, por meio de botões físicos de comando, a ação a ser registrada (**Entrada** ou **Saída** de material).
2. Uma *tag* RFID fixada na mercadoria é aproximada do leitor, que captura seu identificador único.
3. O *display* OLED local exibe *feedback* imediato com a identificação do produto lido.
4. As informações coletadas são formatadas e enviadas via cabo USB (comunicação serial) ao computador conectado.
5. Uma aplicação no computador recebe os dados via porta serial e efetua a sincronização com o banco de dados do estoque (camada central do SEMP).

---

## ⚙️ Especificações de Hardware dos Componentes

### 1. Raspberry Pi Pico — Controlador Geral
Unidade central de processamento lógico local. Responsável pela leitura do módulo RFID, *debounce* e interpretação das chaves tácteis, atualização do *display* e formatação das *strings* de dados para envio ao computador via USB. É baseado no microcontrolador **RP2040** (Dual-core ARM Cortex-M0+, até 133MHz), com 264KB de SRAM integrada e 2MB de memória Flash para armazenamento do *firmware*.

### 2. Conexão com o Computador (Cabo USB)
A comunicação de dados entre o terminal e a camada central do SEMP é realizada por meio de um cabo USB conectado diretamente à porta USB nativa do Raspberry Pi Pico. O Pico opera como um dispositivo serial (**USB CDC**), de modo que uma aplicação no computador *host* pode ler os dados enviados pelo *firmware* como se fossem provenientes de uma porta serial convencional (`COM` ou `ttyACM`). Esse mesmo cabo USB também é responsável pela alimentação elétrica do terminal, dispensando fonte externa.

### 3. Leitor RFID (RC522)
Transceptor de rádio frequência operando em 13.56 MHz, compatível com *tags* passivas Mifare. Conecta-se diretamente ao barramento **SPI** do controlador geral.

### 4. Display OLED 0,96"
Módulo monocromático de alta definição, resolução nativa 128x64 pixels, controlador integrado SSD1306. Comunicação via barramento síncrono **I2C**, reduzindo a contagem de pinos de sinal necessários.

### 5. Botões de Comando
Chaves de contato táctil tipo *push-button*, integradas diretamente aos pinos de GPIO do controlador geral, configuradas em modo *Pull-Up* interno (nível lógico alto por padrão, fechando o circuito em nível lógico baixo quando pressionadas).

---

## 💰 Planilha de Custos Estimados

*Levantamento orçamentário médio praticado no mercado brasileiro para aquisição dos componentes individuais, com finalidade de prototipagem do terminal.*

<div align="center">

| Componente | Especificação / Modelo | Qtd. | Valor Unit. | Valor Total |
| :--- | :--- | :---: | :--- | :--- |
| **Raspberry Pi Pico** | Placa microcontroladora RP2040 standard | 1 | R$ 45,00 | R$ 45,00 |
| **Leitor RFID** | Kit Módulo RC522 13.56MHz com tags | 1 | R$ 15,00 | R$ 15,00 |
| **Display OLED** | Display Gráfico 0.96" 128x64 I2C SSD1306 | 1 | R$ 25,00 | R$ 25,00 |
| **Chaves Tácteis** | Push-buttons de 4 pinos para painel | 2 | R$ 1,00 | R$ 2,00 |
| **Cabo USB** | Cabo USB-A para Micro-USB/USB-C | 1 | R$ 15,00 | R$ 15,00 |
| **Miscelâneas** | Protoboard e cabos jumpers | 1 | R$ 20,00 | R$ 20,00 |
| <br> | <br> | <br> | **TOTAL ESTIMADO:** | **R$ 122,00** |

</div>

---

## 🔌 Esquema de Ligações Eletroeletrônicas

O sistema elétrico foi projetado sob lógica de compatibilidade de sinal em **3.3V**. Dessa forma, as comunicações entre os barramentos dispensam o uso de conversores de nível lógico bidirecionais.

### Barramento SPI0 (Raspberry Pi Pico ↔ Módulo RFID RC522)

| Pino RFID MFRC522 | Função do Sinal | GPIO (Pico) | Pino Físico (Pico) |
| :--- | :--- | :--- | :--- |
| **VCC** | Alimentação do módulo (3.3V) | 3.3V(OUT) | Pino 36 |
| **RST** | Reinicialização de Hardware | GP20 | Pino 26 |
| **GND** | Referência de Aterramento | GND | Pino 38 |
| **MISO** | Master Input Slave Output | GP16 (SPI0 RX) | Pino 21 |
| **MOSI** | Master Output Slave Input | GP19 (SPI0 TX) | Pino 25 |
| **SCK** | Sinal de Clock Serial SPI | GP18 (SPI0 SCK) | Pino 24 |
| **SDA (NSS)** | Seleção de Escravo (Chip Select) | GP17 (SPI0 CSn) | Pino 22 |

### Barramento I2C0 (Raspberry Pi Pico ↔ Display OLED)

| Pino Display OLED | Função do Sinal | GPIO (Pico) | Pino Físico (Pico) |
| :--- | :--- | :--- | :--- |
| **GND** | Referência de Aterramento | GND | Pino 23 |
| **VCC** | Alimentação 3.3V | 3.3V(OUT) | Pino 36 |
| **SCL** | Sinal de Clock Linha I2C | GP5 (I2C0 SCL) | Pino 7 |
| **SDA** | Sinal de Dados Linha I2C | GP4 (I2C0 SDA) | Pino 6 |

### Mapeamento de Chaves Tácteis (Botões)

| Botão Mapeado | Terminal Físico 1 | Terminal Físico 2 | Estado Lógico |
| :--- | :--- | :--- | :--- |
| **Entrada no Estoque** | GP14 (Pino 19 do Pico) | Barramento GND Comum | `HIGH` (aciona em `LOW`) |
| **Saída do Estoque** | GP15 (Pino 20 do Pico) | Barramento GND Comum | `HIGH` (aciona em `LOW`) |

---

## ⚠️ Recomendações Técnicas de Infraestrutura Elétrica

Para assegurar a confiabilidade do terminal em ambiente de estoque, devem-se observar as seguintes práticas de engenharia de hardware:

1. **Qualidade e Comprimento do Cabo USB:** Recomenda-se o uso de cabos USB de boa qualidade, com blindagem adequada contra interferência eletromagnética, e comprimento não superior a 3 metros, a fim de evitar perdas de sinal e instabilidade na comunicação serial entre o terminal e o computador. Cabos longos ou de baixa qualidade podem causar desconexões intermitentes ou falhas na enumeração do dispositivo USB.
2. **Mecanismo de Antirrepique (*Debounce*):** Os contatos mecânicos dos botões geram oscilações parasitárias de chaveamento elétrico. É imperativo implementar filtros de *debounce* via código (com atraso computacional de aproximadamente 30ms a 50ms) ou um arranjo físico de circuito integrador passa-baixas simples (filtro RC).
3. **Dimensionamento Elétrico de Corrente:** O regulador interno do Raspberry Pi Pico fornece corrente restrita na saída de 3.3V. Caso novos sensores sejam adicionados ao terminal, é mandatório prever o uso de uma fonte de tensão regulada dedicada, com capacidade para fornecer corrente excedente ao circuito, uma vez que a alimentação via porta USB do computador também é limitada em corrente.

---
