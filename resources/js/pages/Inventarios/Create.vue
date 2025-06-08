<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

// Definición de tipos para las props
interface ProductoItem {
    id: number;
    nombre: string;
}

interface CreateInventarioProps {
    productos: ProductoItem[];
    flash: SharedData['flash']; // Para manejar los mensajes flash
}

const props = defineProps<CreateInventarioProps>();

// Breadcrumbs para la navegación
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventario', href: '/inventarios' },
    { title: 'Registrar Nuevo Movimiento', href: '#' },
];

// Formulario de Inertia
const form = useForm({
    producto_id: null as number | null,
    tipo_movimiento: 'entrada' as 'entrada' | 'salida' | 'ajuste',
    cantidad_entrada: 0 as number,
    cantidad_salida: 0 as number,
    fecha_actualizacion: new Date().toISOString().split('T')[0],
    compra_id: null as number | null,
    venta_id: null as number | null,
});

// Mensajes flash (éxito/error)
const page = usePage<SharedData>();
const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

// Propiedad computada para obtener el stock actual del producto seleccionado
const stockActual = ref<number | null>(null);

// Watcher para el cambio de producto_id
watch(() => form.producto_id, async (newProductId) => {
    if (newProductId) {
        try {
            const response = await fetch(route('inventarios.stock', newProductId));
            const data = await response.json();
            stockActual.value = data.stock_actual;
        } catch (error) {
            console.error('Error al obtener stock:', error);
            stockActual.value = null;
        }
    } else {
        stockActual.value = null;
    }
}, { immediate: true });

// Función para enviar el formulario
const submit = () => {
    form.post(route('inventarios.store'), {
        onSuccess: () => {
            form.reset();
        },
        onError: (errors) => {
            console.error('Errores de validación:', errors);
        },
    });
};
</script>

<template>
    <Head title="Registrar Movimiento de Inventario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
            <div class="w-full max-w-3xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Registrar Nuevo Movimiento de Inventario</h1>

                    <div v-if="successMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p>{{ successMessage }}</p>
                    </div>
                    <div v-if="errorMessage" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p>{{ errorMessage }}</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="producto_id" class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Producto</label>
                                <select v-model="form.producto_id" @change="form.clearErrors('producto_id')"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                    <option :value="null" disabled>Selecciona un producto</option>
                                    <option v-for="producto in props.productos" :key="producto.id" :value="producto.id">
                                        {{ producto.nombre }}
                                    </option>
                                </select>
                                <div v-if="form.errors.producto_id" class="text-red-500 text-sm mt-2">{{ form.errors.producto_id }}</div>
                                <p v-if="form.producto_id && stockActual !== null" class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                    Stock actual: {{ stockActual }}
                                </p>
                            </div>

                            <div>
                                <label for="tipo_movimiento" class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Movimiento</label>
                                <select v-model="form.tipo_movimiento" @change="form.clearErrors('tipo_movimiento')"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                    <option value="entrada">Entrada</option>
                                    <option value="salida">Salida</option>
                                    <option value="ajuste">Ajuste</option>
                                </select>
                                <div v-if="form.errors.tipo_movimiento" class="text-red-500 text-sm mt-2">{{ form.errors.tipo_movimiento }}</div>
                            </div>

                            <div v-if="form.tipo_movimiento === 'entrada' || form.tipo_movimiento === 'ajuste'">
                                <label for="cantidad_entrada" class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Cantidad de Entrada</label>
                                <input
                                    id="cantidad_entrada"
                                    type="number"
                                    v-model.number="form.cantidad_entrada"
                                    @input="form.clearErrors('cantidad_entrada')"
                                    min="0"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                />
                                <div v-if="form.errors.cantidad_entrada" class="text-red-500 text-sm mt-2">{{ form.errors.cantidad_entrada }}</div>
                            </div>

                            <div v-if="form.tipo_movimiento === 'salida' || form.tipo_movimiento === 'ajuste'">
                                <label for="cantidad_salida" class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Cantidad de Salida</label>
                                <input
                                    id="cantidad_salida"
                                    type="number"
                                    v-model.number="form.cantidad_salida"
                                    @input="form.clearErrors('cantidad_salida')"
                                    min="0"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
                                <div v-if="form.errors.cantidad_salida" class="text-red-500 text-sm mt-2">{{ form.errors.cantidad_salida }}</div>
                            </div>

                            <input id="fecha_actualizacion" type="datetime-local" v-model="form.fecha_actualizacion" @input="form.clearErrors('fecha_actualizacion')"
                             class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
                        </div>

                        <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
                            <button type="submit" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all" :disabled="form.processing">
                                <span v-if="form.processing">Guardando...</span>
                                <span v-else>Registrar Movimiento</span>
                            </button>
                            <button type="button" @click="router.get('/inventarios')"
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
