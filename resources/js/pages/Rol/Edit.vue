<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, usePage, router, Head } from '@inertiajs/vue3';


// Definimos la interfaz para BreadcrumbItem
interface BreadcrumbItem {
    title: string;
    href: string;
}

// Definimos la interfaz para Rol
interface Rol {
    id: number;
    nombre: string;
}
// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Roles', href: '/rols' },
    { title: 'Editar Rol', href: '#' },
];

// Obtener el rol desde los props de Inertia y tiparlo
const page = usePage<{ rol?: Rol}>();  // el "rol" puede no existir.
const rol = page.props.rol;


// Formulario con datos prellenados
const form = useForm({
    nombre: rol?.nombre || '',
});

// Envío del formulario (solo PUT para edición)
const submit = () => {
    form.put(`/rols/${rol?.id}`, {
        onSuccess: () => router.visit('/rols')// Redirige al listado
    });
};
</script>

<template>
        <Head title="Editar Rol" />
     <AppLayout :breadcrumbs="breadcrumbs">
  <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
    <div class="w-full max-w-3xl">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Editar Rol</h1>

       <form @submit.prevent="submit" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
      </div>

      <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
            <button type="submit" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all":disabled="form.processing">
                <span v-if="form.processing">Procesando...</span>
                <span v-else>Actualizar</span>
            </button>
            <button type="button" @click="router.get('/rols')" class="flex-1 md:flex-none px-6 py-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-700 dark:hover:bg-gray-500 font-medium transition-all">
                 Cancelar
            </button>
      </div>
    </form>
  </div>
</div>
</div>
</AppLayout>
</template>
