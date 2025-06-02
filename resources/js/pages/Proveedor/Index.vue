<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2, CirclePlus } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Proveedor } from '@/types';

interface ProveedorPageProps extends SharedData {
  proveedors: {
    data: Proveedor[];
    meta: any;
    links: any;
  };
}
const { props } = usePage<ProveedorPageProps>();
const search = ref('');

// Filtro de proveedor por cualquier campo visible
const proveedors = computed(() => props.proveedors.data);

const proveedorsFiltrados = computed(() =>
  proveedors.value.filter((proveedor) =>
    Object.values(proveedor).some((value) =>
      String(value).toLowerCase().includes(search.value.toLowerCase())
    )
  )
);

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Proveedores', href: '/proveedor' }];

// Método para eliminar proveedor
const deleteProveedor = async (id: number) => {
  if (!window.confirm('¿Seguro que quieres eliminar a este Proveedor?')) return;
  router.delete(`/proveedors/${id}`, {
    preserveScroll: true,
    onSuccess: () => {
      router.visit('/proveedors', { replace: true });
    },
    onError: (errors) => {
      console.error('Error deleting proveedor:', errors);
    },
  });
};
</script>

<template>
    <Head title="Proveedores" />
    <AppLayout :breadcrumbs="breadcrumbs">
      <div class="flex flex-col gap-4 p-4 bg-gray-100 dark:bg-gray-900">
        <div class="flex justify-between items-center flex-wrap gap-2">
          <Button as-child size="sm" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all">
            <Link href="/proveedors/create"> <CirclePlus class="mr-1" />Crear</Link>
          </Button>
          <input v-model="search" type="text" placeholder="Buscar Proveedor..."
            class="px-4 py-2 rounded border border-zinc-300 w-full sm:w-64 dark:bg-zinc-800 dark:text-white"/>
        </div>

        <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
              <Table class="min-w-[900px] w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
              <TableCaption class="p-4 text-lg font-semibold text-left rtl:text-right text-gray-900 dark:text-white">Lista de Proveedores</TableCaption>
            <TableHeader class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-zinc-700 dark:text-gray-400">
              <TableRow class="bg-gray-200 dark:bg-zinc-700">
                <TableHead>Razón social</TableHead>
                <TableHead>Nit</TableHead>
                <TableHead>Dirección</TableHead>
                <TableHead>Teléfono</TableHead>
                <TableHead>Email</TableHead>
                <TableHead class="text-center">Acciones</TableHead>
              </TableRow>
            </TableHeader>

            <TableBody>
              <TableRow v-for="proveedor in proveedorsFiltrados" :key="proveedor.id">
                <TableCell class="font-medium">{{ proveedor.razon_social }}</TableCell>
                <TableCell>{{ proveedor.nit }}</TableCell>
                <TableCell>{{ proveedor.direccion }}</TableCell>
                <TableCell>{{ proveedor.telefono }}</TableCell>
                <TableCell>{{ proveedor.correo }}</TableCell>
                <TableCell class="text-center space-x-2">
                  <Button as-child size="sm" class="bg-blue-500 hover:bg-blue-700 text-white">
                  <Link :href="`/proveedors/${proveedor.id}/edit`">
                    <Pencil />
                  </Link>
                </Button>
                <Button size="sm" class="bg-rose-500 hover:bg-rose-700 text-white" @click="deleteProveedor(proveedor.id)">
                  <Trash2 />
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      <!-- Paginación -->
      <div class="flex justify-center mt-4 gap-2 flex-wrap">
        <template v-for="(link, i) in props.proveedors.links" :key="i">
          <button
            v-if="link.url"
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
