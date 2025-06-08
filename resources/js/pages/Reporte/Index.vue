<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { CirclePlus, Pencil, Trash2 } from 'lucide-vue-next';

// --- Interfaces para el tipado de props ---
interface ReporteItem {
    id: number;
    tipo: 'ventas' | 'compras' | 'pedidos' | 'inventario';
    fecha_inicio: string | null;
    fecha_fin: string | null;
    estado: 'generado' | 'archivado';
    descripcion: string | null;
    filtros: Record<string, any> | null; // Assuming filters is a JSON object
    user_id: number;
    created_at: string;
    updated_at: string;
}

interface ReportesProps {
    reportes: {
        data: ReporteItem[];
        links: {
            url: string | null;
            label: string;
            active: boolean;
        }[];
        current_page: number;
        from: number;
        last_page: number;
        per_page: number;
        to: number;
        total: number;
    };
    filters: {
        search?: string;
        sort_column?: string;
        sort_direction?: 'asc' | 'desc';
    };
    [key: string]: any; // Agrega la firma de índice para permitir otras propiedades
}

// Obtener props usando usePage para consistencia con el archivo de ejemplo
const { props } = usePage<ReportesProps>();

// --- Estado reactivo para filtros y ordenamiento ---
const search = ref(props.filters.search || '');
const sortColumn = ref(props.filters.sort_column || 'created_at');
const sortDirection = ref(props.filters.sort_direction || 'desc');

// --- Breadcrumbs para AppLayout ---
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reportes', href: route('reportes.index') },
];

// --- Observar cambios en los filtros y realizar la petición a Inertia ---
watch([search, sortColumn, sortDirection], ([newSearch, newSortColumn, newSortDirection]) => {
    router.get(
        route('reportes.index'),
        {
            search: newSearch,
            sort_column: newSortColumn,
            sort_direction: newSortDirection,
        },
        { preserveState: true, replace: true }
    );
});

// --- Funciones de acción ---
const deleteReporte = (id: number) => {
    if (confirm('¿Estás seguro de que quieres eliminar este reporte? Esta acción no se puede deshacer.')) {
        router.delete(route('reportes.destroy', id), {
            onSuccess: () => {
                router.visit(route('reportes.index'), { replace: true });
            },
            onError: (errors) => {
                console.error('Error al eliminar el reporte:', errors);
                alert('Hubo un error al eliminar el reporte.');
            },
        });
    }
};

const changeSort = (column: string) => {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
};
</script>

<template>
    <Head title="Reportes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4 bg-gray-100 dark:bg-gray-900">
            <div class="flex justify-between items-center flex-wrap gap-2">
                <Button as-child size="sm"
                    class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-500 text-white font-medium shadow-lg">
                    <Link :href="route('reportes.create')">
                        <CirclePlus class="mr-1" /> Crear Reporte
                    </Link>
                </Button>

                <input
                    v-model="search"
                    type="text"
                    placeholder="Buscar por tipo, descripción..."
                    class="px-4 py-2 rounded border border-zinc-300 w-full sm:w-64 dark:bg-zinc-800 dark:text-white"
                />
            </div>

            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <Table class="min-w-[1000px] w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <TableCaption class="p-4 text-lg font-semibold text-left text-gray-900 dark:text-white">
                        Lista de Reportes Generados
                    </TableCaption>
                    <TableHeader class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-zinc-700 dark:text-gray-400">
                        <TableRow>
                            <TableHead @click="changeSort('id')" class="cursor-pointer">
                                ID
                                <span v-if="sortColumn === 'id'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            </TableHead>
                            <TableHead @click="changeSort('tipo')" class="cursor-pointer">
                                Tipo
                                <span v-if="sortColumn === 'tipo'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            </TableHead>
                            <TableHead>Fecha Inicio</TableHead>
                            <TableHead>Fecha Fin</TableHead>
                            <TableHead>Descripción</TableHead>
                            <TableHead @click="changeSort('estado')" class="cursor-pointer">
                                Estado
                                <span v-if="sortColumn === 'estado'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            </TableHead>
                            <TableHead class="text-center">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="props.reportes.data.length === 0">
                            <TableCell colspan="7" class="text-center py-4 text-gray-500">
                                No se encontraron reportes que coincidan con la búsqueda.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="reporte in props.reportes.data" :key="reporte.id">
                            <TableCell class="font-medium">{{ reporte.id }}</TableCell>
                            <TableCell>{{ reporte.tipo }}</TableCell>
                            <TableCell>{{ reporte.fecha_inicio || 'N/A' }}</TableCell>
                            <TableCell>{{ reporte.fecha_fin || 'N/A' }}</TableCell>
                            <TableCell>{{ reporte.descripcion || 'Sin descripción' }}</TableCell>
                            <TableCell>
                                <span :class="{
                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                    'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100': reporte.estado === 'generado',
                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100': reporte.estado === 'archivado',
                                }">
                                    {{ reporte.estado }}
                                </span>
                            </TableCell>
                            <TableCell class="text-center space-x-2">
                                <Button as-child size="sm" class="bg-blue-500 hover:bg-blue-700 text-white">
                                    <Link :href="route('reportes.edit', reporte.id)">
                                        <Pencil class="w-4 h-4" />
                                    </Link>
                                </Button>
                                <Button size="sm" class="bg-rose-500 hover:bg-rose-700 text-white"
                                    @click="deleteReporte(reporte.id)">
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex justify-center mt-4 gap-2 flex-wrap">
                <template v-for="(link, i) in props.reportes.links" :key="i">
                    <button v-if="link.url" :class="[
                        'px-3 py-1 rounded border text-sm',
                        link.active
                            ? 'bg-cyan-700 text-white font-bold'
                            : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-zinc-800 dark:text-white dark:hover:bg-cyan-700'
                    ]" @click="router.visit(link.url, { preserveScroll: true })" v-html="link.label" />
                    <span v-else class="px-3 py-1 text-gray-400 text-sm" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
