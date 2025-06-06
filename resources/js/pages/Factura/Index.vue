<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell, TableCaption } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {  Trash2, CirclePlus, Pencil } from 'lucide-vue-next';

// Definición de tipos para las props
interface Factura {
    id: number;
    venta_id: number;
    numero_factura: string;
    fecha_emision: string;
    total: number;
    metodo_pago: 'efectivo' | 'tarjeta_credito' | 'tarjeta_debito';
    estado: 'pendiente' | 'pagada' | 'anulada';
    venta: { // Asumiendo que `venta` se carga con el user en el controlador
        id: number;
        user: { name: string };
    };
    detalle_facturas?: Array<{
        id: number;
        producto?: {
            nombre: string;
        }
    }>;
}

interface FacturaPageProps {

    [key: string]: any;

    facturas: {
        data: Factura[];
        meta: {
            current_page: number;
            last_page: number;
        };
        links: {
            url: string | null;
            label: string;
            active: boolean;
        }[];
    };
    filters: {
        search: string | null;
    };
}
const { props } = usePage<FacturaPageProps>();

const search = ref(props.filters.search || ''); // Inicializa con el valor del filtro o cadena vacía

// Usamos computed para filtrar en el frontend, como en el ejemplo de ventas
const facturasFiltradas = computed(() =>
    props.facturas.data.filter((factura) =>
        [
            factura.numero_factura,
            factura.venta_id,
            factura.venta?.user?.name,
            factura.fecha_emision,
            String(factura.total), // Convertir a string para búsqueda
            factura.metodo_pago,
            factura.estado,
        ]
        .some((value) =>
            String(value).toLowerCase().includes(search.value.toLowerCase())
        )
    )
);

// Definición de breadcrumbs, como en el ejemplo de ventas
const breadcrumbs = [{ title: 'Facturas', href: '/facturas' }];

const deleteFactura = (id: number) => {
    if (!confirm('¿Estás seguro de que quieres eliminar esta factura? Esta acción no se puede deshacer.')) return;
    router.delete(route('facturas.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            // Recargar la página o actualizar los datos si es necesario
            router.visit(route('facturas.index'), { replace: true });
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
};

// Función para obtener la variante del badge según el estado
const getEstadoVariant = (estado: string): "default" | "destructive" | "outline" | "secondary" | null | undefined => {
    switch (estado.toLowerCase()) {
        case 'pagada':
            return 'default';
        case 'pendiente':
            return 'outline';
        case 'anulada':
            return 'destructive';
        default:
            return 'default';
    }
};

</script>

<template>
    <Head title="Facturas" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4 bg-gray-100 dark:bg-gray-900">
            <div class="flex justify-between items-center flex-wrap gap-2">
                <Button as-child size="sm" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-500 text-white font-medium shadow-lg">
                    <Link :href="route('facturas.create')">
                        <CirclePlus class="mr-1" /> Nueva Factura
                    </Link>
                </Button>

                <input v-model="search" type="text" placeholder="Buscar factura..."
                    class="px-4 py-2 rounded border border-zinc-300 w-full sm:w-64 dark:bg-zinc-800 dark:text-white" />
            </div>

            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <Table class="min-w-[1200px] w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <TableCaption class="p-4 text-lg font-semibold text-left text-gray-900 dark:text-white">Lista de Facturas</TableCaption>
                    <TableHeader class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-zinc-700 dark:text-gray-400">
                        <TableRow>
                            <TableHead>Número Factura</TableHead>
                            <TableHead>Venta ID</TableHead>
                            <TableHead>Cliente</TableHead>
                            <TableHead>Cajero</TableHead>
                            <TableHead>Fecha Emisión</TableHead>
                            <TableHead>Total</TableHead>
                            <TableHead>Método Pago</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead class="text-center">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="factura in facturasFiltradas" :key="factura.id">
                            <TableCell>{{ factura.numero_factura }}</TableCell>
                            <TableCell>{{ factura.venta_id }}</TableCell>
                            <TableCell>{{ factura.venta?.user?.name || 'N/A' }}</TableCell>
                             <TableCell>{{ factura.fecha_emision }}</TableCell>
                            <TableCell>
                                {{ Number(factura.total).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }) }}
                            </TableCell>
                            <TableCell>{{ factura.metodo_pago }}</TableCell>
                            <TableCell>
                                <Badge :variant="getEstadoVariant(factura.estado)">
                                    {{ factura.estado }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-center space-x-2">
                                <Button as-child size="sm" class="bg-yellow-500 hover:bg-yellow-700 text-white">
                                    <Link :href="route('facturas.edit', factura.id)"> <Pencil /></Link>
                                </Button>
                                <Button size="sm" class="bg-rose-500 hover:bg-rose-700 text-white" @click="deleteFactura(factura.id)">
                                    <Trash2 />
                                </Button>
                                </TableCell>
                        </TableRow>
                        <TableRow v-if="facturasFiltradas.length === 0">
                            <TableCell colspan="9" class="text-center text-gray-500 py-4">No hay facturas para mostrar.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex justify-center mt-4 gap-2 flex-wrap">
                <template v-for="(link, i) in props.facturas.links" :key="i">
                    <button v-if="link.url"
                        :class="[ 'px-3 py-1 rounded border text-sm',
                            link.active
                                ? 'bg-cyan-700 text-white font-bold'
                                : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-zinc-800 dark:text-white dark:hover:bg-cyan-700'
                        ]"
                        @click="router.visit(link.url, { preserveScroll: true })"
                        v-html="link.label" />
                    <span v-else class="px-3 py-1 text-gray-400 text-sm" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
