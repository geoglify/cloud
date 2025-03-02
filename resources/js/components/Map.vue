<script setup lang="js">
import MapboxDraw from '@mapbox/mapbox-gl-draw';
import '@mapbox/mapbox-gl-draw/dist/mapbox-gl-draw.css';
import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import ShipLayer from './ShipLayer.vue';

// Override Mapbox Draw constants to use Maplibre GL classes
MapboxDraw.constants.classes.CONTROL_BASE = 'maplibregl-ctrl';
MapboxDraw.constants.classes.CONTROL_PREFIX = 'maplibregl-ctrl-';
MapboxDraw.constants.classes.CONTROL_GROUP = 'maplibregl-ctrl-group';

// Reference for the map container element
const mapContainer = ref(null);
let map = null;

onMounted(() => {
    if (!mapContainer.value) return;

    // Initialize the map
    map = new maplibregl.Map({
        container: mapContainer.value,
        style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
        center: [0, 0],
        zoom: 1,
    });
});

onBeforeUnmount(() => {
    if (map) {
        map.remove();
    }
});
</script>

<template>
    <div class="h-full w-full">
        <div ref="mapContainer" class="h-full w-full"></div>
    </div>
    
    <ShipLayer />
</template>
