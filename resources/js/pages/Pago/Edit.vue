<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { onMounted, computed, watch } from 'vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue'; // Asegúrate de tener este componente

import type { BreadcrumbItem } from '@/types';

// Define las props que espera recibir de tu controlador
const props = defineProps<{
    pago: {
        id: number;
        venta_id: number | null;
        compra_id: number | null;
        monto: number;
        cambio: number;
        metodo_pago: 'efectivo' | 'tarjeta_credito' | 'tarjeta_debito';
        fecha_pago: string;
        referencia_pago: string | null;
    };
    ventas: Array<{
        id: number;
        total: number;
        cliente_id: number;
        cliente: {
            id: number;
            nombre: string;
        };
    }>;
    compras: Array<{
        id: number;
        total: number;
        proveedor_id: number;
        proveedor: {
            id: number;
            razon_social: string;
        };
    }>;
}>();

// Define el formulario con Inertia
const form = useForm({
    venta_id: null as number | null,
    compra_id: null as number | null,
    monto: 0 as number, // Este será el total de la venta/compra seleccionada
    monto_recibido: 0 as number, // Nuevo campo para el dinero que el usuario "recibe"
    metodo_pago: 'efectivo' as 'efectivo' | 'tarjeta_credito' | 'tarjeta_debito',
    fecha_pago: '',
    referencia_pago: '' as string | null,
    // 'cambio' no se define aquí directamente porque será una propiedad computada
});

