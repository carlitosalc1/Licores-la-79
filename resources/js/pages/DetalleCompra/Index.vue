<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2, CirclePlus } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface DetalleCompra {
  id: number;
  compra: { id: number };
  producto: { nombre: string };
  cantidad: number;
  precio_unitario: number;
  subtotal: number;
}

interface PageProps extends SharedData {
  detalles: {
    data: DetalleCompra[];
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
}

const { props } = usePage<PageProps>();
const search = ref('');
const detalles = computed(() => props.detalles.data);

// Función para convertir valores numéricos a texto con separadores para búsqueda
const formatNumber = (value: number) => {
  const numeric = Number(value);
  if (isNaN(numeric)) return '$ 0';
  return numeric.toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  });
};


const detallesFiltrados = computed(() =>
  detalles.value.filter((detalle) =>
    [
      detalle.producto.nombre,
      `Compra #${detalle.compra.id}`,
      detalle.cantidad.toString(),
      formatNumber(detalle.precio_unitario),
      formatNumber(detalle.subtotal)
    ].some((value) =>
      value.toLowerCase().includes(search.value.toLowerCase())
    )
  )
);

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Detalle Compras', href: '/detalle_compras' }];

const deleteDetalle = (id: number) => {
  if (!confirm('¿Estás seguro de eliminar este detalle de compra?')) return;
  router.delete(`/detalle_compras/${id}`, {
    preserveScroll: true,
    onSuccess: () => router.visit('/detalle_compras', { replace: true }),
    onError: (errors) => console.error(errors),
  });
};
</script>

<template>
  <Head title="Detalle Compras" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4 bg-gray-100 dark:bg-gray-900">
      <div class="flex justify-between items-center flex-wrap gap-2">
        <Button as-child size="sm" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-500 text-white font-medium shadow-lg">
          <Link href="/detalle_compras/create">
            <CirclePlus class="mr-1" /> Crear
          </Link>
        </Button>

        <input v-model="search" type="text" placeholder="Buscar detalle..."
          class="px-4 py-2 rounded border border-zinc-300 w-full sm:w-64 dark:bg-zinc-800 dark:text-white" />
      </div>

      <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <Table class="min-w-[900px] w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <TableCaption class="p-4 text-lg font-semibold text-left text-gray-900 dark:text-white">Lista de Detalles de Compra</TableCaption>
          <TableHeader class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-zinc-700 dark:text-gray-400">
            <TableRow>
              <TableHead>Producto</TableHead>
              <TableHead>Compra</TableHead>
              <TableHead>Cantidad</TableHead>
              <TableHead>Precio Unitario</TableHead>
              <TableHead>Subtotal</TableHead>
              <TableHead class="text-center">Acciones</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="detalle in detallesFiltrados" :key="detalle.id">
              <TableCell class="font-medium">{{ detalle.producto.nombre }}</TableCell>
              <TableCell>Compra #{{ detalle.compra.id }}</TableCell>
              <TableCell>{{ detalle.cantidad }}</TableCell>
              <TableCell>
                {{ formatNumber(detalle.precio_unitario) }}
              </TableCell>
              <TableCell>
                {{ formatNumber(detalle.subtotal) }}
              </TableCell>
              <TableCell class="text-center space-x-2">
                <Button as-child size="sm" class="bg-blue-500 hover:bg-blue-700 text-white">
                  <Link :href="`/detalle_compras/${detalle.id}/edit`">
                    <Pencil />
                  </Link>
                </Button>
                <Button size="sm" class="bg-rose-500 hover:bg-rose-700 text-white" @click="deleteDetalle(detalle.id)">
                  <Trash2 />
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div class="flex justify-center mt-4 gap-2 flex-wrap">
        <template v-for="(link, i) in props.detalles.links" :key="i">
          <button v-if="link.url"
            :class="['px-3 py-1 rounded border text-sm',
              link.active
                ? 'bg-cyan-700 text-white font-bold'
                : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700']"
            @click="router.visit(link.url, { preserveScroll: true })"
            v-html="link.label" />
          <span v-else class="px-3 py-1 text-gray-400 text-sm" v-html="link.label" />
        </template>
      </div>
    </div>
  </AppLayout>
</template>
