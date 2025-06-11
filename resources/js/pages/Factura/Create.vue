<script setup lang="ts">
import { computed, watch, ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import type { BreadcrumbItem } from '@/types';

// Tipos de datos para el frontend
interface Producto {
    id: number;
    nombre: string;

}

interface DetalleVentaFromBackend {
    producto_id: number;
    producto_nombre: string;
    cantidad: number;
    precio_unitario: number;
    subtotal: number;
    impuesto_iva: number;
}

interface Venta {
    id: number;
    total_venta: number;
    cliente: { id: number; nombre: string; } | null;
    user: { id: number; name: string; } | null;
    detalles_venta: DetalleVentaFromBackend[];
}

interface Cliente {
    id: number;
    nombre: string;
}

interface User {
    id: number;
    name: string;
}

// Interfaz para los detalles de la factura que enviará el formulario
interface FacturaDetalleForm {
    producto_id: number;
    producto_nombre: string; // Solo para mostrar en el frontend, no se envía
    cantidad: number;
    precio: number; // Propiedad para el v-model y cálculos en el frontend
    precio_unitario: number;
    subtotal: number;
    impuesto_iva: number;
    total: number;
}

// Define the structure of the entire form data for useForm
interface FacturaFormPayload {
    venta_id: number | null;
    user_id: number | null;
    cliente_id: number | null;
    metodo_pago: 'efectivo' | 'tarjeta_credito' | 'tarjeta_debito' | '';
    estado: 'pendiente' | 'pagada' | 'anulada';
    detalles_factura: FacturaDetalleForm[];
    total_iva: number;
    total_venta: number;
    [key: string]: any;
}

const props = defineProps<{
    ventasSinFactura: Venta[];
    clientes: Cliente[];
    users: User[];
    productos: Producto[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Facturas', href: route('facturas.index') },
    { title: 'Crear', href: route('facturas.create'), active: true },
];

// Initialize useForm with the explicit FacturaFormPayload type
const form = useForm<FacturaFormPayload>({
    venta_id: null,
    user_id: null,
    cliente_id: null,
    metodo_pago: '' as 'efectivo' | 'tarjeta_credito' | 'tarjeta_debito' | '',
    estado: 'pendiente' as 'pendiente' | 'pagada' | 'anulada',
    detalles_factura: [],
    total_iva: 0,
    total_venta: 0,
});

// Watch para precargar datos de la venta seleccionada
watch(() => form.venta_id, (newVentaId) => {
    form.detalles_factura = []; // Limpiar detalles anteriores

    if (newVentaId !== null) {
        const selectedVenta = props.ventasSinFactura.find(venta => venta.id === newVentaId);
        if (selectedVenta) {
            form.user_id = selectedVenta.user?.id || null;
            form.cliente_id = selectedVenta.cliente?.id || null;

            // Mapear los detalles de la venta a detalles de factura
            // y recalcular sus propios subtotales, impuestos y total para asegurar consistencia
            form.detalles_factura = selectedVenta.detalles_venta.map(detalle => {
                const IVA_RATE = 0.19; // Tasa de IVA (19% para Colombia)
                const subtotalCalculado = detalle.cantidad * detalle.precio_unitario;
                const impuestoIvaCalculado = subtotalCalculado * IVA_RATE;
                const totalCalculado = subtotalCalculado + impuestoIvaCalculado;
                return {
                    producto_id: detalle.producto_id,
                    producto_nombre: detalle.producto_nombre,
                    cantidad: detalle.cantidad,
                    precio: parseFloat(detalle.precio_unitario.toFixed(2)),
                    precio_unitario: parseFloat(detalle.precio_unitario.toFixed(2)),
                    subtotal: parseFloat(subtotalCalculado.toFixed(2)),
                    impuesto_iva: parseFloat(impuestoIvaCalculado.toFixed(2)),
                    total: parseFloat(totalCalculado.toFixed(2)),
                };
            });
        }
    }
}, { immediate: false }); // No ejecutar inmediatamente al cargar la página

// Función para recalcular subtotal, impuesto_iva y total para un detalle
const recalcularTotales = (index: number) => {
    const detalle = form.detalles_factura[index];
    if (!detalle) {
        return;
    }

    const IVA_RATE = 0.19; // 19% de IVA para Colombia

    detalle.cantidad = Math.max(1, Number(detalle.cantidad) || 1);
    detalle.precio = Number(detalle.precio) || 0;
    detalle.precio_unitario = detalle.precio; // Sincroniza precio_unitario con precio para el backend


    detalle.subtotal = parseFloat((detalle.cantidad * detalle.precio).toFixed(2));
    detalle.impuesto_iva = parseFloat((detalle.subtotal * IVA_RATE).toFixed(2));
    detalle.total = parseFloat((detalle.subtotal + detalle.impuesto_iva).toFixed(2));
};

// Propiedad computada para calcular el total de IVA de la factura
const totalIVAComputed = computed(() => {
    return form.detalles_factura.reduce((sum, detalle) => {
        return sum + detalle.impuesto_iva;
    }, 0);
});

// Propiedad computada para calcular el total de la factura (total venta)
const totalFacturaComputed = computed(() => {
    return form.detalles_factura.reduce((sum, detalle) => {
        return sum + detalle.total;
    }, 0);
});

// Watch for changes in totalIVAComputed and totalFacturaComputed to update form fields
watch(totalIVAComputed, (newValue) => {
    form.total_iva = parseFloat(newValue.toFixed(2));
});

watch(totalFacturaComputed, (newValue) => {
    form.total_venta = parseFloat(newValue.toFixed(2));
});


// Formatear monedas para visualización
const formatCurrency = (value: number) => {
    return Number(value).toLocaleString('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });
};

// Función para enviar el formulario
const submit = () => {
    // Se elimina form.transform para asegurar que user_id se envíe al backend
    form.post(route('facturas.store'), {
        onSuccess: () => {
            form.reset();
            router.visit(route('facturas.index'));
        },
        onError: (errors) => {
            console.error("Error al crear factura:", errors);
        },
    });
};

// Function to remove a detail row
const removeDetalle = (index: number) => {
    form.detalles_factura.splice(index, 1);
};
</script>

<template>
    <Head title="Crear Factura" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
            <div class="w-full max-w-4xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Crear Nueva Factura</h1>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="venta_id" class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Venta Asociada</label>
                                <select id="venta_id" v-model="form.venta_id" class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" >
                                    <option :value="null" disabled>Seleccione una venta</option>
                                    <option v-for="venta in props.ventasSinFactura" :key="venta.id" :value="venta.id">
                                        Venta #{{ venta.id }} (Cliente: {{ venta.cliente?.nombre || 'N/A' }} - Total: {{ formatCurrency(venta.total_venta) }})
                                    </option>
                                </select>
                                <InputError :message="form.errors.venta_id" class="mt-2" />
                            </div>

                            <div>
                                <label for="user_id" class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Cajero (Usuario)</label>
                                <select id="user_id" v-model="form.user_id" class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                    <option :value="null" disabled>Seleccione un cajero</option>
                                    <option v-for="user in props.users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.user_id" class="mt-2" />
                            </div>

                            <div>
                                <label for="cliente_id" class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Cliente</label>
                                <select id="cliente_id" v-model="form.cliente_id" class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                    <option :value="null">Sin Cliente</option>
                                    <option v-for="cliente in props.clientes" :key="cliente.id" :value="cliente.id">
                                        {{ cliente.nombre }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.cliente_id" class="mt-2" />
                            </div>

                            <div>
                                <label for="total" class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Total Factura</label>
                                <Input type="text" id="total" :value="formatCurrency(totalFacturaComputed)" readonly class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 cursor-not-allowed" />
                            </div>

                            <div>
                                <label for="metodo_pago" class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Método de Pago</label>
                                <select id="metodo_pago" v-model="form.metodo_pago" class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                                    <option value="" disabled selected>Seleccione un método de pago</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                    <option value="tarjeta_debito">Tarjeta de Débito</option>
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

                        <div class="mt-6">
                            <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Detalles de la Factura</h2>
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
                                        <tr v-if="form.detalles_factura.length === 0">
                                            <td colspan="7" class="p-4 text-center text-gray-500 dark:text-gray-400">
                                                Seleccione una venta para cargar los detalles.
                                            </td>
                                        </tr>
                                        <tr v-for="(detalle, index) in form.detalles_factura" :key="index"
                                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <td class="p-3">{{ detalle.producto_nombre }}</td>
                                            <td class="p-3 text-center">
                                                <input type="number" :id="`cantidad-${index}`" v-model.number="detalle.cantidad" @input="recalcularTotales(index)" min="1"
                                                    class="w-20 text-center rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 p-2 focus:ring-1 focus:ring-cyan-500 focus:border-transparent transition-all" />
                                                <InputError :message="form.errors[`detalles_factura.${index}.cantidad`]" class="mt-2" />
                                            </td>
                                            <td class="p-3 text-right">
                                                <input type="number" :id="`precio-${index}`" v-model.number="detalle.precio" @input="recalcularTotales(index)" step="0.01"
                                                    class="w-24 text-right rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 p-2 focus:ring-1 focus:ring-cyan-500 focus:border-transparent transition-all" />
                                                <InputError :message="form.errors[`detalles_factura.${index}.precio`]" class="mt-2" />
                                            </td>
                                            <td class="p-3 text-right">{{ formatCurrency(detalle.subtotal) }}</td>
                                            <td class="p-3 text-right">{{ formatCurrency(detalle.impuesto_iva) }}</td>
                                            <td class="p-3 text-right">{{ formatCurrency(detalle.total) }}</td>
                                            <td class="p-3 text-center">
                                                <button type="button" @click="removeDetalle(index)"
                                                    class="font-medium text-red-600 dark:text-red-500 hover:underline">
                                                    Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-right mt-6 space-y-2 text-gray-800 dark:text-white">
                                <p class="text-lg"><strong>Total IVA:</strong> {{ formatCurrency(totalIVAComputed) }}</p>
                                <p class="text-xl font-bold"><strong>Total Factura:</strong> {{ formatCurrency(totalFacturaComputed) }}</p>
                            </div>
                        </div>
                        <InputError :message="form.errors['detalles_factura']" class="mt-2" />


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
