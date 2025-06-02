<script setup lang="ts">
import { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, router, Head } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue'; // Importar ref para variables reactivas de display
import InputError from '@/components/InputError.vue'; // Importar InputError

interface Venta {
    id: number;
    // Podrías añadir más campos si necesitas mostrarlos en el select
}

interface Producto {
    id: number;
    nombre: string;
    precio_venta: number; // Campo para precargar el precio unitario
}

interface DetalleVenta {
    id: number;
    venta_id: number;
    producto_id: number;
    cantidad: number;
    precio_unitario: number;
    subtotal: number;
    impuesto_iva: number;
    total_pagar: number;
}

// Props que el controlador de Laravel envía a este componente Vue
const props = defineProps<{
    ventas: Venta[];
    productos: Producto[];
    detalleVenta: DetalleVenta; // Se añade la prop para el detalle de venta existente
}>();

// Breadcrumbs para la navegación
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Detalles de Venta', href: '/detalle_ventas' },
    { title: `Editar Detalle: ${props.detalleVenta.id}`, href: '#' },
];

// Inicialización del formulario con los valores del detalleVenta existente
const form = useForm({
    venta_id: props.detalleVenta.venta_id,
    producto_id: String(props.detalleVenta.producto_id), // Convertir a string para v-model en select
    cantidad: props.detalleVenta.cantidad,
    precio_unitario: props.detalleVenta.precio_unitario,
    subtotal: props.detalleVenta.subtotal,
    impuesto_iva: props.detalleVenta.impuesto_iva,
    total_pagar: props.detalleVenta.total_pagar,
});

// Variables reactivas para los valores de display formateados
const displayPrecioUnitario = ref('0');
const displaySubtotal = ref('0');
const displayImpuestoIva = ref('0');
const displayTotalPagar = ref('0');

const selectedProduct = computed(() => {
    return props.productos.find(p => p.id === Number(form.producto_id));
});

// Función para recalcular subtotal, impuesto_iva y total_pagar
const recalculateTotals = () => {
    // Asegurarse de que precio_unitario y cantidad sean números válidos
    const currentPrecioUnitario = typeof form.precio_unitario === 'string' ? parseFloat(form.precio_unitario) : form.precio_unitario;
    const currentCantidad = typeof form.cantidad === 'string' ? parseFloat(form.cantidad) : form.cantidad;

    if (!isNaN(currentPrecioUnitario) && !isNaN(currentCantidad) && currentPrecioUnitario >= 0 && currentCantidad >= 0) {
        form.subtotal = currentCantidad * currentPrecioUnitario;
        form.impuesto_iva = form.subtotal * 0.19; // IVA del 19%
        form.total_pagar = form.subtotal + form.impuesto_iva;
    } else {
        form.subtotal = 0;
        form.impuesto_iva = 0;
        form.total_pagar = 0;
    }
};

// Watcher para precargar precio_unitario cuando cambia el producto
watch(() => form.producto_id, () => {
    if (selectedProduct.value) {
        form.precio_unitario = selectedProduct.value.precio_venta;
    } else {
        form.precio_unitario = 0;
    }
    recalculateTotals(); // Recalcular totales después de actualizar el precio unitario
});

// Watcher para recalcular subtotal, impuesto_iva y total_pagar cuando cambian cantidad o precio_unitario
watch([() => form.cantidad, () => form.precio_unitario], recalculateTotals, { immediate: true }); // Ejecutar inmediatamente al montar el componente

// Watchers para mantener las variables de display sincronizadas y formateadas
watch(() => form.precio_unitario, (newValue) => {
    displayPrecioUnitario.value = newValue === 0 ? '0' : newValue.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}, { immediate: true });

watch(() => form.subtotal, (newValue) => {
    displaySubtotal.value = newValue === 0 ? '0' : newValue.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}, { immediate: true });

watch(() => form.impuesto_iva, (newValue) => {
    displayImpuestoIva.value = newValue === 0 ? '0' : newValue.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}, { immediate: true });

watch(() => form.total_pagar, (newValue) => {
    displayTotalPagar.value = newValue === 0 ? '0' : newValue.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}, { immediate: true });

// Función para limpiar y convertir el input de texto a un número
const formatNumberInput = (field: 'precio_unitario') => {
    let cleanedValue;
    if (field === 'precio_unitario') {
        cleanedValue = displayPrecioUnitario.value.replace(/\./g, ''); // Elimina el punto de miles
    }

    const numericValue = parseFloat(cleanedValue || '0');

    if (field === 'precio_unitario') {
        form.precio_unitario = isNaN(numericValue) ? 0 : numericValue;
    }
};

// Enviar formulario (actualización)
const submit = () => {
    const dataToSubmit = {
        ...form.data(),
        precio_unitario: typeof form.precio_unitario === 'string' ? parseFloat(form.precio_unitario) : form.precio_unitario,
        cantidad: typeof form.cantidad === 'string' ? parseFloat(form.cantidad) : form.cantidad,
        subtotal: form.subtotal, // Estos ya son números por el cálculo
        impuesto_iva: form.impuesto_iva,
        total_pagar: form.total_pagar,
    };

    form.put(route('detalle_ventas.update', props.detalleVenta.id), {
        onSuccess: () => {
            router.visit(route('detalle_ventas.index'));
        },
        onError: (errors) => {
            console.error("Error al actualizar el detalle de venta:", errors);
        },
    });
};
</script>

<template>
    <Head :title="`Editar Detalle de Venta: ${detalleVenta.id}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
            <div class="w-full max-w-3xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Editar Detalle de Venta: {{ detalleVenta.id }}</h1>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="venta_id">ID de Venta</label>
                                <select id="venta_id" v-model="form.venta_id"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                    <option disabled value="">Seleccione una Venta</option>
                                    <option v-for="venta in ventas" :key="venta.id" :value="venta.id">
                                        {{ venta.id }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.venta_id" class="mt-2" />
                            </div>

                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="producto_id">Producto</label>
                                <select id="producto_id" v-model="form.producto_id"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                    <option disabled value="">Seleccione un Producto</option>
                                    <option v-for="producto in productos" :key="producto.id" :value="producto.id">
                                        {{ producto.nombre }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.producto_id" class="mt-2" />
                            </div>

                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="cantidad">Cantidad</label>
                                <input id="cantidad" type="number" v-model.number="form.cantidad" min="1"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"/>
                                <InputError :message="form.errors.cantidad" class="mt-1" />
                            </div>

                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Precio Unitario</label>
                                <input v-model="displayPrecioUnitario" @blur="formatNumberInput('precio_unitario')" @input="formatNumberInput('precio_unitario')" type="text" autocomplete="off"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"/>
                                <InputError :message="form.errors.precio_unitario" class="mt-1" />
                            </div>

                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Sub Total</label>
                                <input :value="displaySubtotal" type="text" disabled
                                    class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3"/>
                                <InputError :message="form.errors.subtotal" class="mt-1" />
                            </div>

                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Impuesto Iva (19%)</label>
                                <input :value="displayImpuestoIva" type="text" disabled
                                    class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3"/>
                                <InputError :message="form.errors.impuesto_iva" class="mt-1" />
                            </div>

                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Total a Pagar</label>
                                <input :value="displayTotalPagar" type="text" disabled
                                    class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3"/>
                                <InputError :message="form.errors.total_pagar" class="mt-1" />
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
                            <button type="submit" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all" :disabled="form.processing">
                                <span v-if="form.processing">Actualizando...</span>
                                <span v-else>Actualizar Detalle de Venta</span>
                            </button>
                            <button type="button" @click="router.get(route('detalle_ventas.index'))"
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
