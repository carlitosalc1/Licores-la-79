<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell, TableCaption } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

// Importar tipos
import { type BreadcrumbItem } from '@/types';

// Definición de props con tipado
interface Props {
    compras: {
        data: Array<{
            id: number;
            proveedor?: { id: number; nombre: string };
            fecha_compra: string;
            total_compra: number;
            estado: string;
            detalle_compras: Array<{
                id: number;
                producto: { nombre: string };
                cantidad: number;
                precio_unitario: number;
            }>;
        }>;
        links: Array<{ url?: string; label: string; active: boolean; }>;
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
        from: number | null;
        to: number | null;
        total: number;
    };
    filtros: {
        proveedor_id?: number;
        fecha_inicio?: string;
        fecha_fin?: string;
    };
    totalCompras: number;
    proveedores: Array<{ id: number; nombre: string }>; // Para el select de proveedores
}

const props = defineProps<Props>();

// Estado del formulario con tipado
const form = ref<{
    proveedor_id: number | null;
    fecha_inicio: string;
    fecha_fin: string;
}>({
    proveedor_id: props.filtros.proveedor_id || null,
    fecha_inicio: props.filtros.fecha_inicio || '',
    fecha_fin: props.filtros.fecha_fin || '',
});

// Definir los ítems del breadcrumb
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Compras por Proveedor', href: '/compras_por_proveedor' }]

const applyFilters = () => {
    router.get(route('reportes.compras'), { ...form.value, page: 1 }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    form.value.proveedor_id = null;
    form.value.fecha_inicio = '';
    form.value.fecha_fin = '';
    applyFilters();
};

const exportToExcel = () => {
    const params = new URLSearchParams(form.value as Record<string, string>).toString();
    window.location.href = route('reportes.compras.exportarExcel') + (params ? '?' + params : '');
};

const exportToPdf = () => {
    const params = new URLSearchParams(form.value as Record<string, string>).toString();
    window.location.href = route('reportes.compras.exportarPdf') + (params ? '?' + params : '');
};

const goToPage = (url: string | null) => {
    if (url) {
        router.get(url, {}, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

// watch(form.value, () => {
//     applyFilters();
// }, { deep: true });
</script>

<template>
    <Head title="Reporte de Compras por Proveedor" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div class="mb-6 border-b pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <Label for="proveedor">Proveedor</Label>
                                <Select v-model="form.proveedor_id">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Selecciona un proveedor" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="null">Todos los proveedores</SelectItem>
                                        <SelectItem v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                                            {{ proveedor.nombre }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
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
                        Total de Compras Filtradas: ${{ totalCompras ? totalCompras.toFixed(2) : '0.00' }}
                    </div>

                    <div class="overflow-x-auto rounded-md border">
                        <Table>
                            <TableCaption v-if="compras.data.length === 0">No se encontraron compras para los filtros aplicados.</TableCaption>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID Compra</TableHead>
                                    <TableHead>Proveedor</TableHead>
                                    <TableHead>Fecha</TableHead>
                                    <TableHead>Total</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Productos</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="compra in compras.data" :key="compra.id">
                                    <TableCell class="font-medium">{{ compra.id }}</TableCell>
                                    <TableCell>{{ compra.proveedor ? compra.proveedor.nombre : 'N/A' }}</TableCell>
                                    <TableCell>{{ new Date(compra.fecha_compra).toLocaleDateString() }} {{ new Date(compra.fecha_compra).toLocaleTimeString() }}</TableCell>
                                    <TableCell>${{ compra.total_compra.toFixed(2) }}</TableCell>
                                    <TableCell>{{ compra.estado }}</TableCell>
                                    <TableCell>
                                        <ul class="list-disc list-inside text-sm">
                                            <li v-for="detalle in compra.detalle_compras" :key="detalle.id">
                                                {{ detalle.producto.nombre }} ({{ detalle.cantidad }} x ${{ detalle.precio_unitario.toFixed(2) }})
                                            </li>
                                        </ul>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div v-if="compras.links.length > 3" class="flex justify-center mt-6">
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <Button
                                variant="outline"
                                :disabled="!compras.prev_page_url"
                                @click="goToPage(compras.prev_page_url)"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                            >
                                Anterior
                            </Button>

                            <Button
                                v-for="link in compras.links.slice(1, -1)"
                                :key="link.label"
                                @click="goToPage(link.url)"
                                :variant="link.active ? 'default' : 'outline'"
                                :disabled="!link.url"
                                :class="{
                                    'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                                    'bg-white border-gray-300 text-gray-700 hover:bg-gray-50': !link.active,
                                    'hidden sm:inline-flex': link.label.includes('...')
                                }"
                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                v-html="link.label"
                            />

                            <Button
                                variant="outline"
                                :disabled="!compras.next_page_url"
                                @click="goToPage(compras.next_page_url)"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                            >
                                Siguiente
                            </Button>
                        </nav>
                    </div>

                    <div v-if="compras.total > 0" class="mt-4 text-center text-sm text-gray-600">
                        Mostrando {{ compras.from }} a {{ compras.to }} de {{ compras.total }} resultados.
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
