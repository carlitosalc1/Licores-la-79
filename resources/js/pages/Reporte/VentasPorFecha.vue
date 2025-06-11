<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell, TableCaption } from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types'; // Asegúrate de que esta ruta sea correcta

// Definición de props con tipado
interface VentaData {
    id: number;
    cliente?: { nombre: string; apellido: string; cedula_ruc: string };
    fecha_venta: string;
    total: number; // CAMBIO: Usar 'total' en lugar de 'total_venta' para coincidir con el backend
    estado: string;
    detalle_ventas: Array<{
        id: number;
        producto: { nombre: string };
        cantidad: number;
        precio_unitario: number;
    }>;
}

interface PaginationLinks {
    url?: string;
    label: string;
    active: boolean;
}

interface Props {
    ventas: {
        data: VentaData[];
        links: PaginationLinks[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
        from: number | null;
        to: number | null;
        total: number;
    };
    filtros: {
        fecha_inicio?: string;
        fecha_fin?: string;
    };
    totalVentas: number; // Este es el total sumado de todas las ventas filtradas, no por página
}

const props = defineProps<Props>();

// Estado del formulario con tipado
const form = ref<{
    fecha_inicio: string;
    fecha_fin: string;
}>({
    fecha_inicio: props.filtros.fecha_inicio || '',
    fecha_fin: props.filtros.fecha_fin || '',
});

// Definir los ítems del breadcrumb
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Reportes', href: route('reportes.index') }, { title: 'Ventas por Fechas', href: route('reportes.ventas') }];

const applyFilters = () => {
    // Si se aplica un filtro, volvemos a la primera página por defecto
    // Usamos `router.get` para enviar los filtros como parámetros de la URL
    router.get(route('reportes.ventas'), { ...form.value, page: 1 }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    form.value.fecha_inicio = '';
    form.value.fecha_fin = '';
    applyFilters(); // Vuelve a cargar sin filtros
};

const exportToExcel = () => {
    const params = new URLSearchParams(form.value as Record<string, string>).toString();
    // Usa window.location.href para disparar la descarga directamente
    window.location.href = route('reportes.ventas.exportarExcel') + (params ? '?' + params : '');
};

const exportToPdf = () => {
    const params = new URLSearchParams(form.value as Record<string, string>).toString();
    // Usa window.location.href para disparar la descarga directamente
    window.location.href = route('reportes.ventas.exportarPdf') + (params ? '?' + params : '');
};

// Función para navegar entre páginas
const goToPage = (url: string | null) => {
    if (url) {
        router.get(url, {}, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

// Opcional: watch para aplicar filtros automáticamente al cambiar las fechas
// Puedes activar esto si quieres que los filtros se apliquen cada vez que el usuario selecciona una fecha
// watch([() => form.value.fecha_inicio, () => form.value.fecha_fin], () => {
//     applyFilters();
// });
</script>

<template>
    <Head title="Reporte de Ventas" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div class="mb-6 border-b pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros de Ventas</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label for="fecha_inicio">Fecha Inicio</Label>
                                <Input
                                    id="fecha_inicio"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.fecha_inicio"
                                />
                            </div>
                            <div>
                                <Label for="fecha_fin">Fecha Fin</Label>
                                <Input
                                    id="fecha_fin"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.fecha_fin"
                                />
                            </div>
                        </div>
                        <div class="mt-4 flex space-x-2">
                            <Button @click="applyFilters">Aplicar Filtros</Button>
                            <Button @click="resetFilters" variant="outline">Restablecer</Button>
                        </div>
                    </div>

                    <div class="mb-6 flex space-x-4">
                        <Button @click="exportToExcel">Exportar a Excel</Button>
                        <Button @click="exportToPdf" variant="destructive">Exportar a PDF</Button>
                    </div>

                    <div class="mb-4 text-xl font-bold">
                        Total de Ventas Filtradas: ${{ totalVentas ? totalVentas.toFixed(2) : '0.00' }}
                    </div>

                    <div class="overflow-x-auto rounded-md border">
                        <Table>
                            <TableCaption v-if="ventas.data.length === 0">No se encontraron ventas para los filtros aplicados.</TableCaption>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID Venta</TableHead>
                                    <TableHead>Cliente</TableHead>
                                    <TableHead>Fecha</TableHead>
                                    <TableHead>Total</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Detalles</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="venta in ventas.data" :key="venta.id">
                                    <TableCell class="font-medium">{{ venta.id }}</TableCell>
                                    <TableCell>{{ venta.cliente ? venta.cliente.nombre + ' ' + venta.cliente.apellido : 'Consumidor Final' }}</TableCell>
                                    <TableCell>{{ new Date(venta.fecha_venta).toLocaleDateString() }} {{ new Date(venta.fecha_venta).toLocaleTimeString() }}</TableCell>
                                    <TableCell>${{ venta.total.toFixed(2) }}</TableCell> <TableCell>{{ venta.estado }}</TableCell>
                                    <TableCell>
                                        <ul class="list-disc list-inside text-sm">
                                            <li v-for="detalle in venta.detalle_ventas" :key="detalle.id">
                                                {{ detalle.producto.nombre }} ({{ detalle.cantidad }} x ${{ detalle.precio_unitario.toFixed(2) }})
                                            </li>
                                        </ul>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div v-if="ventas.links.length > 3" class="flex justify-center mt-6 space-x-2">
                        <Button
                            v-if="ventas.prev_page_url"
                            variant="outline"
                            @click="goToPage(ventas.prev_page_url)"
                        >
                            Anterior
                        </Button>

                        <Button
                            v-for="link in ventas.links.filter(link => !link.label.includes('Anterior') && !link.label.includes('Siguiente'))"
                            :key="link.label"
                            @click="goToPage(link.url)"
                            :variant="link.active ? 'default' : 'outline'"
                            :disabled="!link.url"
                        >
                            <span v-html="link.label"></span>
                        </Button>

                        <Button
                            v-if="ventas.next_page_url"
                            variant="outline"
                            @click="goToPage(ventas.next_page_url)"
                        >
                            Siguiente
                        </Button>
                    </div>


                    <div v-if="ventas.total > 0" class="mt-4 text-center text-sm text-gray-600">
                        Mostrando {{ ventas.from }} a {{ ventas.to }} de {{ ventas.total }} resultados.
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
