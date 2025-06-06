<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { onMounted, computed, watch } from 'vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

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

// Función para obtener la fecha y hora actual en formato compatible con datetime-local (solo si no hay fecha_pago existente)
const getCurrentDateTime = () => {
    const now = new Date();
    const timezoneOffset = now.getTimezoneOffset() * 60000;
    const localISOTime = new Date(now.getTime() - timezoneOffset).toISOString().slice(0, 16);
    return localISOTime;
};

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
            // No resetear el formulario en "edit" después de éxito, solo redirigir
            // o mostrar un mensaje de éxito.
            // Si quieres resetear, hazlo selectivamente o redirige.
            // form.reset();
            // form.fecha_pago = getCurrentDateTime();
            // form.monto_recibido = 0; // Resetear el monto recibido si se mantiene en la misma página
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
                                <p v-if="form.errors.fecha_pago" class="text-red-500 text-sm mt-1">{{ form.errors.fecha_pago }}</p>
                            </div>

                            <div></div>
                            <div class="mb-4">
                                <Label for="venta_id">Venta Asociada</Label>
                                <Select v-model="form.venta_id" @update:modelValue="handleVentaChange" :disabled="form.compra_id !== null">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecciona una venta (opcional)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="null">(No asociada a Venta)</SelectItem>
                                        <SelectItem v-for="venta in props.ventas" :key="venta.id" :value="venta.id">
                                            ID: {{ venta.id }} - Total:{{ Number(venta.total).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }) }} (Cliente: {{ venta.cliente.nombre }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <span v-if="form.errors.venta_id" class="text-red-500 text-sm mt-1">{{ form.errors.venta_id }}</span>
                            </div>

                            <div class="mb-4">
                                <Label for="compra_id">Compra Asociada</Label>
                                <Select v-model="form.compra_id" @update:modelValue="handleCompraChange" :disabled="form.venta_id !== null">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecciona una compra (opcional)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="null">(No asociada a Compra)</SelectItem>
                                        <SelectItem v-for="compra in props.compras" :key="compra.id" :value="compra.id">
                                            ID: {{ compra.id }} - Total:{{ Number(compra.total).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }) }} (Proveedor: {{ compra.proveedor.razon_social }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <span v-if="form.errors.compra_id" class="text-red-500 text-sm mt-1">{{ form.errors.compra_id }}</span>
                            </div>

                            <div class="mb-4">
                                <Label for="monto_a_pagar">Monto Total a Pagar</Label>
                                <Input
                                    id="monto_a_pagar"
                                    type="number"
                                    step="0.01"
                                    :value="montoTotalAPagar"
                                    readonly
                                    class="bg-gray-100 dark:bg-gray-700 cursor-not-allowed"
                                />
                                <span v-if="form.errors.monto" class="text-red-500 text-sm mt-1">{{ form.errors.monto }}</span>
                            </div>

                            <div class="mb-4">
                                <Label for="monto_recibido">Monto Recibido</Label>
                                <Input
                                    id="monto_recibido"
                                    type="number"
                                    step="0.01"
                                    v-model.number="form.monto_recibido"
                                    :required="montoTotalAPagar > 0"
                                />
                                <span v-if="form.errors.monto_recibido" class="text-red-500 text-sm mt-1">{{ form.errors.monto_recibido }}</span>
                            </div>

                            <div class="mb-4">
                                <Label for="cambio">Cambio</Label>
                                <Input
                                    id="cambio"
                                    type="number"
                                    step="0.01"
                                    :value="cambioCalculado"
                                    readonly
                                    class="bg-gray-100 dark:bg-gray-700 cursor-not-allowed"
                                />
                            </div>

                            <div class="mb-4">
                                <Label for="metodo_pago">Método de Pago</Label>
                                <Select v-model="form.metodo_pago" required>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecciona un método de pago" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="efectivo">Efectivo</SelectItem>
                                        <SelectItem value="tarjeta_credito">Tarjeta Crédito</SelectItem>
                                        <SelectItem value="tarjeta_debito">Tarjeta Débito</SelectItem>
                                    </SelectContent>
                                </Select>
                                <span v-if="form.errors.metodo_pago" class="text-red-500 text-sm mt-1">{{ form.errors.metodo_pago }}</span>
                            </div>

                            <div class="mb-6">
                                <Label for="referencia_pago">Referencia de Pago (opcional)</Label>
                                <Input id="referencia_pago" type="text" v-model="referenciaPagoModel" />
                                <span v-if="form.errors.referencia_pago" class="text-red-500 text-sm mt-1">{{ form.errors.referencia_pago }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
                            <button type="submit" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all" :disabled="form.processing">
                                <span v-if="form.processing">Actualizando...</span>
                                <span v-else>Actualizar</span>
                            </button>
                            <button type="button" @click="router.get('/pagos')"
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
