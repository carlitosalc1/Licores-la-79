<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';

// Tipos para la factura y las ventas
interface Venta {
    id: number;
    total?: number;
    user?: {
        name: string;
        id: number;
    };
    // Añade otras propiedades relevantes de Venta si las necesitas
}

interface Factura {
    id: number;
    venta_id: number;
    numero_factura: string;
    fecha_emision: string;
    subtotal: number;
    impuesto: number;
    total: number;
    metodo_pago: 'efectivo' | 'tarjeta';
    estado: 'pendiente' | 'pagada' | 'anulada';
    created_at: string;
    updated_at: string;
    // Si necesitas acceder a la venta asociada para el display inicial del select
    venta?: Venta;
}

// Definición de Props para el componente Edit
const props = defineProps<{
    factura: Factura; // La factura a editar
    ventas: Venta[];   // Lista de ventas disponibles para asociar (incluyendo la actual de la factura)
}>();

// Estado del formulario con useForm de Inertia, inicializado con los datos de la factura
const form = useForm({
    venta_id: props.factura.venta_id,
    subtotal: props.factura.subtotal,
    impuesto: props.factura.impuesto,
    total: props.factura.total,
    metodo_pago: props.factura.metodo_pago,
    estado: props.factura.estado,
});

// Función para calcular el total (la misma lógica que en Create.vue)
const calculateTotal = () => {
    form.total = Math.round(Number(form.subtotal) + Number(form.impuesto));
};

// Propiedad computada para formatear el total con punto de mil para la visualización
const formattedTotal = computed(() => {
    return new Intl.NumberFormat('es-CO', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(form.total);
});

// Función para enviar el formulario de actualización
const submit = () => {
    // Usamos form.put para enviar la petición de actualización
    form.put(route('facturas.update', props.factura.id), {
        onSuccess: () => {
            // Opcional: Redirigir a la vista de la factura o al índice
            // router.visit(route('facturas.show', props.factura.id));
            // Opcional: mostrar un mensaje flash
        },
        onError: (errors) => {
            console.error("Error al actualizar factura:", errors);
        },
    });
};
</script>

<template>
    <Head :title="`Editar Factura ${factura.numero_factura}`" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Factura #{{ factura.numero_factura }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="numero_factura" class="block text-sm font-medium text-gray-700">Número de Factura</label>
                                <input
                                    type="text"
                                    id="numero_factura"
                                    :value="factura.numero_factura"
                                    readonly
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed sm:text-sm"
                                />
                            </div>

                            <div>
                                <label for="fecha_emision" class="block text-sm font-medium text-gray-700">Fecha de Emisión</label>
                                <input
                                    type="date"
                                    id="fecha_emision"
                                    :value="factura.fecha_emision"
                                    readonly
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed sm:text-sm"
                                />
                            </div>

                            <div>
                                <label for="venta_id" class="block text-sm font-medium text-gray-700">Venta Asociada</label>
                                <select
                                    id="venta_id"
                                    v-model="form.venta_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option :value="null">Seleccione una venta</option>
                                    <option v-for="venta in ventas" :key="venta.id" :value="venta.id">
                                        Venta #{{ venta.id }} (Cliente: {{ venta.user?.name || 'N/A' }})
                                    </option>
                                </select>
                                <div v-if="form.errors.venta_id" class="text-red-600 text-sm mt-1">{{ form.errors.venta_id }}</div>
                            </div>

                            <div>
                                <label for="subtotal" class="block text-sm font-medium text-gray-700">Subtotal</label>
                                <input
                                    type="number"
                                    id="subtotal"
                                    v-model.number="form.subtotal"
                                    @input="calculateTotal"
                                    step="1"
                                    min="0"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                                <div v-if="form.errors.subtotal" class="text-red-600 text-sm mt-1">{{ form.errors.subtotal }}</div>
                            </div>

                            <div>
                                <label for="impuesto" class="block text-sm font-medium text-gray-700">Impuesto</label>
                                <input
                                    type="number"
                                    id="impuesto"
                                    v-model.number="form.impuesto"
                                    @input="calculateTotal"
                                    step="1"
                                    min="0"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                                <div v-if="form.errors.impuesto" class="text-red-600 text-sm mt-1">{{ form.errors.impuesto }}</div>
                            </div>

                            <div>
                                <label for="total" class="block text-sm font-medium text-gray-700">Total</label>
                                <input
                                    type="text"
                                    id="total"
                                    :value="formattedTotal"
                                    readonly
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed sm:text-sm"
                                />
                                <div v-if="form.errors.total" class="text-red-600 text-sm mt-1">{{ form.errors.total }}</div>
                            </div>

                            <div>
                                <label for="metodo_pago" class="block text-sm font-medium text-gray-700">Método de Pago</label>
                                <select
                                    id="metodo_pago"
                                    v-model="form.metodo_pago"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                </select>
                                <div v-if="form.errors.metodo_pago" class="text-red-600 text-sm mt-1">{{ form.errors.metodo_pago }}</div>
                            </div>

                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
                                <select
                                    id="estado"
                                    v-model="form.estado"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="pendiente">Pendiente</option>
                                    <option value="pagada">Pagada</option>
                                    <option value="anulada">Anulada</option>
                                </select>
                                <div v-if="form.errors.estado" class="text-red-600 text-sm mt-1">{{ form.errors.estado }}</div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 space-x-4">
                            <Link :href="route('facturas.index')" class="text-gray-600 hover:text-gray-900 transition duration-150 ease-in-out">
                                Cancelar
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                Actualizar Factura
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
