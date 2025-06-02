<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';
import InputError from '@/components/InputError.vue';

interface Producto {
    id: number;
    nombre: string;
    precio_compra: number; // ¡Nuevo campo para precargar el precio de compra!
}

interface Compra {
    id: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Detalle Compras', href: '/detalle_compras' },
    { title: 'Crear Detalle', href: '#' },
];

const props = defineProps<{
    productos: Producto[];
    compras: Compra[];
}>();

const form = useForm({
    producto_id: '',
    compra_id: '',
    cantidad: 1,
    precio_unitario: 0, // Este valor será actualizado al seleccionar un producto
    subtotal: 0,
});

// Variables reactivas para los valores de visualización formateados
const displayPrecioUnitario = ref('0');
const displaySubtotal = ref('0');

// Propiedad computada para obtener el producto seleccionado
const selectedProduct = computed(() => {
    // Busca el producto en los props 'productos' que coincida con el 'producto_id' del formulario
    return props.productos.find(p => p.id === Number(form.producto_id));
});

// Observador para precargar precio_unitario cuando cambia el producto
watch(() => form.producto_id, (newProductId) => {
    if (selectedProduct.value) {
        form.precio_unitario = selectedProduct.value.precio_compra;
    } else {
        form.precio_unitario = 0; // Si no hay producto seleccionado, el precio es 0
    }
}, { immediate: true }); // Ejecutar inmediatamente al montar el componente para inicializar

// Observador para recalcular el subtotal cuando cambian la cantidad o el precio unitario
watch([() => form.cantidad, () => form.precio_unitario], () => {
    const currentPrecioUnitario = typeof form.precio_unitario === 'string' ? parseFloat(form.precio_unitario) : form.precio_unitario;
    const currentCantidad = typeof form.cantidad === 'string' ? parseFloat(form.cantidad) : form.cantidad;

    if (!isNaN(currentPrecioUnitario) && !isNaN(currentCantidad) && currentPrecioUnitario >= 0 && currentCantidad >= 0) {
        form.subtotal = currentCantidad * currentPrecioUnitario;
    } else {
        form.subtotal = 0;
    }
}, { immediate: true });

// Observadores para mantener las variables de visualización sincronizadas y formateadas
watch(() => form.precio_unitario, (newValue) => {
    displayPrecioUnitario.value = newValue === 0 ? '0' : newValue.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}, { immediate: true });

watch(() => form.subtotal, (newValue) => {
    displaySubtotal.value = newValue === 0 ? '0' : newValue.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}, { immediate: true });

// Función para limpiar y convertir la entrada de texto a un número
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

const submit = () => {
    const dataToSubmit = {
        ...form.data(),
        precio_unitario: typeof form.precio_unitario === 'string' ? parseFloat(form.precio_unitario) : form.precio_unitario,
        cantidad: typeof form.cantidad === 'string' ? parseFloat(form.cantidad) : form.cantidad,
        subtotal: form.subtotal,
    };

    form.post(route('detalle_compras.store'), {
        onSuccess: () => {
            router.visit(route('detalle_compras.index'));
        },
        onError: (errors) => {
            console.error("Error al crear el detalle de compra:", errors);
        },
    });
};
</script>

<template>
    <Head title="Crear Detalle Compra" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
            <div class="w-full max-w-3xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Crear Detalle de Compra</h1>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="producto_id">Producto</label>
                                <select id="producto_id" v-model="form.producto_id"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                    <option disabled value="">Seleccione un producto</option>
                                    <option v-for="producto in props.productos" :key="producto.id" :value="producto.id">
                                        {{ producto.nombre }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.producto_id" class="mt-2" />
                            </div>

                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="compra_id">Compra</label>
                                <select id="compra_id" v-model="form.compra_id"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                    <option disabled value="">Seleccione una compra</option>
                                    <option v-for="compra in props.compras" :key="compra.id" :value="compra.id">
                                        Compra #{{ compra.id }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.compra_id" class="mt-2" />
                            </div>

                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="cantidad">Cantidad</label>
                                <input id="cantidad" type="number" v-model.number="form.cantidad" min="1"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
                                <InputError :message="form.errors.cantidad" class="mt-1" />
                            </div>

                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="precio_unitario">Precio Unitario</label>
                                <input id="precio_unitario" v-model="displayPrecioUnitario" @blur="formatNumberInput('precio_unitario')" @input="formatNumberInput('precio_unitario')" type="text" autocomplete="off"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
                                <InputError :message="form.errors.precio_unitario" class="mt-1" />
                            </div>

                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Subtotal</label>
                                <input :value="displaySubtotal" type="text" disabled
                                    class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3" />
                                <InputError :message="form.errors.subtotal" class="mt-1" />
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
                            <button type="submit"
                                class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all"
                                :disabled="form.processing">
                                <span v-if="form.processing">Procesando...</span>
                                <span v-else>Guardar</span>
                            </button>
                            <button type="button" @click="router.get(route('detalle_compras.index'))"
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
