<script setup lang="ts">

import {  usePage } from '@inertiajs/vue3';

const page = usePage();
const quote = page.props.quote;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
        <div class="relative hidden h-full flex-col bg-muted p-10 text-black dark:border-r lg:flex">
  <div class="absolute inset-0 bg-amber-50" />

  <!-- Centramos completamente la imagen -->
  <div class="relative z-20 flex-1 flex items-center justify-center">
    <img src="/img/logo.png" alt="Logo del restaurante" class="w-[32rem] h-[32rem]" />
  </div>

  <div v-if="quote" class="relative z-20 mt-auto text-center">
    <blockquote class="space-y-2">
      <p class="text-2xl  font-bold text-gray-800 mb-2">
        &ldquo;Tus mejores momentos, con el sabor que merecen. La variedad que buscas, al mejor precio. Licores la 79. &rdquo;
      </p>
    </blockquote>
  </div>
</div>

        <div class="lg:p-8">
            <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                <div class="flex flex-col space-y-2 text-center">
                    <h1 class="text-xl font-medium tracking-tight" v-if="title">{{ title }}</h1>
                    <p class="text-sm text-muted-foreground" v-if="description">{{ description }}</p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
