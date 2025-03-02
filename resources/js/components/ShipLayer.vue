<script setup lang="js">

import { onBeforeUnmount, onMounted, ref } from 'vue';

onMounted(() => {
    window.Echo.channel('ships.latest_positions').listen('ShipsLatestPositionsUpdated', (data) => {
        console.log('Dados recebidos:', data);
    });
});

</script>
<template>
    <h1>Ship Layer</h1>
</template>
