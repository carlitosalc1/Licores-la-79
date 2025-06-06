<script setup lang="ts">
import { type BreadcrumbItem } from '@/types';
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Head } from '@inertiajs/vue3'; // Asegúrate de importar Head

// Definiciones de tipos para evitar errores de TypeScript.
interface Cliente {
  id: number;
  nombre: string;
}

interface Producto {
  id: number;
  nombre: string;
  precio_venta: number; // Este es el precio del producto en sí
}

interface Usuario {
  id: number;
  name: string;
}

interface DetalleVenta {
  id?: number;
  producto_id: number;
  producto?: Producto;
  nombre: string;
  cantidad: number;
  precio_unitario: number; // Cambiado de 'precio' a 'precio_unitario' para coincidir con el backend
  subtotal: number;
  impuesto_iva: number;
  total: number;
}

interface Venta {
  id: number;
  cliente_id: number;
  cliente?: Cliente;
  metodo_pago: string;
  fecha_venta: string;
  tipo_comprobante: string;
  user_id: number;
  user: Usuario;
  estado: string;
  detalle_ventas: DetalleVenta[];
  total_iva: number;
  total_venta: number;
  total: number; // Añadido si tu modelo Venta tiene un campo 'total' general
}

const props = defineProps<{
  clientes: Cliente[];
  productos: Producto[];
  usuario: Usuario;
  venta: Venta;
}>();

// Inicializar el formulario con los datos de la venta existente
const form = useForm({
  cliente_id: props.venta.cliente_id,
  metodo_pago: props.venta.metodo_pago,
  fecha_venta: props.venta.fecha_venta,
  tipo_comprobante: props.venta.tipo_comprobante,
  user_id: props.venta.user_id,
  estado: props.venta.estado,
  detalles: props.venta.detalle_ventas.map((detalle: DetalleVenta) => ({
   producto_id: detalle.producto_id,
    nombre: detalle.producto?.nombre || 'Producto Desconocido',
    cantidad: Number(detalle.cantidad) || 1,
    precio: Number(detalle.precio_unitario) || 0,
    subtotal: Number(detalle.subtotal) || 0,
    impuesto_iva: Number(detalle.impuesto_iva) || 0,
    total: Number(detalle.total) || (Number(detalle.subtotal) + Number(detalle.impuesto_iva)) // ← Cálculo de respaldo
  })),
  total_iva: Number(props.venta.total_iva) ||
            props.venta.detalle_ventas.reduce((sum, d) => sum + Number(d.impuesto_iva), 0),
  total_venta: Number(props.venta.total) ||
              props.venta.detalle_ventas.reduce((sum, d) => sum + Number(d.total), 0)
});

const productoSeleccionado = ref<Producto | null>(null);
const successMessage = ref<string | null>(null);

function agregarProducto() {
  if (!productoSeleccionado.value) return;

  const producto = productoSeleccionado.value;
  const cantidad = 1;
  const precio_venta_num = Number(producto.precio_venta);

  if (isNaN(precio_venta_num)) {
    console.error("Precio no válido:", producto.precio_venta);
    return;
  }

  const subtotal = cantidad * precio_venta_num;
  const iva = subtotal * 0.19;
  const total = subtotal + iva;

  form.detalles.push({
    producto_id: producto.id,
    nombre: producto.nombre,
    cantidad,
    precio: precio_venta_num,
    subtotal,
    impuesto_iva: iva,
    total,
  });

  // Actualizar totales generales
  form.total_iva += iva;
  form.total_venta += total;

  productoSeleccionado.value = null;
}

function recalcularTotales(index: number) {
  const item = form.detalles[index];

  // Validar valores numéricos
  item.cantidad = Math.max(1, Number(item.cantidad)) || 1;
  item.precio = Math.max(0, Number(item.precio)) || 0;

  // Recalcular todo
  item.subtotal = item.cantidad * item.precio;
  item.impuesto_iva = item.subtotal * 0.19;
  item.total = item.subtotal + item.impuesto_iva; // ← Esto corrige el 0

  // Actualizar totales generales
  form.total_iva = form.detalles.reduce((sum, d) => sum + d.impuesto_iva, 0);
  form.total_venta = form.detalles.reduce((sum, d) => sum + d.total, 0);
}

