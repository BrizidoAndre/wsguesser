# WGUESSER — Interface Vue.js

## Proposta do projeto

Este projeto é a interface web do WGUESSER, um jogo de adivinhação de palavras no estilo Termo/Wordle. O objetivo é descobrir uma palavra de cinco letras em até seis tentativas, utilizando o feedback de cada letra para orientar os próximos palpites.

A interface apresenta o tabuleiro, recebe a digitação, mostra os resultados e permite reiniciar após o fim da partida. O projeto irmão `full_api_game`, desenvolvido em Laravel, é responsável por sortear a palavra, validar os palpites, persistir a partida e determinar vitória ou derrota.

O título exibido é **WGUESSER**. Os textos da interface estão atualmente em inglês. O fluxo não inclui login, ranking, multiplayer ou desafio diário.

## Recursos implementados

- Tabuleiro com seis linhas e cinco colunas.
- Teclado virtual com letras, exclusão e envio do palpite.
- Entrada pelo teclado físico, com conversão das letras para maiúsculas.
- Feedback visual nas células e nas teclas usando `correct`, `present` e `absent`.
- Animação de apresentação das letras após um palpite aceito.
- Alertas temporários para erros retornados no envio de palpites.
- Mensagens de vitória e derrota; palavra secreta exibida quando recebida na resposta de derrota.
- Botão `Restart` após o encerramento da partida.
- Persistência do identificador da partida no `localStorage`, na chave `game_id_br`, para consultar o histórico ao recarregar a página.

O navegador guarda apenas o identificador. A recuperação das tentativas depende da API e do banco de dados; o jogo não funciona de forma independente ou offline.

## Tecnologias

Versões declaradas em `package.json`:

- Vue `^3.5.38`, com Composition API e componentes Single File Component.
- Vue Router `^5.1.0`.
- Vite `^8.0.16` e `@vitejs/plugin-vue` `^6.0.7`.
- JavaScript com módulos ES, CSS e Fetch API nativa para HTTP.
- Node.js `^22.18.0 || >=24.12.0` e npm.

O Vue DevTools está instalado e importado na configuração do Vite, mas não está incluído na lista de plugins ativos.

## Organização do código

| Arquivo ou pasta | Responsabilidade |
| --- | --- |
| `src/App.vue` | Tabuleiro, teclado, estado da partida e chamadas para criar, carregar e enviar palpites. |
| `src/helpers/api.js` | URL base da API e funções HTTP `get` e `post`, retornando `[resposta, dados]`. |
| `src/helpers/global.js` | Identificador reativo da partida e sincronização com `localStorage`. |
| `src/helpers/addAlert.js` | Lista reativa de alertas e remoção após três segundos. |
| `src/components/AppAlert.vue` | Apresentação dos alertas com transições. |
| `src/assets/` | Estilos globais e recursos visuais. |
| `src/main.js` | Inicialização da aplicação e registro do router. |
| `src/router/index.js` | Rotas `/` e `/about` herdadas da estrutura inicial. |
| `vite.config.js` | Plugin Vue, alias `@` para `src` e base de publicação. |

O jogo é renderizado diretamente em `App.vue`, que não contém `RouterView`. Assim, as views `HomeView.vue` e `AboutView.vue` e os componentes de boas-vindas do template não compõem o fluxo atual do jogo.

## Fluxo e integração com a API

1. Ao montar a aplicação, o frontend verifica se existe `game_id_br` salvo.
2. Se houver identificador, consulta a partida; caso contrário, solicita uma nova.
3. A pessoa digita até cinco letras e envia com `ENTER`.
4. O backend valida a palavra e devolve os resultados por letra e o estado da partida.
5. A interface atualiza o tabuleiro e o teclado. Ao encerrar, bloqueia novas letras e oferece reinício.

| Requisição | Finalidade |
| --- | --- |
| `POST games` | Criar uma partida e obter `gameId`. |
| `GET games/{gameId}` | Recuperar estado e histórico em `guesses`. |
| `POST games/{gameId}/guesses` | Enviar `{ "word": "TERMO" }` e receber `feedback`, estado e contadores. |

Os caminhos são concatenados à URL base. O cliente espera JSON sem envelope `data`, compatível com os resources do Laravel deste conjunto.

## Executar localmente

1. Prepare e inicie o projeto Laravel seguindo seu README. O banco precisa conter um dicionário e as pendências de criação de partidas precisam ser tratadas para o fluxo funcionar em uma instalação nova.

2. Ajuste a constante `api_url` em `src/helpers/api.js`. O valor atualmente presente é:

   ```js
   const api_url = 'http://172.17.1.8/BR_client_side/api/';
   ```

   Para a API iniciada com `php artisan serve`, utilize:

   ```js
   const api_url = 'http://127.0.0.1:8000/';
   ```

   Preserve a barra final. As rotas desse Laravel não possuem prefixo `/api`. Não há leitura de uma variável `VITE_API_URL` implementada no cliente atual.

3. Na pasta deste projeto, instale as dependências e inicie o servidor:

   ```powershell
   npm ci
   npm run dev
   ```

4. Abra a URL informada pelo Vite. A configuração atual define `base: '/BR_client_side'`; no ambiente local, o caminho esperado é `/BR_client_side/` no endereço e porta exibidos pelo servidor.

### Compilação e prévia

```powershell
npm run build
npm run preview
```

O build gera os arquivos estáticos em `dist/`. A base `/BR_client_side` deve corresponder ao caminho de hospedagem; para publicar na raiz ou em outro subdiretório, ajuste `base` em `vite.config.js`. A URL da API também deve corresponder ao ambiente de destino antes da compilação.

## Estado atual e pontos de atenção

- **Dependência de ambiente:** o endereço da API está fixo no código. Sem ajustá-lo, o frontend tenta acessar o servidor da rede local indicado acima.
- **Erros de rede e recuperação:** o helper não captura falhas de conexão ou respostas que não sejam JSON. Criação e consulta não verificam `res.ok`; um identificador antigo ou inexistente não dispara recuperação automática. Para descartar uma referência inválida, remova `game_id_br` do armazenamento local e recarregue a página.
- **Envios simultâneos:** não há estado de carregamento que impeça novos envios enquanto um palpite está pendente.
- **Teclado físico:** a condição `else if ('Backspace')` é sempre verdadeira. Teclas não reconhecidas como letras ou Enter acabam acionando exclusão, mesmo quando não são Backspace.
- **Cores das teclas:** o histórico sobrescreve a cor na ordem em que é percorrido, sem preservar a prioridade de `correct` sobre `present` e `absent`.
- **Palavra secreta:** a consulta GET não a retorna e `loadGame()` não a recupera; por isso, ela não reaparece ao recarregar uma partida perdida. `createGame()` também não limpa `gameInfo.secret`.
- **Validação:** o frontend limita a entrada a cinco letras, mas pode enviar palavras incompletas. A validação definitiva fica na API. O teclado atual trabalha com A–Z, sem acentos.
- **Testes:** não há script de testes automatizados nem de lint em `package.json`.

Esta documentação descreve o código analisado. Não foram executados o build nem uma partida integrada com a API durante esta atualização dos READMEs.
