<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
// Importa tus componentes de UI para alertas (e.g., de Shadcn Vue o tu propio sistema)
// import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

// Importa el tipo SharedData si lo tienes definido en '@/types'
// Asegúrate de que SharedData ya extiende PageProps de Inertia
import { type SharedData } from '@/types'; // <--- ¡Añade esta línea!

// Tipar page.props con SharedData
const page = usePage<SharedData>(); // <--- ¡Modifica esta línea!

const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

// Opcional: auto-ocultar mensajes después de un tiempo
// watch([successMessage, errorMessage], () => {
//     if (successMessage.value || errorMessage.value) {
//         setTimeout(() => {
//             page.props.flash = {};
//         }, 5000); // Ocultar después de 5 segundos
//     }
// });
</script>

<template>
    <div v-if="successMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-md" role="alert">
        <p>{{ successMessage }}</p>
    </div>

    <div v-if="errorMessage" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md" role="alert">
        <p>{{ errorMessage }}</p>
    </div>
</template>
