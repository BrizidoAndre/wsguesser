# WGUESSER — Projeto fullstack

## Proposta

O WGUESSER é um jogo de adivinhação de palavras no estilo Termo/Wordle. O jogador precisa descobrir uma palavra de cinco letras em até seis tentativas. A cada palpite aceito, recebe indicações sobre quais letras estão na posição correta, quais estão presentes em outra posição e quais não fazem parte da palavra.

O projeto reúne uma API Laravel e uma interface Vue.js. Essa divisão mantém as regras e os dados das partidas no backend, enquanto o frontend cuida da interação com o jogador.

## Projetos

| Pasta | Responsabilidade | Documentação |
| --- | --- | --- |
| `full_api_game` | API em Laravel: sorteia palavras, valida e registra palpites, armazena partidas e determina vitória ou derrota. | [README da API Laravel](full_api_game/README.md) |
| `full_front_game` | Interface em Vue.js: apresenta tabuleiro e teclado, envia palpites e exibe o resultado de cada tentativa. | [README da interface Vue.js](full_front_game/README.md) |

## Como as partes se conectam

1. A interface solicita uma nova partida à API, que sorteia uma palavra do dicionário no banco de dados.
2. O jogador envia seus palpites pelo teclado virtual ou físico.
3. A API valida cada palavra, registra a tentativa e devolve o feedback por letra e o estado da partida.
4. A interface atualiza o tabuleiro e apresenta a vitória ou a derrota, permitindo reiniciar ao final.

O identificador da partida fica salvo no navegador para que a interface consulte o histórico na API ao recarregar a página. O funcionamento depende dos dois projetos e do banco de dados.

As partidas são individuais, com uma palavra sorteada a cada nova partida. O fluxo atual não inclui login, ranking, multiplayer ou desafio diário.

## Por onde começar

1. Consulte o [README da API Laravel](full_api_game/README.md) para preparar o ambiente PHP, configurar o banco de dados e entender os endpoints. Observe as pendências de inicialização das partidas e de preenchimento do dicionário.
2. Consulte o [README da interface Vue.js](full_front_game/README.md) para instalar as dependências, configurar o endereço da API e iniciar a interface.

Os READMEs de cada projeto detalham as tecnologias, a organização do código, as instruções de execução e as limitações identificadas na implementação atual.
