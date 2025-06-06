<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, usePage, router, Head } from '@inertiajs/vue3';


// Definimos la interfaz para BreadcrumbItem
interface BreadcrumbItem {
    title: string;
    href: string;
}

// Definimos la interfaz para Proveedor
interface Proveedor {
    id: number;
    razon_social: string;
    nit: string;
    direccion: string;
    telefono: string;
    correo: string;
}
// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Proveedores', href: '/proveedors' },
    { title: 'Editar Proveedor', href: '#' },
];

// Obtener el proveedor desde los props de Inertia y tiparlo
const page = usePage<{ proveedor?: Proveedor }>();  // El "Proveedor" puede no existir.
const proveedor = page.props.proveedor;


// Formulario con datos prellenados
const form = useForm({
    razon_social: proveedor?.razon_social || '',
    nit: proveedor?.nit || '',
    direccion: proveedor?.direccion || '',
    telefono: proveedor?.telefono || '',
    correo: proveedor?.correo || '',
});

// Envío del formulario (solo PUT para edición)
const submit = () => {
    form.put(`/proveedors/${proveedor?.id}`, {
        onSuccess: () => router.visit('/proveedors')// Redirige al listado

    });
};
</script>

<template>
        <Head title="Editar Proveedor" />
     <AppLayout :breadcrumbs="breadcrumbs">
  <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
    <div class="w-full max-w-3xl">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Editar Proveedor</h1>

        <form @submit.prevent="submit" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Razón_social</label>
          <input v-model="form.razon_social" type="text" autocomplete="off"
            class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
          <div v-if="form.errors.razon_social" class="text-red-500 text-sm mt-2">
            {{ form.errors.razon_social }}
          </div>
        </div>

        <div>
          <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Nit</label>
          <input v-model="form.nit" type="text" autocomplete="off"
            class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
          <div v-if="form.errors.nit" class="text-red-500 text-sm mt-2">
            {{ form.errors.nit }}
          </div>
        </div>

        <div>
          <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Dirección</label>
          <input v-model="form.direccion" type="text" autocomplete="off"
            class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
          <div v-if="form.errors.direccion" class="text-red-500 text-sm mt-2">
            {{ form.errors.direccion }}
          </div>
        </div>

        <div>
          <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Teléfono</label>
          <input v-model="form.telefono" type="text" inputmode="tel" autocomplete="off" @input="form.telefono = form.telefono.replace(/[^\d+\-()\s]/g, '')"
           class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
         <div v-if="form.errors.telefono" class="text-red-500 text-sm mt-2">
           {{ form.errors.telefono }}
         </div>
        </div>

        <div class="md:col-span-2">
          <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
          <input v-model="form.correo" type="email" autocomplete="off"
            class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
          <div v-if="form.errors.correo" class="text-red-500 text-sm mt-2">
            {{ form.errors.correo }}
          </div>
        </div>
      </div>

   <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
            <button type="submit" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all":disabled="form.processing">
                <span v-if="form.processing">Procesando...</span>
                <span v-else>Actualizar</span>
            </button>
            <button type="button" @click="router.get('/proveedors')" class="flex-1 md:flex-none px-6 py-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-700 dark:hover:bg-gray-500 font-medium transition-all">
                 Cancelar
            </button>
      </div>
    </form>
  </div>
</div>
</div>
</AppLayout>
</template>
