<script setup lang="ts">
import { type BreadcrumbItem } from '@/types';
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Proveedor, Producto, } from '@/types';
import InputError from '@/components/InputError.vue';

interface Usuario {
  id: number;
  name: string;
}
const props = defineProps<{
  proveedores: Proveedor[];
  productos: Producto[];
  usuario: Usuario;
}>();

const form = useForm({
  proveedor_id: null,
  fecha_compra: '',
  user_id: props.usuario.id,
  estado: 'pagado',
  detalles: [] as {
  producto_id: number;
  nombre: string;
  cantidad: number;
  precio: number;
  subtotal: number;
  impuesto_iva: number; // Assuming 19% IVA based on your calculation
  total: number;
  }[],
  total_iva: 0,
  total_compra: 0,
});

const productoSeleccionado = ref<Producto | null>(null);

function agregarProducto() {
  if (!productoSeleccionado.value) return;

  const producto = productoSeleccionado.value;
  const cantidad = 1; // Default quantity
  // *** Importante: Asegurarse de que precio_compra sea siempre un número ***
  const precio_compra_num = Number(producto.precio_compra);

  if (isNaN(precio_compra_num)) {
    console.error("Error: el precio de compra del producto no es un número válido:", producto.precio_compra);
    return; // Detener la función si el precio no es válido
  }
  const subtotal = cantidad * precio_compra_num;
  const iva = subtotal * 0.19; // Calculate IVA (19%)
  const total = subtotal + iva;

  form.detalles.push({
    producto_id: producto.id,
    nombre: producto.nombre,
    cantidad,
    precio: precio_compra_num, // Usar el precio convertido a número
    subtotal,
    impuesto_iva: iva,
    total,
  });

  productoSeleccionado.value = null; // Reset selected product after adding
}

function recalcularTotales(index: number) {
  const item = form.detalles[index];
  // Ensure quantity is a number and at least 1
  item.cantidad = Math.max(1, Number(item.cantidad) || 1);

  item.subtotal = item.cantidad * item.precio;
  item.impuesto_iva = item.subtotal * 0.19;
  item.total = item.subtotal + item.impuesto_iva;
}

// Computed properties for overall totals
const totalIVA = computed(() =>
  form.detalles.reduce((acc, d) => acc + d.impuesto_iva, 0)
);

const totalCompra = computed(() =>
  form.detalles.reduce((acc, d) => acc + d.total, 0)
);

// Watch for changes in totalIVA and totalCompra to update form fields
watch(totalIVA, (newValue) => {
  form.total_iva = newValue;
});

watch(totalCompra, (newValue) => {
  form.total_compra = newValue;
});

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Compras', href: '/compras' },
  { title: 'Crear Compra', href: '#' },
];

function submit() {
  form.post(route('compras.store'), {
    onSuccess: () => {
      router.visit(route('compras.index')); // Redirect to sales index on success
    },
    onError: (errors) => {
      console.error("Error al crear la compra:", errors);
    },
  });
}

// Helper function for currency formatting (no decimals, thousands separator)
const formatCurrency = (value: number) => {
  return Number(value).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  });
};
</script>

<template>
  <Head title="Crear Compra" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
      <div class="w-full max-w-4xl">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
          <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Crear Nueva Compra</h1>

          <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="proveedor_id">Proveedor</label>
                <select id="proveedor_id" v-model="form.proveedor_id"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                  <option disabled :value="null">Seleccione un Proveedor</option>
                  <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                    {{ proveedor.razon_social }}
                  </option>
                </select>
                <InputError :message="form.errors.proveedor_id" class="mt-2" />
              </div>

              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha</label>
                <input type="date" v-model="form.fecha_compra"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
                <p v-if="form.errors.fecha_compra" class="text-red-500 text-sm mt-1">{{ form.errors.fecha_compra }}</p>
              </div>

              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="estado">Estado</label>
                <select id="estado" v-model="form.estado"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                  <option value="pagado">Pagada</option>
                  <option value="cancelada">Cancelada</option>
                </select>
                <InputError :message="form.errors.estado" class="mt-2" />
              </div>


              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Cajero</label>
                <input type="text" :value="props.usuario.name" disabled
                  class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3" />
                <InputError :message="form.errors.user_id" class="mt-2" />
              </div>
            </div>

            <div class="mt-6">
              <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Detalles de Compra</h2>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 items-center">
                <select v-model="productoSeleccionado"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                  <option disabled :value="null">Seleccione un producto</option>
                  <option v-for="producto in productos" :key="producto.id" :value="producto">
                    {{ producto.nombre }} - {{ formatCurrency(producto.precio_compra) }}
                  </option>
                </select>
                <button type="button" @click="agregarProducto"
                  class="px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all">
                  Agregar Producto
                </button>
              </div>

              <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="min-w-full w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                  <thead class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-zinc-700 dark:text-gray-400">
                    <tr>
                      <th class="p-3 text-left">Producto</th>
                      <th class="p-3 text-center">Cantidad</th>
                      <th class="p-3 text-right">Precio Unitario</th>
                      <th class="p-3 text-right">Subtotal</th>
                      <th class="p-3 text-right">IVA (19%)</th>
                      <th class="p-3 text-right">Total</th>
                      <th class="p-3 text-center">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="form.detalles.length === 0">
                      <td colspan="7" class="p-4 text-center text-gray-500 dark:text-gray-400">No hay productos agregados.</td>
                    </tr>
                    <tr v-for="(detalle, index) in form.detalles" :key="index"
                      class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                      <td class="p-3">{{ detalle.nombre }}</td>
                      <td class="p-3 text-center">
                        <input type="number" v-model.number="detalle.cantidad" min="1"
                          class="w-20 text-center rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 p-2 focus:ring-1 focus:ring-cyan-500 focus:border-transparent transition-all"
                          @input="recalcularTotales(index)" />
                      </td>
                      <td class="p-3 text-right">{{ formatCurrency(detalle.precio) }}</td>
                      <td class="p-3 text-right">{{ formatCurrency(detalle.subtotal) }}</td>
                      <td class="p-3 text-right">{{ formatCurrency(detalle.impuesto_iva) }}</td>
                      <td class="p-3 text-right">{{ formatCurrency(detalle.total) }}</td>
                      <td class="p-3 text-center">
                        <button type="button" @click="form.detalles.splice(index, 1)"
                          class="font-medium text-red-600 dark:text-red-500 hover:underline">
                          Eliminar
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="text-right mt-6 space-y-2 text-gray-800 dark:text-white">
                <p class="text-lg"><strong>Total IVA:</strong> {{ formatCurrency(totalIVA) }}</p>
                <p class="text-xl font-bold"><strong>Total Compra:</strong> {{ formatCurrency(totalCompra) }}</p>
              </div>
            </div>

            <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
              <button type="submit"
                class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all"
                :disabled="form.processing">
                <span v-if="form.processing">Procesando...</span>
                <span v-else>Guardar Compra</span>
              </button>
              <button type="button" @click="router.get(route('compras.index'))"
                class="flex-1 md:flex-none px-6 py-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition-all">
                Cancelar
              </button>
            </div>
            <div v-if="form.hasErrors" class="mt-4 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 rounded-lg">
              <h3 class="font-semibold mb-2">Se encontraron los siguientes errores:</h3>
              <ul class="list-disc list-inside">
                <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
              </ul>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