// Función auxiliar para formatear moneda con separador de miles y sin decimales
const formatCurrency = (value: number) => {
    if (isNaN(value)) return '0';
    return Number(value).toLocaleString('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};

// Propiedad computada para el monto total a pagar (de la venta/compra seleccionada)
const montoTotalAPagar = computed<number>(() => {
    const selectedItem = form.venta_id
        ? props.ventas.find(v => v.id === form.venta_id)
        : form.compra_id
            ? props.compras.find(c => c.id === form.compra_id)
            : null;
    return selectedItem ? parseFloat(selectedItem.total.toFixed(2)) : 0;
});

// Propiedad computada para el cálculo del cambio
const cambioCalculado = computed<number>(() => {
    const montoRecibido = form.monto_recibido;
    const totalAPagar = montoTotalAPagar.value; // Usa el montoTotalAPagar computado

    const cambio = parseFloat((montoRecibido - totalAPagar).toFixed(2));
    return isNaN(cambio) ? 0 : cambio;
});

// Propiedad computada para mostrar y analizar 'monto_recibido'
const displayMontoRecibido = computed({
    get() {
        // Cuando se obtiene el valor, siempre se formatea si es un número válido
        return formatCurrency(form.monto_recibido);
    },
    set(value: string) {
        // Limpia la entrada del usuario antes de convertirla a número
        // Elimina todos los caracteres que no sean dígitos.
        const cleanedValue = value.replace(/[^0-9]/g, '');
        const parsedValue = parseInt(cleanedValue, 10); // Usa parseInt para enteros
        form.monto_recibido = isNaN(parsedValue) ? 0 : parsedValue;
    }
});


// Observa los cambios en venta_id y compra_id para actualizar el monto a pagar
watch([() => form.venta_id, () => form.compra_id], () => {
    form.monto = montoTotalAPagar.value; // Actualiza el monto en el formulario
    // Resetea monto_recibido cuando la asociación cambia,
    // ya que el nuevo total de venta/compra podría requerir un nuevo monto recibido.
    form.monto_recibido = 0;
}, { deep: true }); // Usamos deep: true si el cambio de props.pago también activara el watch, aunque onMounted lo inicializa.

// Propiedad computada para v-model del campo de referencia
const referenciaPagoModel = computed({
    get() {
        return form.referencia_pago ?? '';
    },
    set(value: string) {
        form.referencia_pago = value === '' ? null : value;
    }
});

// Propiedad computada para mostrar el valor seleccionado en el SelectTrigger de Venta
const displaySelectedVenta = computed(() => {
    if (form.venta_id === null) {
        return 'Selecciona una venta (opcional)';
    }
    const selectedVenta = props.ventas.find(v => v.id === form.venta_id);
    if (selectedVenta) {
        return `ID: ${selectedVenta.id} - Total: ${formatCurrency(selectedVenta.total)} (Cliente: ${selectedVenta.cliente.nombre})`;
    }
    return 'Venta no encontrada'; // En caso de que el ID no corresponda a ninguna venta
});

// Propiedad computada para mostrar el valor seleccionado en el SelectTrigger de Compra
const displaySelectedCompra = computed(() => {
    if (form.compra_id === null) {
        return 'Selecciona una compra (opcional)';
    }
    const selectedCompra = props.compras.find(c => c.id === form.compra_id);
    if (selectedCompra) {
        return `ID: ${selectedCompra.id} - Total: ${formatCurrency(selectedCompra.total)} (Proveedor: ${selectedCompra.proveedor.razon_social})`;
    }
    return 'Compra no encontrada'; // En caso de que el ID no corresponda a ninguna compra
});

onMounted(() => {
    // Populate the form with existing pago data
    form.venta_id = props.pago.venta_id;
    form.compra_id = props.pago.compra_id;
    form.monto = props.pago.monto; // El monto original de la venta/compra
    form.metodo_pago = props.pago.metodo_pago;
    form.fecha_pago = props.pago.fecha_pago.slice(0, 16); // Ensure correct format for datetime-local
    form.referencia_pago = props.pago.referencia_pago;

    // AL EDITAR: Calcula el monto_recibido original
    // Se asume que 'monto' en la DB es el total de la venta/compra
    // y 'cambio' es la diferencia entre el monto_recibido y el monto_total.
    // Por lo tanto: monto_recibido = monto + cambio
    form.monto_recibido = parseFloat((props.pago.monto + props.pago.cambio).toFixed(2));
});

// Función para manejar el envío del formulario
const submit = () => {
    // Antes de enviar, transformamos los datos para incluir el 'cambio' calculado
    form.transform((data) => ({
        ...data,
        monto: montoTotalAPagar.value, // Aseguramos que 'monto' sea el total a pagar
        cambio: cambioCalculado.value, // Añadimos el 'cambio' calculado
    })).put(route('pagos.update', props.pago.id), { // Use form.put for updates
        onSuccess: () => {
            router.visit(route('pagos.index')); // Redirigir a la página de índice de pagos
        },
        onError: (errors) => {
            console.error('Errores de validación:', errors);
        },
        preserveScroll: true,
    });
};

// Lógica para deshabilitar el otro selector si uno ya está seleccionado
const handleVentaChange = () => {
    if (form.venta_id !== null) {
        form.compra_id = null;
    }
};

const handleCompraChange = () => {
    if (form.compra_id !== null) {
        form.venta_id = null;
    }
};

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pagos', href: '/pagos' },
    { title: 'Editar pago', href: '#' },
];
</script>

<template>
    <Head title="Editar Pago" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
            <div class="w-full max-w-3xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Editar Pago</h1>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha y Hora</label>
                                <input type="datetime-local" v-model="form.fecha_pago"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
                                <InputError :message="form.errors.fecha_pago" class="mt-2" />
                            </div>

                            <div></div>
                            <div class="mb-4">
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="venta_id">Venta Asociada</label>
                                <Select v-model="form.venta_id" @update:modelValue="handleVentaChange" :disabled="form.compra_id !== null">
                                    <SelectTrigger class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                        <SelectValue :placeholder="displaySelectedVenta" />
                                    </SelectTrigger>
                                    <SelectContent class="bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                                        <SelectItem :value="null">
                                            <span class="text-gray-800 dark:text-white">(No asociada a Venta)</span>
                                        </SelectItem>
                                        <SelectItem v-for="venta in props.ventas" :key="venta.id" :value="venta.id">
                                            <span class="text-gray-800 dark:text-white">ID: {{ venta.id }} - Total: {{ formatCurrency(venta.total) }} (Cliente: {{ venta.cliente.nombre }})</span>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.venta_id" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="compra_id">Compra Asociada</label>
                                <Select v-model="form.compra_id" @update:modelValue="handleCompraChange" :disabled="form.venta_id !== null">
                                    <SelectTrigger class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                        <SelectValue :placeholder="displaySelectedCompra" />
                                    </SelectTrigger>
                                    <SelectContent class="bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                                        <SelectItem :value="null">
                                            <span class="text-gray-800 dark:text-white">(No asociada a Compra)</span>
                                        </SelectItem>
                                        <SelectItem v-for="compra in props.compras" :key="compra.id" :value="compra.id">
                                            <span class="text-gray-800 dark:text-white">ID: {{ compra.id }} - Total: {{ formatCurrency(compra.total) }} (Proveedor: {{ compra.proveedor.razon_social }})</span>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.compra_id" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="monto_a_pagar">Monto Total a Pagar</label>
                                <input
                                    id="monto_a_pagar"
                                    type="text"
                                    :value="formatCurrency(montoTotalAPagar)"
                                    readonly
                                    class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 cursor-not-allowed"
                                />
                                <InputError :message="form.errors.monto" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="monto_recibido">Monto Recibido</label>
                                <input
                                    id="monto_recibido"
                                    type="text"
                                    v-model="displayMontoRecibido"
                                    :required="montoTotalAPagar > 0"
                                    @focus="displayMontoRecibido = String(form.monto_recibido)"
                                    @blur="displayMontoRecibido = formatCurrency(form.monto_recibido)"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                />
                                <InputError :message="form.errors.monto_recibido" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="cambio">Cambio</label>
                                <input
                                    id="cambio"
                                    type="text"
                                    :value="formatCurrency(cambioCalculado)"
                                    readonly
                                    class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 cursor-not-allowed"
                                />
                            </div>

                            <div class="mb-4">
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="metodo_pago">Método de Pago</label>
                                <Select v-model="form.metodo_pago" required>
                                    <SelectTrigger class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                        <SelectValue placeholder="Selecciona un método de pago" />
                                    </SelectTrigger>
                                    <SelectContent class="bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                                        <SelectItem value="efectivo">
                                            <span class="text-gray-800 dark:text-white">Efectivo</span>
                                        </SelectItem>
                                        <SelectItem value="tarjeta_credito">
                                            <span class="text-gray-800 dark:text-white">Tarjeta Crédito</span>
                                        </SelectItem>
                                        <SelectItem value="tarjeta_debito">
                                            <span class="text-gray-800 dark:text-white">Tarjeta Débito</span>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.metodo_pago" class="mt-2" />
                            </div>

                            <div class="mb-6">
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="referencia_pago">Referencia de Pago (opcional)</label>
                                <input id="referencia_pago" type="text" v-model="referenciaPagoModel"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
                                <InputError :message="form.errors.referencia_pago" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
                            <button type="submit"
                                class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all"
                                :disabled="form.processing">
                                <span v-if="form.processing">Actualizando...</span>
                                <span v-else>Actualizar</span>
                            </button>
                            <button type="button" @click="router.get('/pagos')"
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
