<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell, TableCaption } from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';

// Definición de props con tipado
interface Props {
    productos: Array<{ // Este reporte no está paginado en el controlador, por eso es un Array simple
        nombre: string;
        codigo: string;
        total_vendido: number;
    }>;
    filtros: {
        fecha_inicio?: string;
        fecha_fin?: string;
        limite?: number;
    };
}

const props = defineProps<Props>();

// Estado del formulario con tipado
const form = ref<{
    fecha_inicio: string;
    fecha_fin: string;
    limite: number | null;
}>({
    fecha_inicio: props.filtros.fecha_inicio || '',
    fecha_fin: props.filtros.fecha_fin || '',
    limite: props.filtros.limite || 10, // Por defecto, mostrar los 10 más vendidos
});

// Definir los ítems del breadcrumb
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Productos Más Vendidos', href: '/producto_mas_vendidos' }]

const applyFilters = () => {
    router.get(route('reportes.productos-vendidos'), form.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    form.value.fecha_inicio = '';
    form.value.fecha_fin = '';
    form.value.limite = 10;
    applyFilters();
};

const exportToExcel = () => {
    const params = new URLSearchParams(form.value as Record<string, string>).toString();
    window.location.href = route('reportes.productos-vendidos.exportarExcel') + (params ? '?' + params : '');
};

const exportToPdf = () => {
    const params = new URLSearchParams(form.value as Record<string, string>).toString();
    window.location.href = route('reportes.productos-vendidos.exportarPdf') + (params ? '?' + params : '');
};

// Este reporte no tiene paginación, así que no necesitamos goToPage
// watch(form.value, () => {
//     applyFilters();
// }, { deep: true });
</script>

<template>
    <Head title="Reporte de Productos Más Vendidos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div class="mb-6 border-b pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                            <div>
                                <Label for="limite">Límite de Productos</Label>
                                <Input
                                    id="limite"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full"
                                    v-model="form.limite"
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

                    <div class="overflow-x-auto rounded-md border">
                        <Table>
                            <TableCaption v-if="productos.length === 0">No se encontraron productos más vendidos para los filtros aplicados.</TableCaption>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nombre Producto</TableHead>
                                    <TableHead>Código</TableHead>
                                    <TableHead>Cantidad Vendida</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="(producto, index) in productos" :key="index">
                                    <TableCell class="font-medium">{{ producto.nombre }}</TableCell>
                                    <TableCell>{{ producto.codigo }}</TableCell>
                                    <TableCell>{{ producto.total_vendido }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div v-if="productos.length > 0" class="mt-4 text-center text-sm text-gray-600">
                        Mostrando los {{ productos.length }} productos más vendidos.
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
