<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData, type Compra } from '@/types';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2, CirclePlus } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface CompraPageProps extends SharedData {
  compras: {
    data: Compra[];
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

const { props } = usePage<CompraPageProps>();
const search = ref('');
const compras = computed(() => props.compras.data);

// Filtrado en frontend
const comprasFiltradas = computed(() =>
  compras.value.filter((compra) =>
    [compra.fecha, compra.total, compra.estado, compra.proveedor.razon_social, compra.user.name]
      .some((value) =>
        String(value).toLowerCase().includes(search.value.toLowerCase())
      )
  )
);

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Compras', href: '/compras' }];

// Eliminar compra
const deleteCompra = (id: number) => {
  if (!confirm('¿Estás seguro de eliminar esta compra?')) return;
  router.delete(`/compras/${id}`, {
    preserveScroll: true,
    onSuccess: () => router.visit('/compras', { replace: true }),
    onError: (errors) => console.error(errors),
  });
};
</script>

<template>
  <Head title="Compras" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4 bg-gray-100 dark:bg-gray-900">
      <!-- Botón Crear y Buscador -->
      <div class="flex justify-between items-center flex-wrap gap-2">
        <Button as-child size="sm" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-500 text-white font-medium shadow-lg">
          <Link href="/compras/create">
            <CirclePlus class="mr-1" /> Crear
          </Link>
        </Button>

        <input v-model="search" type="text" placeholder="Buscar compra..."
          class="px-4 py-2 rounded border border-zinc-300 w-full sm:w-64 dark:bg-zinc-800 dark:text-white" />
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <Table class="min-w-[900px] w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <TableCaption class="p-4 text-lg font-semibold text-left text-gray-900 dark:text-white">Lista de Compras</TableCaption>
          <TableHeader class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-zinc-700 dark:text-gray-400">
            <TableRow>
              <TableHead>Fecha</TableHead>
              <TableHead>Total</TableHead>
              <TableHead>Estado</TableHead>
              <TableHead>Proveedor</TableHead>
              <TableHead>Usuario</TableHead>
              <TableHead class="text-center">Acciones</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="compra in comprasFiltradas" :key="compra.id">
              <TableCell>{{ compra.fecha }}</TableCell>
          <TableCell> {{ Number(compra.total).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }) }}</TableCell>
              <TableCell>
                <span :class="compra.estado === 'pagada' ? 'text-green-600 font-semibold' : 'text-red-500 font-semibold'">
                  {{ compra.estado }}
                </span>
              </TableCell>
              <TableCell>{{ compra.proveedor.razon_social }}</TableCell>
              <TableCell>{{ compra.user.name }}</TableCell>
              <TableCell class="text-center space-x-2">
                <Button as-child size="sm" class="bg-blue-500 hover:bg-blue-700 text-white">
                  <Link :href="`/compras/${compra.id}/edit`">
                    <Pencil />
                  </Link>
                </Button>
                <Button size="sm" class="bg-rose-500 hover:bg-rose-700 text-white" @click="deleteCompra(compra.id)">
                  <Trash2 />
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Paginación -->
      <div class="flex justify-center mt-4 gap-2 flex-wrap">
        <template v-for="(link, i) in props.compras.links" :key="i">
          <button v-if="link.url"
            :class="[
              'px-3 py-1 rounded border text-sm',
              link.active
                ? 'bg-cyan-700 text-white font-bold'
                : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-zinc-800 dark:text-white dark:hover:bg-cyan-700' ]"
            @click="router.visit(link.url, { preserveScroll: true })"
            v-html="link.label" />
          <span v-else class="px-3 py-1 text-gray-400 text-sm" v-html="link.label" />
        </template>
      </div>
    </div>
  </AppLayout>
</template>
