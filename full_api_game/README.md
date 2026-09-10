# WGUESSER — API Laravel

## Proposta do projeto

Este projeto é o backend do WGUESSER, um jogo de adivinhação de palavras no estilo Termo/Wordle. O jogador tenta descobrir uma palavra de cinco letras em até seis tentativas. A cada palpite aceito, recebe uma indicação sobre a presença e a posição de cada letra.

A API centraliza o sorteio da palavra, a validação dos palpites, o armazenamento das partidas e a definição de vitória ou derrota. A interface fica no projeto irmão `full_front_game`, desenvolvido em Vue.js, que consome as respostas JSON deste serviço.

O código implementa partidas individuais com palavras sorteadas por partida. Não há desafio diário, ranking, modo multiplayer ou associação das partidas a usuários nas rotas atuais.

## Tecnologias

- PHP `^8.3` e Laravel `^13.8`, conforme `composer.json`.
- Eloquent para acesso ao banco e migrations para definição das tabelas.
- MySQL configurado no `.env.example`.
- Laravel Sanctum instalado, mas sem autenticação aplicada às rotas do jogo.
- PHPUnit `^12.5.12` como dependência de desenvolvimento; os testes presentes são os exemplos do projeto inicial.

## Funcionamento

1. O cliente solicita uma nova partida.
2. A API sorteia um registro da tabela `words` e cria a partida com seis tentativas disponíveis.
3. Cada palpite deve ser uma string de cinco caracteres existente em `words.name`.
4. Um palpite válido é salvo em `attempts` e atualiza os contadores da partida.
5. Uma palavra idêntica à sorteada encerra a partida com `won`. Esgotar as tentativas encerra com `lost`; nos demais casos, o estado é `playing`.
6. O cliente pode consultar a partida pelo identificador para recuperar seu estado e histórico.

O retorno por letra usa os seguintes estados:

| Estado | Significado |
| --- | --- |
| `correct` | Letra na posição correta. |
| `present` | Letra encontrada na palavra, mas em outra posição. |
| `absent` | Letra ausente da palavra. |

A palavra secreta aparece no campo `secretWord` da resposta ao palpite que resulta em derrota. Ela não é retornada pelo endpoint de consulta da partida.

## Organização do código

| Arquivo ou pasta | Responsabilidade |
| --- | --- |
| `routes/api.php` | Define os três endpoints do jogo. |
| `app/Http/Controllers/GameController.php` | Cria e consulta partidas, valida e registra palpites. |
| `app/Http/Helpers/GuessHelper.php` | Calcula o resultado por letra. |
| `app/Http/Resources/` | Define os campos das respostas JSON. |
| `app/Models/` | Modelos `Word`, `Game`, `Attempt` e o `User` da estrutura inicial. |
| `database/migrations/` | Estrutura das tabelas do jogo e do Laravel. |
| `bootstrap/app.php` | Configura rotas sem prefixo `/api` e erros de validação com HTTP 400. |
| `app/Providers/AppServiceProvider.php` | Remove o envelope `data` dos resources, libera atribuição em massa e ativa carregamento automático de relacionamentos. |

### Dados do jogo

- `words`: dicionário utilizado tanto no sorteio quanto na validação dos palpites; contém `name`.
- `games`: referência à palavra (`word_id`), estado (`status`) e contadores (`attemptsUsed` e `attemptsRemaining`).
- `attempts`: palavra enviada (`word`) e referência à partida (`game_id`).

Cada partida referencia uma palavra e pode ter várias tentativas. As migrations usam `foreignId`, mas não declaram restrições com `constrained()`. O relacionamento reverso em `Word` está declarado como `hasOne`, embora o sorteio possa reutilizar uma palavra em várias partidas.

## Endpoints

O prefixo da API está vazio em `bootstrap/app.php`. Com `php artisan serve`, a base é `http://127.0.0.1:8000/`, e os endpoints abaixo **não** recebem `/api` antes de `/games`.

