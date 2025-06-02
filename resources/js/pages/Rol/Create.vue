<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, router, Head } from '@inertiajs/vue3'

// Definimos la interfaz para BreadcrumbItem
interface BreadcrumbItem {
    title: string;
    href: string;
}

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Roles', href: '/rols' },
    { title: 'Crear Roles', href: '#' },
];

// Definición del formulario
const form = useForm({
  nombre: '',
})

// Envío del formulario
function submit() {
  form.post('/rols', {
    onSuccess: () => {
      form.reset()
    }
  })
}
</script>

<template>
     <Head title="Crear Rol" />
     <AppLayout :breadcrumbs="breadcrumbs">
  <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
    <div class="w-full max-w-3xl">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Crear Rol</h1>
    <form @submit.prevent="submit" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
         <div>
          <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre</label>
          <input v-model="form.nombre" type="text" autocomplete="off"
            class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"/>
          <div v-if="form.errors.nombre" class="text-red-500 text-sm mt-2">
            {{ form.errors.nombre }}
          </div>
        </div>
      </div>

      <!-- Botones -->
      <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
           <button type="submit" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all":disabled="form.processing">
               <span v-if="form.processing">Procesando...</span>
               <span v-else>Guardar</span>
           </button>
           <button type="button" @click="router.get('/rols')"
          class="flex-1 md:flex-none px-6 py-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition-all">
          Cancelar
        </button>
      </div>
    </form>
   </div>
  </div>
  </div>
</AppLayout>
</template>
