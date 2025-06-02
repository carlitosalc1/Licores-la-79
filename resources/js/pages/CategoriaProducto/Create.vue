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
    { title: 'Categoria de Productos', href: '/categoria_productos' },
    { title: 'Crear Categoria de producto', href: '#' },
];

// Definición del formulario
const form = useForm({
  nombre: '',
  descripcion: '',
})

// Envío del formulario
function submit() {
  form.post('/categoria_productos', {
    onSuccess: () => {
      form.reset()
    }
  })
}
</script>

<template>
     <Head title="Crear Categoria de producto" />
     <AppLayout :breadcrumbs="breadcrumbs">
  <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
    <div class="w-full max-w-3xl">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Crear Categoria de producto</h1>
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
        <div class="md:col-span-2">
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Descripción</label>
                <textarea v-model="form.descripcion" rows="4"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"></textarea>
                <div v-if="form.errors.descripcion" class="text-red-500 text-sm mt-2">
                  {{ form.errors.descripcion }}
                </div>
              </div>
            </div>
      <!-- Botones -->
      <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
           <button type="submit" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all":disabled="form.processing">
               <span v-if="form.processing">Procesando...</span>
               <span v-else>Guardar</span>
           </button>
           <button type="button" @click="router.get('/categoria_productos')"
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