| Método | Caminho | Entrada | Resposta principal |
| --- | --- | --- | --- |
| POST | `/games` | Sem corpo obrigatório. | `gameId`, `status`, `attemptsUsed`, `attemptsRemaining`. |
| GET | `/games/{game}` | Identificador na URL. | Dados da partida e `guesses`, com palavra e feedback de cada tentativa. |
| POST | `/games/{game}/guesses` | JSON com `word`. | Estado, contadores e `feedback`; `secretWord` na derrota e `message` ao encerrar. |

Exemplo de corpo para enviar um palpite, desde que a palavra esteja no dicionário:

```json
{
  "word": "TERMO"
}
```

Enviar `Accept: application/json` e, quando houver corpo, `Content-Type: application/json`. As respostas dos resources não possuem um objeto externo `data`.

Erros previstos no código:

- **400**: palpite inválido, com a primeira mensagem de validação em `message`.
- **404**: partida inexistente. A rota GET possui uma mensagem personalizada.
- **409**: tentativa de jogar em uma partida já encerrada.

## Preparação do ambiente local

São necessários PHP compatível com as dependências, Composer, MySQL e a extensão PHP de acesso ao banco. Execute os comandos a partir desta pasta.

1. Instale as dependências e crie o `.env`, caso ainda não exista:

   ```powershell
   composer install
   if (-not (Test-Path .env)) { Copy-Item .env.example .env }
   php artisan key:generate
   ```

2. Crie o banco no MySQL e configure `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` no `.env`. O exemplo usa `DB_DATABASE=example_app`.

3. Crie as tabelas:

   ```powershell
   php artisan migrate
   ```

4. Antes de testar partidas, trate as pendências de inicialização descritas abaixo e abasteça `words` com palavras de cinco letras em maiúsculas, sem acentos, para corresponder à entrada atual do Vue. O `DatabaseSeeder` cria apenas um usuário de exemplo e **não popula o dicionário**.

5. Inicie o serviço:

   ```powershell
   php artisan serve
   ```

6. No projeto `full_front_game`, ajuste `api_url`, em `src/helpers/api.js`, para `http://127.0.0.1:8000/`, preservando a barra final.

O endereço atualmente gravado no frontend é `http://172.17.1.8/BR_client_side/api/`, específico de outro ambiente. Esse caminho de publicação não é um prefixo definido pelas rotas Laravel. O CORS atual permite todas as origens e métodos, sem credenciais.

Os arquivos de Vite e os scripts JavaScript desta pasta pertencem à estrutura Laravel; a interface do jogo está no projeto Vue separado.

## Estado atual e pontos de atenção

A análise do código identifica um protótipo com o fluxo principal implementado e pendências que precisam ser consideradas ao executá-lo:

- **Inicialização da partida:** a migration de `games` exige `attemptsUsed` sem valor padrão, mas a criação informa apenas `attemptsRemaining` e a relação fornece `word_id`. Com o MySQL em modo estrito, essa omissão pode impedir a inserção. `status` também não tem padrão explícito na migration. A criação deve definir `attemptsUsed = 0` e `status = playing`, ou o banco deve fornecer esses valores. Os valores de fallback nos resources apenas formatam a resposta, não inicializam o banco.
- **Dicionário vazio:** não existe tratamento para a ausência de palavras; nesse caso, a criação tenta acessar uma relação em um resultado nulo.
- **Letras repetidas:** o helper verifica presença, mas não desconta ocorrências já utilizadas. Portanto, pode indicar letras repetidas como presentes além da quantidade disponível na palavra secreta.
- **Maiúsculas e acentos:** não há normalização no backend, e a vitória usa comparação estrita. O helper acessa posições da string por byte. O conjunto de palavras deve ser compatível com a entrada A–Z em maiúsculas do frontend.
- **Concorrência:** o registro do palpite e a atualização da partida não estão protegidos por transação ou bloqueio; envios simultâneos podem deixar contadores e histórico inconsistentes.
- **Cobertura de testes:** não há testes específicos para sorteio, validação, contagem de tentativas ou classificação de letras.

O comando previsto para os testes existentes é `composer test`. Esta documentação foi produzida por análise estática dos arquivos; não atesta execução da API nem conexão com o banco.
