<script setup>
import {computed, onMounted, onUnmounted, reactive, ref} from "vue";
import AppAlert from "@/components/AppAlert.vue";
import {addAlert} from "@/helpers/addAlert.js";
import {api} from "@/helpers/api.js";
import {gameId} from "@/helpers/global.js";

const gameInfo = reactive({
  won: false,
  gameover: false,
  secret: '',
});

const keyRows = [
  [
    'Q',
    'W',
    'E',
    'R',
    'T',
    'Y',
    'U',
    'I',
    'O',
    'P',
  ],
  [
    'A',
    'S',
    'D',
    'F',
    'G',
    'H',
    'J',
    'K',
    'L',
    '⌫',
  ],
  [
    'Z',
    'X',
    'C',
    'V',
    'B',
    'N',
    'M',
    'ENTER'
  ]
];
const maxGuesses = 6;
const wordLength = 5;
const guesses = ref([]);
const feedbacks = ref([]);
const keysGuessed = computed(() => {
  return [...feedbacks.value].map(f => f.feedback);
})
const type = ref('');


const showRightClassKey = (keyTarget) => {
  let value = '';
  keysGuessed.value.forEach(keys => {
    return keys.forEach(key => {
      if (key.letter === keyTarget) {
        if (key.status === 'correct') {
          value = 'correct';
        } else if (key.status === 'present') {
          value = 'present';
        } else if (key.status === 'absent') {
          value = 'absent';
        }
      }
      if (!value) {
        value = '';
      }
    })
  })
  return value;
}
const showRightClassGuess = (rowIndex, keyIndex) => {
  if (rowIndex === guesses.value.length && !gameInfo.gameover) {
    return 'active';
  }
  const guess = guesses.value[rowIndex];
  if (guess) {
    const feedback = feedbacks.value[rowIndex];
    const feedbackLetter = feedback.feedback[keyIndex];
    return feedbackLetter.status;
  }

  return '';
}
const showRightLetter = (rowIndex, keyIndex) => {
  if (rowIndex < guesses.value.length) {
    return guesses.value[rowIndex][keyIndex]
  } else if (rowIndex === guesses.value.length) {
    return type.value[keyIndex];
  }
  return '';
}
const typeKey = (key) => {
  if (gameInfo.gameover) {
    return;
  }
  // what happens when the user press the backspace
  if (key === '⌫') {
    type.value = type.value.slice(0, type.value.length - 1);
  } else if (key === 'ENTER') {
    // what happens when the user press the enter key
    guess();
  } else if (type.value.length < 5) {
    type.value += key;
  }
}

async function createGame() {
  const [res, data] = await api.post('games');
  gameId.value = data.gameId;
  guesses.value = [];
  feedbacks.value = [];
  type.value = '';
  gameInfo.gameover = false;
  gameInfo.won = false;
}

async function loadGame() {
  const [res, data] = await api.get('games/' + gameId.value);
  guesses.value = data.guesses.map(g => g.word.toUpperCase());
  checkStatus(data.status);
  feedbacks.value = data.guesses;
}

function checkStatus(status) {
  if (status !== 'playing') {
    gameInfo.gameover = true;
    gameInfo.won = status === 'won';
  }
}

async function guess() {
  const body = {
    'word': type.value
  }
  const [res, data] = await api.post(`games/${gameId.value}/guesses`, {...body})
  if (!res.ok) {
    addAlert(data.message)
  } else {
    checkStatus(data.status);
    if (data.status === 'lost') {
      gameInfo.secret = data.secretWord;
    }
    guesses.value.push(type.value);
    const feedFormat = {}
    feedFormat.feedback = data.feedback;
    feedFormat.word = data.feedback.map(f => f.letter).join('');
    feedbacks.value.push(feedFormat);
    type.value = '';
  }
}

function showStatus() {
  if (gameInfo.won) {
    return 'Won';
  } else if (gameInfo.gameover) {
    return 'Game over';
  } else {
    return 'Playing';
  }
}

const keyDown = (ev) => {
  let {key} = ev;
  if (/^[a-z]$/i.test(key)) {
    typeKey(key.toUpperCase())
  } else if (key === 'Enter') {
    typeKey('ENTER')
  } else if ('Backspace') {
    typeKey('⌫')
  }
}
onMounted(() => {
  window.addEventListener('keydown', keyDown);
  if (gameId.value) {
    loadGame()
  } else {
    createGame();
  }
})
onUnmounted(() => {
  window.removeEventListener('keydown', keyDown);
})
</script>

<template>
  <div class="message-container">
    <app-alert></app-alert>
    <div class="message show success" v-if="gameInfo.won">
      <p>Congratulations! You guessed the word.</p>
    </div>
    <div class="message show info" v-else-if="gameInfo.gameover">
      <p>Game over.</p>
      <p v-if="gameInfo.secret">The secret word was {{ gameInfo.secret }}</p>
    </div>
  </div>

  <main class="game">

    <!-- Header -->
    <header class="game-header">
      <h1 class="logo">WGUESSER</h1>
      <p class="bolder">Status: {{ showStatus() }}</p>
    </header>

    <!-- Board -->
    <section class="board">

      <div
          v-for="(row,rowIndex) in maxGuesses"
          class="board-row"
      >
        <div
            v-for="(key,keyIndex) in wordLength"
            class="cell"
            :class="[
              showRightClassGuess(rowIndex,keyIndex),
              {
                'submitted': rowIndex < guesses.length,
              }
            ]"
            :style="{
              animationDelay: keyIndex * .1 + 's'
            }"
        >
          {{ showRightLetter(rowIndex, keyIndex) }}
        </div>
      </div>
    </section>
    <div class="center" v-if="gameInfo.gameover">
      <button class="btn btn-primary" @click="createGame">Restart</button>
    </div>
    <!-- Keyboard -->
    <section class="keyboard">
      <div
          v-for="row in keyRows"
          class="keyboard-row"
      >
        <button
            v-for="key in row"
            class="key"
            :class="[
                {
                  'key-enter': key === 'ENTER',
                  'key-delete': key === '⌫',
                },
                showRightClassKey(key)
            ]"
            @click="typeKey(key)"
        >
          {{ key }}
        </button>
      </div>

    </section>

  </main>
</template>

<style scoped>
.submitted {
  opacity: 0;
  animation: pulse .5s ease-out forwards;
}

@keyframes pulse {
  from {
    opacity: 0;
    transform: scale(.6);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>
