<script setup>
const props = defineProps(['loading']);
</script>
<template>
    <div :class="`px-4 py-3 bg-gray-50 dark:bg-gray-800 rounded ${loading ? 'animate-pulse min-h-[100px]' : ''}`">
        <slot></slot>
    </div>
</template>