<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2, CirclePlus } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';


interface Venta {
    id: number;
    // Agrega cualquier otro campo de Venta que necesites mostrar o buscar
}

interface Producto {
    id: number;
    nombre: string;
    // Agrega cualquier otro campo de Producto que necesites mostrar o buscar
}

interface DetalleVenta {
    id: number;
    venta_id: number;
    producto_id: number;
    cantidad: number;
    precio_unitario: number;
    subtotal: number;
    impuesto_iva: number;
    venta: Venta;
    producto: Producto;
}

interface DetalleVentaPageProps extends SharedData {
    detallesVenta: {
        data: DetalleVenta[];
        links: {
            url: string | null;
            label: string;
            active: boolean;
        }[];
        current_page: number;
        last_page: number;
    };
    filters: {
        search: string;
    };
}

const { props } = usePage<DetalleVentaPageProps>();
const search = ref(props.filters.search || '');

// Computed property para obtener los detalles de venta
const detallesVenta = computed(() => props.detallesVenta.data);

// Filtrado en frontend para el buscador
const detallesVentaFiltrados = computed(() =>
    detallesVenta.value.filter((detalle) =>
        [
            detalle.cantidad,
            detalle.precio_unitario,
            detalle.subtotal,
            detalle.impuesto_iva,
            detalle.venta.id,
            detalle.producto.nombre
        ].some((value) =>
            String(value).toLowerCase().includes(search.value.toLowerCase())
        )
    )
);

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Detalles de Venta', href: '/detalle_ventas' }];

// Eliminar DetalleVenta

const deleteDetalleVenta = (id: number) => {
    if (!confirm('¿Estás seguro de eliminar este detalle de venta?')) return;
    router.delete(route('detalle_ventas.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            router.get(route('detalle_ventas.index'), {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        },
        onError: (errors) => console.error(errors),
    });
};

watch(search, (value) => {
    router.get(route('detalle_ventas.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
});
</script>

<template>
    <Head title="Detalles de Venta" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4 bg-gray-100 dark:bg-gray-900">
            <div class="flex justify-between items-center flex-wrap gap-2">
                <Button as-child size="sm" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-500 text-white font-medium shadow-lg">
                    <Link :href="route('detalle_ventas.create')">
                        <CirclePlus class="mr-1" /> Añadir Detalle
                    </Link>
                </Button>

                <input v-model="search" type="text" placeholder="Buscar detalle de venta..."
                    class="px-4 py-2 rounded border border-zinc-300 w-full sm:w-64 dark:bg-zinc-800 dark:text-white" />
            </div>
            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <Table class="min-w-[900px] w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <TableCaption class="p-4 text-lg font-semibold text-left text-gray-900 dark:text-white">Lista de Detalles de Venta</TableCaption>
                    <TableHeader class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-zinc-700 dark:text-gray-400">
                        <TableRow>
                            <TableHead>ID Venta</TableHead>
                            <TableHead>Producto</TableHead>
                            <TableHead>Cantidad</TableHead>
                            <TableHead>Precio Unitario</TableHead>
                            <TableHead>Subtotal</TableHead>
                            <TableHead>IVA</TableHead>
                            <TableHead class="text-center">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="detallesVentaFiltrados.length === 0">
                            <TableCell colspan="7" class="text-center py-4 text-gray-600 dark:text-gray-300">
                                No se encontraron detalles de venta.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="detalle in detallesVentaFiltrados" :key="detalle.id">
                            <TableCell>{{ detalle.venta_id }}</TableCell>
                            <TableCell>{{ detalle.producto.nombre }}</TableCell>
                            <TableCell>{{ detalle.cantidad }}</TableCell>
                            <TableCell> {{ Number(detalle.precio_unitario).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }) }}</TableCell>
                            <TableCell> {{ Number(detalle.subtotal).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }) }}</TableCell>
                            <TableCell> {{ Number(detalle.impuesto_iva).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }) }}</TableCell>
                            <TableCell class="text-center space-x-2">
                                <Button as-child size="sm" class="bg-blue-500 hover:bg-blue-700 text-white">
                                    <Link :href="route('detalle_ventas.edit', detalle.id)">
                                        <Pencil />
                                    </Link>
                                </Button>
                                <Button size="sm" class="bg-rose-500 hover:bg-rose-700 text-white" @click="deleteDetalleVenta(detalle.id)">
                                    <Trash2 />
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex justify-center mt-4 gap-2 flex-wrap">
                <template v-for="(link, i) in props.detallesVenta.links" :key="i">
                    <button v-if="link.url"
                        :class="[
                            'px-3 py-1 rounded border text-sm',
                            link.active
                                ? 'bg-cyan-700 text-white font-bold'
                                : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-zinc-800 dark:text-white dark:hover:bg-cyan-700'
                        ]"
                        @click="router.visit(link.url, { preserveScroll: true, data: { search: search } })"
                        v-html="link.label" />
                    <span v-else class="px-3 py-1 text-gray-400 text-sm" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
