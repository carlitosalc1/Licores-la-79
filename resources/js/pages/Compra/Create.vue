<script setup lang="ts">
import { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, router, Head } from '@inertiajs/vue3'

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Compras', href: '/compras' },
    { title: 'Crear Compra', href: '#' },
];

interface Proveedor {
  id: number;
  razon_social: string;
}

interface Usuario {
  id: number;
  name: string;
}

// Props
const props = defineProps<{
  proveedores: Proveedor[];
  usuario: Usuario;
}>();
const form = useForm({
  fecha: '',
  total: 0,
  estado: '',
  proveedor_id: '',
  user_id: props.usuario.id,
});


// Enviar formulario
const submit = () => {
  const totalString = String(form.total).replace(/\./g, ''); // asegura que sea string
  form.total = parseInt(totalString, 10);
  form.post(route('compras.store'));
};
</script>

<template>
  <Head title="Crear Compra" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
      <div class="w-full max-w-3xl">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
          <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Crear Compra</h1>
          <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                 <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha</label>
                <input type="date" v-model="form.fecha"
                 class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
                <p v-if="form.errors.fecha" class="text-red-500 text-sm mt-1">{{ form.errors.fecha }}</p>
              </div>
           <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Total</label>
                <input v-model="form.total" type="text" autocomplete="off"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"/>
                <div v-if="form.errors.total" class="text-red-500 text-sm mt-1">
                  {{ form.errors.total }}
                </div>
              </div>
              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Estado</label>
                <select v-model="form.estado"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                  <option disabled value="">Seleccione una opción</option>
                  <option value="cancelada">cancelada</option>
                  <option value="pagado">Pagada</option>
                </select>
                <div v-if="form.errors.estado" class="text-red-500 text-sm mt-2">
                  {{ form.errors.estado }}
                </div>
              </div>
             <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Proveedor</label>
                <select v-model="form.proveedor_id"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                  <option disabled value="">Seleccione un Proveedor</option>
                  <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                    {{ proveedor.razon_social }}
                  </option>
                </select>
                <div v-if="form.errors.proveedor_id" class="text-red-500 text-sm mt-2">
                  {{ form.errors.proveedor_id }}
                </div>
              </div>
       <!-- Usuario (sólo visualización) -->
              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Usuario</label>
                <input type="text" :value="props.usuario.name"
                 class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3" disabled />
              </div>
            </div>
         <!-- Botones -->
            <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
              <button type="submit" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all" :disabled="form.processing">
                <span v-if="form.processing">Procesando...</span>
                <span v-else>Guardar</span>
              </button>
              <button type="button" @click="router.get('/productos')"
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


