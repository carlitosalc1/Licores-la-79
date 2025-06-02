<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
     <img
      src="/img/logo.png"
      alt="Logo de la licoreria"
      :class="className"
      v-bind="$attrs"
    />
</template>
