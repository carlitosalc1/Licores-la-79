<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell, TableCaption } from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';

// Definición de props con tipado
interface Props {
    productos: {
        data: Array<{
            id: number;
            nombre: string;
            codigo: string;
            categoria_producto?: { nombre: string };
            stock: number;
            stock_minimo: number;
            precio_venta: number;
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
        stock_bajo?: boolean;
        nombre?: string;
    };
}

const props = defineProps<Props>();

// Estado del formulario con tipado
const form = ref<{
    stock_bajo: boolean;
    nombre: string;
}>({
    stock_bajo: props.filtros.stock_bajo || false,
    nombre: props.filtros.nombre || '',
});

// Definir los ítems del breadcrumb
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Inventario Actual', href: '/inventario_actual' }]

const applyFilters = () => {
    // Si se aplica un filtro, volvemos a la primera página por defecto
    router.get(route('reportes.inventario'), { ...form.value, page: 1 }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    form.value.stock_bajo = false;
    form.value.nombre = '';
    applyFilters();
};

const exportToExcel = () => {
    const params = new URLSearchParams(form.value as Record<string, string>).toString();
    window.location.href = route('reportes.inventario.exportarExcel') + (params ? '?' + params : '');
};

const exportToPdf = () => {
    const params = new URLSearchParams(form.value as Record<string, string>).toString();
    window.location.href = route('reportes.inventario.exportarPdf') + (params ? '?' + params : '');
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

// Opcional: watch para aplicar filtros automáticamente
// watch(form.value, () => {
//     applyFilters();
// }, { deep: true });
</script>

<template>
    <Head title="Reporte de Inventario Actual" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div class="mb-6 border-b pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label for="nombre">Nombre del Producto</Label>
                                <Input
                                    id="nombre"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.nombre"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                            <div class="flex items-center gap-2 mt-6">
                                <Checkbox id="stock_bajo" v-model:checked="form.stock_bajo" />
                                <Label for="stock_bajo">Mostrar solo productos con stock bajo</Label>
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
                            <TableCaption v-if="productos.data.length === 0">No se encontraron productos para los filtros aplicados.</TableCaption>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Nombre</TableHead>
                                    <TableHead>Código</TableHead>
                                    <TableHead>Categoría</TableHead>
                                    <TableHead>Stock</TableHead>
                                    <TableHead>Stock Mínimo</TableHead>
                                    <TableHead>Precio Venta</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="producto in productos.data" :key="producto.id">
                                    <TableCell class="font-medium">{{ producto.id }}</TableCell>
                                    <TableCell>{{ producto.nombre }}</TableCell>
                                    <TableCell>{{ producto.codigo }}</TableCell>
                                    <TableCell>{{ producto.categoria_producto ? producto.categoria_producto.nombre : 'N/A' }}</TableCell>
                                    <TableCell :class="{ 'text-red-600 font-bold': producto.stock <= producto.stock_minimo }">{{ producto.stock }}</TableCell>
                                    <TableCell>{{ producto.stock_minimo }}</TableCell>
                                    <TableCell>${{ producto.precio_venta.toFixed(2) }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div v-if="productos.links.length > 3" class="flex justify-center mt-6">
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <Button
                                variant="outline"
                                :disabled="!productos.prev_page_url"
                                @click="goToPage(productos.prev_page_url)"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                            >
                                Anterior
                            </Button>

                            <Button
                                v-for="link in productos.links.slice(1, -1)"
                                :key="link.label"
                                @click="goToPage(link.url)"
                                :variant="link.active ? 'default' : 'outline'"
                                :disabled="!link.url"
                                :class="{
                                    'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                                    'bg-white border-gray-300 text-gray-700 hover:bg-gray-50': !link.active,
                                    'hidden sm:inline-flex': link.label.includes('...') // Ocultar puntos suspensivos en móviles
                                }"
                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                v-html="link.label"
                            />

                            <Button
                                variant="outline"
                                :disabled="!productos.next_page_url"
                                @click="goToPage(productos.next_page_url)"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                            >
                                Siguiente
                            </Button>
                        </nav>
                    </div>

                    <div v-if="productos.total > 0" class="mt-4 text-center text-sm text-gray-600">
                        Mostrando {{ productos.from }} a {{ productos.to }} de {{ productos.total }} resultados.
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
