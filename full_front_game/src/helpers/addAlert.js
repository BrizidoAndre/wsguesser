import {ref} from "vue";

export const alerts = ref([]);

export const addAlert = (message) => {
    const id = Math.random();
    alerts.value.push({
        id,
        message,
    })
    setTimeout(() => {
        alerts.value = alerts.value.filter(a => a.id !== id);
    }, 3000)
}