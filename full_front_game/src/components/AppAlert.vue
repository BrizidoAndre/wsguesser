<script setup>

import {alerts} from "@/helpers/addAlert.js";
</script>

<template>
  <transition-group>
    <div class="message show error" v-for="alert in alerts" :key="alert.id">
      {{ alert.message }}
    </div>
  </transition-group>
</template>

<style scoped>
.v-enter-active,
.v-leave-active,
.v-move {
  transition: .4s;
}

.v-leave-active {
  position: absolute;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}

.message {
  margin-bottom: 1rem;
}
</style>