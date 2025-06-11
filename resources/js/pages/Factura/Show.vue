<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types'; // Importar el tipo BreadcrumbItem

// Tipos de datos para el frontend (adaptados para el show, similar al edit)
interface Producto {
    id: number;
    nombre: string;
}

interface DetalleFacturaFromBackend {
    id: number;
    producto_id: number;
    producto?: { nombre: string; }; // Asumimos que el producto se carga con el detalle
    cantidad: number;
    precio_unitario: number;
    subtotal: number;
    impuesto_iva: number;
}

interface FacturaFromBackend {
    id: number;
    venta_id: number | null;
    venta: { id: number; total: number; } | null; // Asumimos que la venta y su total se cargan
    user_id: number;
    cliente_id: number | null;
    fecha_emision: string;
    total: number;
    metodo_pago: 'efectivo' | 'tarjeta_credito' | 'tarjeta_debito';
    estado: 'pendiente' | 'pagada' | 'anulada';
    numero_factura?: string;
    cliente: { id: number; nombre: string; } | null;
    user: { id: number; name: string; } | null;
    detalle_facturas: DetalleFacturaFromBackend[];
}

const props = defineProps<{
    factura: FacturaFromBackend; // La factura a mostrar
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Facturas', href: route('facturas.index') },
    { title: `Ver #${props.factura.id}`, href: route('facturas.show', props.factura.id), active: true },
];

// Formatear monedas para visualización
const formatCurrency = (value: number) => {
    return Number(value).toLocaleString('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });
};

// Formatear fecha
const formatDate = (dateString: string) => {
    const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('es-ES', options);
};
</script>

<template>
    <Head :title="`Ver Factura #${factura.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
            <div class="w-full max-w-4xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Detalle de Factura #{{ factura.id }}</h1>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-700 dark:text-gray-300">
                        <div>
                            <p class="font-medium mb-1">Número de Factura:</p>
                            <p class="mb-4 text-gray-800 dark:text-white">{{ factura.numero_factura || 'N/A' }}</p>

                            <p class="font-medium mb-1">Venta Asociada:</p>
                            <p class="mb-4 text-gray-800 dark:text-white">
                                <span v-if="factura.venta">Venta #{{ factura.venta.id }} (Total: {{ formatCurrency(factura.venta.total) }})</span>
                                <span v-else>N/A</span>
                            </p>

                            <p class="font-medium mb-1">Cajero (Usuario):</p>
                            <p class="mb-4 text-gray-800 dark:text-white">{{ factura.user?.name || 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="font-medium mb-1">Cliente:</p>
                            <p class="mb-4 text-gray-800 dark:text-white">{{ factura.cliente?.nombre || 'Sin Cliente' }}</p>

                            <p class="font-medium mb-1">Fecha de Emisión:</p>
                            <p class="mb-4 text-gray-800 dark:text-white">{{ formatDate(factura.fecha_emision) }}</p>

                            <p class="font-medium mb-1">Método de Pago:</p>
                            <p class="mb-4 text-gray-800 dark:text-white">{{ factura.metodo_pago }}</p>

                            <p class="font-medium mb-1">Estado:</p>
                            <p class="mb-4 text-gray-800 dark:text-white">{{ factura.estado }}</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Detalles de los Productos</h2>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="factura.detalle_facturas.length === 0">
                                        <td colspan="6" class="p-4 text-center text-gray-500 dark:text-gray-400">
                                            No hay productos detallados para esta factura.
                                        </td>
                                    </tr>
                                    <tr v-for="detalle in factura.detalle_facturas" :key="detalle.id"
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="p-3">{{ detalle.producto?.nombre || 'N/A' }}</td>
                                        <td class="p-3 text-center">{{ detalle.cantidad }}</td>
                                        <td class="p-3 text-right">{{ formatCurrency(detalle.precio_unitario) }}</td>
                                        <td class="p-3 text-right">{{ formatCurrency(detalle.subtotal) }}</td>
                                        <td class="p-3 text-right">{{ formatCurrency(detalle.impuesto_iva) }}</td>
                                        <td class="p-3 text-right">{{ formatCurrency(detalle.subtotal + detalle.impuesto_iva) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="text-right mt-6 space-y-2 text-gray-800 dark:text-white">
                            <p class="text-lg"><strong>Total IVA de Factura:</strong> {{ formatCurrency(factura.detalle_facturas.reduce((sum, d) => sum + d.impuesto_iva, 0)) }}</p>
                            <p class="text-xl font-bold"><strong>Total de la Factura:</strong> {{ formatCurrency(factura.total) }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <Link :href="route('facturas.index')">
                            <Button class="px-6 py-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition-all text-center">
                                Volver a Facturas
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
