import {ref, watch} from "vue";

export const gameId = ref(null);

function syncStorage(value, key) {
    const localKey = key + '_br';
    if (localStorage[localKey]) {
        value.value = JSON.parse(localStorage[localKey]);
    }
    watch(value, value => {
        localStorage[localKey] = JSON.stringify(value);
    })
}

syncStorage(gameId, 'game_id');