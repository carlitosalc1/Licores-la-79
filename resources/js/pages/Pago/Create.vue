<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { onMounted, computed, watch } from 'vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue'; // Import the InputError component


import type { BreadcrumbItem } from '@/types';

// Define las props que espera recibir de tu controlador
const props = defineProps<{
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
    // 'monto' será el total de la venta/compra seleccionada (calculado)
    monto: 0 as number,
    // Nuevo campo para el monto que el usuario ingresa como "recibido"
    monto_recibido: 0 as number,
    metodo_pago: 'efectivo' as 'efectivo' | 'tarjeta_credito' | 'tarjeta_debito',
    fecha_pago: '',
    referencia_pago: '' as string | null,
    // 'cambio' no se define aquí directamente porque será una propiedad computada
});

// Función auxiliar para formatear moneda con separador de miles y sin decimales
const formatCurrency = (value: number) => {
    if (isNaN(value)) return '$ 0';
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

    // Calcular el cambio. Asegurarse de que no sea NaN.
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


// Observa los cambios en venta_id y compra_id para actualizar el monto a pagar
watch([() => form.venta_id, () => form.compra_id], () => {
    form.monto = montoTotalAPagar.value; // Actualiza el monto en el formulario
    form.monto_recibido = 0; // Resetea el monto recibido cuando cambia la asociación
});


// Propiedad computada para v-model del campo de referencia
const referenciaPagoModel = computed({
    get() {
        return form.referencia_pago ?? '';
    },
    set(value: string) {
        form.referencia_pago = value === '' ? null : value;
    }
});

// Función para obtener la fecha y hora actual en formato compatible con datetime-local
const getCurrentDateTime = () => {
    const now = new Date();
    const timezoneOffset = now.getTimezoneOffset() * 60000;
    const localISOTime = new Date(now.getTime() - timezoneOffset).toISOString().slice(0, 16);
    return localISOTime;
};

onMounted(() => {
    form.fecha_pago = getCurrentDateTime();
});

// Función para manejar el envío del formulario
const submit = () => {
    // Antes de enviar, transformamos los datos para incluir el 'cambio' calculado
    form.transform((data) => ({
        ...data,
        monto: montoTotalAPagar.value, // Aseguramos que 'monto' sea el total a pagar
        cambio: cambioCalculado.value, // Añadimos el 'cambio' calculado
    })).post(route('pagos.store'), {
        onSuccess: () => {
            form.reset(); // Reiniciar el formulario después de un registro exitoso
            form.fecha_pago = getCurrentDateTime(); // Reestablecer la fecha/hora actual
            // También reseteamos manualmente los campos que no son parte de form.reset()
            form.monto_recibido = 0;
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
    { title: 'Crear pago', href: '#' },
];
</script>

<template>
    <Head title="Crear Pago" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
            <div class="w-full max-w-3xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Crear Pago</h1>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha y Hora</label>
                                <input type="datetime-local" v-model="form.fecha_pago"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" />
                                <InputError :message="form.errors.fecha_pago" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <Label for="venta_id">Venta Asociada</Label>
                                <Select v-model="form.venta_id" @update:modelValue="handleVentaChange" :disabled="form.compra_id !== null">
                                    <SelectTrigger class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                        <SelectValue :placeholder="displaySelectedVenta" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="null">(No asociada a Venta)</SelectItem>
                                        <SelectItem v-for="venta in props.ventas" :key="venta.id" :value="venta.id">
                                            ID: {{ venta.id }} - Total: {{ formatCurrency(venta.total) }} (Cliente: {{ venta.cliente.nombre }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.venta_id" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <Label for="compra_id">Compra Asociada</Label>
                                <Select v-model="form.compra_id" @update:modelValue="handleCompraChange" :disabled="form.venta_id !== null">
                                    <SelectTrigger class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                        <SelectValue :placeholder="displaySelectedCompra" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="null">(No asociada a Compra)</SelectItem>
                                        <SelectItem v-for="compra in props.compras" :key="compra.id" :value="compra.id">
                                            ID: {{ compra.id }} - Total: {{ formatCurrency(compra.total) }} (Proveedor: {{ compra.proveedor.razon_social }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.compra_id" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <Label for="monto_a_pagar">Monto Total a Pagar</Label>
                                <Input id="monto_a_pagar" type="text" :value="formatCurrency(montoTotalAPagar)" readonly class="bg-gray-100 dark:bg-gray-700 cursor-not-allowed" />
                                <InputError :message="form.errors.monto" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <Label for="monto_recibido">Monto Recibido</Label>
                                <Input id="monto_recibido" type="text" v-model="displayMontoRecibido" :required="montoTotalAPagar > 0"
                                    @focus="displayMontoRecibido = String(form.monto_recibido)"
                                    @blur="displayMontoRecibido = formatCurrency(form.monto_recibido)"/>
                                <InputError :message="form.errors.monto_recibido" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <Label for="cambio">Cambio</Label>
                                <Input id="cambio" type="text" :value="formatCurrency(cambioCalculado)" readonly class="bg-gray-100 dark:bg-gray-700 cursor-not-allowed" />
                            </div>

                            <div class="mb-4">
                                <Label for="metodo_pago">Método de Pago</Label>
                                <Select v-model="form.metodo_pago" required>
                                    <SelectTrigger class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                        <SelectValue placeholder="Selecciona un método de pago" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="efectivo">Efectivo</SelectItem>
                                        <SelectItem value="tarjeta_credito">Tarjeta Crédito</SelectItem>
                                        <SelectItem value="tarjeta_debito">Tarjeta Débito</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.metodo_pago" class="mt-2" />
                            </div>

                            <div class="mb-6">
                                <Label for="referencia_pago">Referencia de Pago (opcional)</Label>
                                <Input id="referencia_pago" type="text" v-model="referenciaPagoModel" />
                                <InputError :message="form.errors.referencia_pago" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
                            <button type="submit"
                                class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all"
                                :disabled="form.processing">
                                <span v-if="form.processing">Procesando...</span>
                                <span v-else>Guardar</span>
                            </button>
                            <button type="button" @click="router.get(route('pagos.index'))"
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
