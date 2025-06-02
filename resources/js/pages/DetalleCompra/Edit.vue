<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

interface Producto {
  id: number;
  nombre: string;
}

interface Compra {
  id: number;
}

interface DetalleCompra {
  id: number;
  producto_id: number;
  compra_id: number;
  cantidad: number;
  precio_unitario: number;
  subtotal: number;
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Detalle Compras', href: '/detalle_compras' },
  { title: 'Editar Detalle', href: '#' },
];

const props = defineProps<{
  productos: Producto[];
  compras: Compra[];
  detalle: DetalleCompra;
}>();

const form = useForm({
  producto_id: props.detalle.producto_id,
  compra_id: props.detalle.compra_id,
  cantidad: props.detalle.cantidad,
  precio_unitario: props.detalle.precio_unitario,
  subtotal: props.detalle.subtotal,
});

const calcularSubtotal = () => {
  const cantidad = Number(form.cantidad);
  const precio_unitario = Number(form.precio_unitario);
  form.subtotal = cantidad * precio_unitario;
};

const precioUnitarioVisible = computed({
  get: () => {
    return form.precio_unitario
      ? Number(form.precio_unitario).toLocaleString('es-CO')
      : '';
  },
  set: (value: string) => {
    const raw = value.replace(/\./g, '').replace(/[^0-9]/g, '');
    form.precio_unitario = Number(raw);
    calcularSubtotal();
  },
});

// Recalcular subtotal si cantidad cambia
watch(() => form.cantidad, calcularSubtotal);

const submit = () => {
  calcularSubtotal();
  form.put(`/detalle_compras/${props.detalle.id}`);
};
</script>

<template>
  <Head title="Editar Detalle Compra" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
      <div class="w-full max-w-3xl">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
          <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Editar Detalle de Compra</h1>
          <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Producto -->
              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Producto</label>
                <select v-model="form.producto_id"
                        class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3">
                  <option disabled value="">Seleccione un producto</option>
                  <option v-for="producto in props.productos" :key="producto.id" :value="producto.id">
                    {{ producto.nombre }}
                  </option>
                </select>
                <p v-if="form.errors.producto_id" class="text-red-500 text-sm mt-1">{{ form.errors.producto_id }}</p>
              </div>

              <!-- Compra -->
              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Compra</label>
                <select v-model="form.compra_id"
                        class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3">
                  <option disabled value="">Seleccione una compra</option>
                  <option v-for="compra in props.compras" :key="compra.id" :value="compra.id">
                    Compra #{{ compra.id }}
                  </option>
                </select>
                <p v-if="form.errors.compra_id" class="text-red-500 text-sm mt-1">{{ form.errors.compra_id }}</p>
              </div>

              <!-- Cantidad -->
              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Cantidad</label>
                <input type="number" min="1" v-model="form.cantidad"
                       class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3" />
                <p v-if="form.errors.cantidad" class="text-red-500 text-sm mt-1">{{ form.errors.cantidad }}</p>
              </div>

              <!-- Precio -->
              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Precio Unitario</label>
                <input v-model="precioUnitarioVisible" type="text" autocomplete="off"
                       class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3" />
                <p v-if="form.errors.precio_unitario" class="text-red-500 text-sm mt-1">{{ form.errors.precio_unitario }}</p>
              </div>

              <!-- Subtotal (solo lectura) -->
              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Subtotal</label>
                <input :value="form.subtotal.toLocaleString('es-ES', { minimumFractionDigits: 0 })" disabled
                       class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3" />
              </div>
            </div>

            <!-- Botones -->
            <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
              <button type="submit"
                      class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all"
                      :disabled="form.processing">
                <span v-if="form.processing">Procesando...</span>
                <span v-else>Actualizar</span>
              </button>
              <button type="button" @click="router.get('/detalle_compras')"
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
