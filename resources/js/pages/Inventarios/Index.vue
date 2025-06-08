<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { CirclePlus, Pencil, Trash2 } from 'lucide-vue-next';

interface InventarioItem {
    id: number;
    producto: {
        id: number;
        nombre: string;
    } | null;
    tipo_movimiento: string;
    cantidad_entrada: number;
    cantidad_salida: number;
    fecha_actualizacion: string;
    created_at: string;
    updated_at: string;
    compra_id?: number | null; // Added to link to purchase
    venta_id?: number | null; // Added to link to sale
}

interface ProductoStock {
    id: number;
    nombre: string;
    stock: number;
    stock_minimo: number;
}

interface InventariosProps {
    inventarios: {
        data: InventarioItem[];
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
    productos: ProductoStock[]; // Add products for the new table
    filters: {
        search?: string;
    };
    // Agrega la firma de índice aquí
    [key: string]: any;
}

const { props } = usePage<InventariosProps>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Inventario', href: '/inventarios' }];

const search = ref(props.filters.search || '');

// Observa cambios en el campo de búsqueda y realiza la petición a Inertia
watch(search, (value) => {
    router.get(
        route('inventarios.index'),
        { search: value },
        { preserveState: true, replace: true }
    );
});

// Computed property for filtering inventarios based on search input
const filteredInventarios = computed(() =>
    props.inventarios.data.filter((item) =>
        [
            item.id,
            item.producto?.nombre,
            item.tipo_movimiento,
            item.fecha_actualizacion,
            item.compra_id, // Include in search
            item.venta_id, // Include in search
        ].some((value) =>
            String(value).toLowerCase().includes(search.value.toLowerCase())
        )
    )
);

// Function to delete an inventory movement
const deleteInventario = (id: number) => {
    if (confirm('¿Estás seguro de que quieres eliminar este movimiento de inventario? Esta acción no se puede deshacer.')) {
        router.delete(route('inventarios.destroy', id), {
            onSuccess: () => {
                // Optionally, refresh the page or remove the item from the local list
                router.visit('/inventarios', { replace: true });
            },
            onError: (errors) => {
                console.error('Error al eliminar:', errors);
                alert('Hubo un error al eliminar el movimiento de inventario.');
            },
        });
    }
};

</script>

<template>
    <Head title="Inventario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4 bg-gray-100 dark:bg-gray-900">
            <div class="flex justify-between items-center flex-wrap gap-2">
                <Button as-child size="sm"
                    class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-500 text-white font-medium shadow-lg">
                    <Link :href="route('inventarios.create')">
                    <CirclePlus class="mr-1" /> Registrar Nuevo Movimiento
                    </Link>
                </Button>

                <input v-model="search" type="text" placeholder="Buscar por ID, producto o tipo de movimiento..."
                    class="px-4 py-2 rounded border border-zinc-300 w-full sm:w-64 dark:bg-zinc-800 dark:text-white" />
            </div>

            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <Table class="min-w-[1200px] w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <TableCaption class="p-4 text-lg font-semibold text-left text-gray-900 dark:text-white">
                        Lista de Movimientos de Inventario
                    </TableCaption>
                    <TableHeader class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-zinc-700 dark:text-gray-400">
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead>Producto</TableHead>
                            <TableHead>Tipo de Movimiento</TableHead>
                            <TableHead>Cantidad Entrada</TableHead>
                            <TableHead>Cantidad Salida</TableHead>
                            <TableHead>Fecha Actualización</TableHead>
                            <TableHead>ID Compra</TableHead>
                            <TableHead>ID Venta</TableHead>
                            <TableHead class="text-center">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="filteredInventarios.length === 0">
                            <TableCell colspan="9" class="text-center py-4 text-gray-500">
                                No se encontraron movimientos de inventario que coincidan con la búsqueda.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="inventario in filteredInventarios" :key="inventario.id">
                            <TableCell class="font-medium">{{ inventario.id }}</TableCell>
                            <TableCell>{{ inventario.producto ? inventario.producto.nombre : 'N/A' }}</TableCell>
                            <TableCell>{{ inventario.tipo_movimiento }}</TableCell>
                            <TableCell>{{ inventario.cantidad_entrada }}</TableCell>
                            <TableCell>{{ inventario.cantidad_salida }}</TableCell>
                            <TableCell>{{ inventario.fecha_actualizacion }}</TableCell>
                            <TableCell>{{ inventario.compra_id || 'N/A' }}</TableCell>
                            <TableCell>{{ inventario.venta_id || 'N/A' }}</TableCell>
                            <TableCell class="text-center space-x-2">
                                <Button as-child size="sm" class="bg-blue-500 hover:bg-blue-700 text-white">
                                    <Link :href="route('inventarios.edit', inventario.id)">
                                    <Pencil />
                                    </Link>
                                </Button>
                                <Button size="sm" class="bg-rose-500 hover:bg-rose-700 text-white"
                                    @click="deleteInventario(inventario.id)">
                                    <Trash2 />
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border mt-8">
                <Table class="min-w-[800px] w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <TableCaption class="p-4 text-lg font-semibold text-left text-gray-900 dark:text-white">
                        Stock Actual de Productos
                    </TableCaption>
                    <TableHeader class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-zinc-700 dark:text-gray-400">
                        <TableRow>
                            <TableHead>Producto</TableHead>
                            <TableHead>Stock Actual</TableHead>
                            <TableHead>Stock Mínimo</TableHead>
                            <TableHead>Estado</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="props.productos.length === 0">
                            <TableCell colspan="4" class="text-center py-4 text-gray-500">
                                No hay productos para mostrar el stock.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="producto in props.productos" :key="producto.id">
                            <TableCell class="font-medium">{{ producto.nombre }}</TableCell>
                            <TableCell>{{ producto.stock }}</TableCell>
                            <TableCell>{{ producto.stock_minimo }}</TableCell>
                            <TableCell>
                                <span :class="{
                                    'text-red-500 font-semibold': producto.stock < producto.stock_minimo,
                                    'text-green-500 font-semibold': producto.stock >= producto.stock_minimo
                                }">
                                    {{ producto.stock < producto.stock_minimo ? 'Bajo' : 'Suficiente' }}
                                </span>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex justify-center mt-4 gap-2 flex-wrap">
                <template v-for="(link, i) in props.inventarios.links" :key="i">
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
