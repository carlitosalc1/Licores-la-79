<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2, CirclePlus } from 'lucide-vue-next';

import type { BreadcrumbItem } from '@/types';

// Define las props
const props = defineProps<{
    pagos: {
        data: Array<{
            id: number;
            monto: number;
            monto_recibido: number;
            cambio: number;
            metodo_pago: string;
            fecha_pago: string;
            referencia_pago: string | null;
            venta_id: number | null;
            compra_id: number | null;
            venta?: {
                id: number;
                total: number;
                cliente: {
                    id: number;
                    nombre: string;
                };
            };
            compra?: {
                id: number;
                total: number;
                proveedor: {
                    id: number;
                    razon_social: string;
                };
            };
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        from: number;
        last_page: number;
        per_page: number;
        to: number;
        total: number;
    };
}>();

const page = usePage();

// Eliminar pago
const deletePago = (id: number) => {
    if (!confirm('¿Estás seguro de eliminar este pago? Esta acción no se puede deshacer.')) return;
    router.delete(`/pagos/${id}`, {
        preserveScroll: true,
        onSuccess: () => router.visit('/pagos', { replace: true }),
        onError: (errors) => console.error(errors),
    });
};

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Pagos', href: '/pagos' }];
</script>

<template>
    <Head title="Pagos" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4 bg-gray-100 dark:bg-gray-900">
            <div class="flex justify-between items-center flex-wrap gap-2">
                <Button as-child size="sm" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-500 text-white font-medium shadow-lg">
                    <Link href="/pagos/create">
                        <CirclePlus class="mr-1" /> Nuevo pago
                    </Link>
                </Button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <Table class="min-w-[1200px] w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <TableCaption class="p-4 text-lg font-semibold text-left rtl:text-right text-gray-900 dark:text-white">
                        Lista de Pagos
                    </TableCaption>
                    <TableHeader class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-zinc-700 dark:text-gray-400">
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead>Tipo</TableHead>
                            <TableHead>Asociado (Cliente/Proveedor)</TableHead>
                            <TableHead>Monto a Pagar</TableHead>
                            <TableHead>Monto Recibido</TableHead>
                            <TableHead>Cambio</TableHead>
                            <TableHead>Método</TableHead>
                            <TableHead>Fecha</TableHead>
                            <TableHead>Referencia</TableHead>
                            <TableHead class="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="pago in pagos.data" :key="pago.id">
                            <TableCell class="font-medium">{{ pago.id }}</TableCell>
                            <TableCell>
                                <span v-if="pago.venta_id">Venta</span>
                                <span v-else-if="pago.compra_id">Compra</span>
                            </TableCell>
                            <TableCell>
                                <span v-if="pago.venta">{{ pago.venta.cliente.nombre }}</span>
                                <span v-else-if="pago.compra">{{ pago.compra.proveedor.razon_social }}</span>
                                <span v-else>N/A</span>
                            </TableCell>
                            <TableCell>
                                {{ new Intl.NumberFormat('es-CO', {
                                    style: 'currency',
                                    currency: 'COP',
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }).format(pago.monto) }}
                            </TableCell>
                            <TableCell>
                                {{ new Intl.NumberFormat('es-CO', {
                                    style: 'currency',
                                    currency: 'COP',
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }).format(pago.monto_recibido) }}
                            </TableCell>
                            <TableCell>
                                {{ new Intl.NumberFormat('es-CO', {
                                    style: 'currency',
                                    currency: 'COP',
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }).format(pago.cambio) }}
                            </TableCell>
                            <TableCell>{{ pago.metodo_pago }}</TableCell>
                            <TableCell>{{ new Date(pago.fecha_pago).toLocaleDateString() }}</TableCell>
                            <TableCell>{{ pago.referencia_pago || 'N/A' }}</TableCell>
                            <TableCell class="text-center space-x-2">
                                <Button as-child size="sm" class="bg-blue-500 hover:bg-blue-700 text-white">
                                    <Link :href="route('pagos.edit', pago.id)">
                                        <Pencil />
                                    </Link>
                                </Button>
                                <Button size="sm" class="bg-rose-500 hover:bg-rose-700 text-white" @click="deletePago(pago.id)">
                                    <Trash2 />
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="pagos.data.length === 0">
                            <TableCell colspan="10" class="text-center py-4 text-gray-500">
                                No hay pagos registrados.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Paginación -->
            <div class="flex justify-center mt-4 gap-2 flex-wrap">
                <template v-for="(link, i) in pagos.links" :key="i">
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
