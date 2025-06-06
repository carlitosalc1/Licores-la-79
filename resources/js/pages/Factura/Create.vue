<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';

interface Venta {
    id: number;
    total?: number;
    user?: {
        name: string;
        id: number;
    };

}

// Definición de Props para el componente
defineProps<{
    ventasSinFactura: Venta[];
}>();

const form = useForm({
    venta_id: null as number | null,
    subtotal: 0,
    impuesto: 0,
    total: 0,
    metodo_pago: '',
    estado: 'pendiente',
});

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

// Función para enviar el formulario
const submit = () => {
    form.post(route('facturas.store'), {
        onSuccess: () => {
            form.reset();

        },
        onError: (errors) => {
            console.error("Error al crear factura:", errors);

        },
    });
};
</script>

<template>
    <Head title="Crear Factura" />

    <AppLayout>
        <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
            <div class="w-full max-w-4xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Crear Nueva Factura</h1>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="venta_id" class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Venta</label>
                                <select id="venta_id" v-model="form.venta_id" class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" >
                                    <option :value="null" disabled>Seleccione una venta</option>
                                    <option v-for="venta in ventasSinFactura" :key="venta.id" :value="venta.id">
                                        Venta #{{ venta.id }} (Cliente: {{ venta.user?.name || 'N/A' }})
                                    </option>
                                </select>
                                <InputError :message="form.errors.venta_id" class="mt-2" />
                            </div>

                            <div>
                                <label for="subtotal" class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Subtotal</label>
                                <input type="number" id="subtotal" v-model.number="form.subtotal" @input="calculateTotal" step="1" min="0"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
                                <InputError :message="form.errors.subtotal" class="mt-2" />
                            </div>

                            <div>
                                <label for="impuesto" class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Impuesto</label>
                                <input
                                    type="number"
                                    id="impuesto"
                                    v-model.number="form.impuesto"
                                    @input="calculateTotal"
                                    step="1"
                                    min="0"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
                                <InputError :message="form.errors.impuesto" class="mt-2" />
                            </div>

                            <div>
                                <label for="total" class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Total</label>
                                <input type="text" id="total" :value="formattedTotal" readonly class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 cursor-not-allowed" />
                                <InputError :message="form.errors.total" class="mt-2" />
                            </div>

                            <div>
                                <label for="metodo_pago" class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Método de Pago</label>
                                <select id="metodo_pago" v-model="form.metodo_pago" class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                </select>
                                <InputError :message="form.errors.metodo_pago" class="mt-2" />
                            </div>

                            <div>
                                <label for="estado" class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Estado</label>
                                <select id="estado" v-model="form.estado" class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                    <option value="pendiente">Pendiente</option>
                                    <option value="pagada">Pagada</option>
                                    <option value="anulada">Anulada</option>
                                </select>
                                <InputError :message="form.errors.estado" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
                            <Button type="submit"
                                class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all"
                                :disabled="form.processing">
                                <span v-if="form.processing">Guardando...</span>
                                <span v-else>Guardar Factura</span>
                            </Button>
                            <Link :href="route('facturas.index')"
                                class="flex-1 md:flex-none px-6 py-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition-all text-center">
                                Cancelar
                            </Link>
                        </div>

                        <!-- Mostrar errores generales del formulario si existen -->
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