const totalIVA = computed(() =>
  form.detalles.reduce((acc, d) => acc + d.impuesto_iva, 0)
);

const totalVenta = computed(() =>
  form.detalles.reduce((acc, d) => acc + d.total, 0)
);


const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Ventas', href: '/ventas' },
  { title: 'Editar Venta', href: '#' },
];

function submit() {
  // Asegurarse de que los totales computados estén actualizados en el formulario antes de enviar
  form.total_iva = totalIVA.value;
  form.total_venta = totalVenta.value;

  form.put(route('ventas.update', props.venta.id), {
    onSuccess: () => {
      successMessage.value = '¡Venta actualizada exitosamente!';
      setTimeout(() => {
        successMessage.value = null;
        router.visit(route('ventas.index')); // Redirigir al índice de ventas en caso de éxito
      }, 3000);
    },
    onError: (errors) => {
      console.error("Error al actualizar la venta:", errors);
      // Los errores ya se muestran a través de form.hasErrors y InputError
    },
  });
}

const formatCurrency = (value: number) => {
  const numValue = Number(value);
  if (isNaN(numValue)) return '$0';

  return numValue.toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  });
};
</script>

<template>
  <Head title="Editar Venta" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
      <div class="w-full max-w-4xl">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
          <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Editar Venta #{{ venta.id }}</h1>

          <form @submit.prevent="submit" class="space-y-6">
            <div v-if="successMessage" class="mt-4 p-4 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200 rounded-lg">
              {{ successMessage }}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="cliente_id">Cliente</label>
                <select id="cliente_id" v-model="form.cliente_id"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                  <option disabled :value="null">Seleccione un cliente</option>
                  <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                    {{ cliente.nombre }}
                  </option>
                </select>
                <InputError :message="form.errors.cliente_id" class="mt-2" />
              </div>

              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha</label>
                <input type="date" v-model="form.fecha_venta"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
                <p v-if="form.errors.fecha_venta" class="text-red-500 text-sm mt-1">{{ form.errors.fecha_venta }}</p>
              </div>

              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="metodo_pago">Método de Pago</label>
                <select id="metodo_pago" v-model="form.metodo_pago"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                  <option disabled value="">Seleccione un medio de pago</option>
                  <option value="efectivo">Efectivo</option>
                  <option value="tarjeta_credito">Tarjeta Credito</option>
                  <option value="tarjeta_debito">Tarjeta Debito</option>
                </select>
                <InputError :message="form.errors.metodo_pago" class="mt-2" />
              </div>

              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="tipo_comprobante">Tipo Comprobante</label>
                <select id="tipo_comprobante" v-model="form.tipo_comprobante"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                  <option value="factura">Factura</option>
                </select>
                <InputError :message="form.errors.tipo_comprobante" class="mt-2" />
              </div>

              <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="estado">Estado</label>
                <select id="estado" v-model="form.estado"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                  <option value="pendiente">Pendiente</option>
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
              <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Detalles de Venta</h2>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 items-center">
                <select v-model="productoSeleccionado"
                  class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                  <option disabled :value="null">Seleccione un producto</option>
                  <option v-for="producto in productos" :key="producto.id" :value="producto">
                    {{ producto.nombre }} - {{ formatCurrency(producto.precio_venta) }}
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
                           @input="recalcularTotales(index)" /></td>
                      <td class="p-3 text-right">
                        <span class="inline-block w-28 text-right p-2">
                        {{ formatCurrency(detalle.precio) }}
                          </span></td>
                      <td class="p-3 text-right">{{ formatCurrency(detalle.total) }}</td>
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
               <p class="text-lg"><strong>Total IVA:</strong> {{ formatCurrency(form.total_iva) }}</p>
               <p class="text-xl font-bold"><strong>Total Venta:</strong> {{ formatCurrency(form.total_venta) }}</p>
              </div>
            </div>

            <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
              <button type="submit"
                class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all"
                :disabled="form.processing">
                <span v-if="form.processing">Procesando...</span>
                <span v-else>Actualizar Venta</span>
              </button>
              <button type="button" @click="router.get(route('ventas.index'))"
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
