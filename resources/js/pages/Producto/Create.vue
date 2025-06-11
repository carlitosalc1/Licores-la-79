<script setup lang="ts">
import { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, router, Head } from '@inertiajs/vue3'

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Productos', href: '/productos' },
    { title: 'Crear Producto', href: '#' },
];

interface CategoriaProducto {
  id: number;
  nombre: string;
}

defineProps<{
  categoriadeproductos: CategoriaProducto[]
}>()
// Definición del formulario
const form = useForm({
  nombre: '',
  descripcion: '',
  precio_compra: 0,
  precio_venta: 0,
  unidad_medida: '',
  stock: 0,
  stock_minimo: 0,
  categoria_producto_id: '',
} as const)

// Envío del formulario
function submit() {
  form.transform((data) => ({
    ...data,
    categoria_producto_id: Number(data.categoria_producto_id),
    precio_compra: parseInt(String(data.precio_compra).replace(/\./g, ''), 10),
    precio_venta: parseInt(String(data.precio_venta).replace(/\./g, ''), 10),
  })).post('/productos', {
    onSuccess: () => {
      form.reset();
    },
  });
}
</script>

<template>
  <Head title="Crear Producto" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
      <div class="w-full max-w-3xl">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
          <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Crear Producto</h1>

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

              <div>
                 <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Precio de compra</label>
                  <input type="text" v-model="form.precio_compra"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
                  <p v-if="form.errors.precio_compra" class="text-red-500 text-sm mt-1">{{ form.errors.precio_compra }}</p>
               </div>

               <div>
                 <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Precio de venta</label>
                 <input type="text" v-model="form.precio_venta"
                 class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
                <p v-if="form.errors.precio_venta" class="text-red-500 text-sm mt-1">{{ form.errors.precio_venta }}</p>
              </div>

              <div>
                 <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Unidad de medida</label>
                  <select v-model="form.unidad_medida" class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                  <option disabled value="">Seleccione una opción</option>
                  <option value="Kilogramos">Kilogramos</option>
                  <option value="Gramos">Gramos</option>
                  <option value="Galón">Galón</option>
                  <option value="Litros">Litros</option>
                  <option value="Mililitros">Mililitros</option>
                  </select>
              <div v-if="form.errors.unidad_medida" class="text-red-500 text-sm mt-2">
                      {{ form.errors.unidad_medida }}
              </div>
             </div>

              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Stock</label>
                <input v-model="form.stock" type="number" min="0"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"/>
                <div v-if="form.errors.stock" class="text-red-500 text-sm mt-2">
                  {{ form.errors.stock }}
                </div>
              </div>

              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Stock Mínimo</label>
                <input v-model="form.stock_minimo" type="number" min="0"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"/>
                <div v-if="form.errors.stock_minimo" class="text-red-500 text-sm mt-2">
                  {{ form.errors.stock_minimo }}
                </div>
              </div>

              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Categoría</label>
                <select v-model="form.categoria_producto_id"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                  <option disabled value="">Seleccione una categoría</option>
                  <option v-for="categoriadeproducto in categoriadeproductos" :key="categoriadeproducto.id" :value="categoriadeproducto.id">
                    {{ categoriadeproducto.nombre }}
                  </option>
                </select>
                <div v-if="form.errors.categoria_producto_id" class="text-red-500 text-sm mt-2">
                  {{ form.errors.categoria_producto_id }}
                </div>
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
