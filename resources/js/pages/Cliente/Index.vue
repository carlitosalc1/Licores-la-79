<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow,} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2, CirclePlus } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Cliente } from '@/types';

interface ClientePageProps extends SharedData {
  clientes: {
    data: Cliente[];
    meta: any;
    links: any;
  };
}
const { props } = usePage<ClientePageProps>();
const search = ref('');

// Filtro de clientes por cualquier campo visible
const clientes = computed(() => props.clientes.data);

const clientesFiltrados = computed(() =>
  clientes.value.filter((cliente) =>
    Object.values(cliente).some((value) =>
      String(value).toLowerCase().includes(search.value.toLowerCase())
    )
  )
);


// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Clientes', href: '/cliente' }];

// Método para eliminar cliente
const deleteCliente = async (id: number) => {
  if (!window.confirm('¿Seguro que quieres eliminar a este Cliente?')) return;
  router.delete(`/clientes/${id}`, {
    preserveScroll: true,
    onSuccess: () => {
      router.visit('/clientes', { replace: true });
    },
    onError: (errors) => {
      console.error('Error deleting cliente:', errors);
    },
  });
};
</script>

<template>
  <Head title="Clientes" />
    <AppLayout :breadcrumbs="breadcrumbs">
      <div class="flex flex-col gap-4 p-4 bg-gray-100 dark:bg-gray-900">
        <div class="flex justify-between items-center flex-wrap gap-2">
          <Button as-child size="sm" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all">
            <Link href="/clientes/create"> <CirclePlus class="mr-1" />Crear</Link>
          </Button>
          <input v-model="search" type="text" placeholder="Buscar Clientes..."
            class="px-4 py-2 rounded border border-zinc-300 w-full sm:w-64 dark:bg-zinc-800 dark:text-white"/>
        </div>

      <!-- Tabla de Clientes -->
      <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <Table class="min-w-[900px] w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <TableCaption class="p-4 text-lg font-semibold text-left rtl:text-right text-gray-900 dark:text-white">Lista de Clientes</TableCaption>
          <TableHeader class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-zinc-700 dark:text-gray-400">
            <TableRow class="bg-gray-200 dark:bg-zinc-700">
              <TableHead>Tipo de identificación</TableHead>
              <TableHead>Número de identificación</TableHead>
              <TableHead>Nombre</TableHead>
              <TableHead>Apellido</TableHead>
              <TableHead>Dirección</TableHead>
              <TableHead>Teléfono</TableHead>
              <TableHead>Email</TableHead>
              <TableHead class="text-center">Acciones</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="cliente in clientesFiltrados" :key="cliente.id">
              <TableCell class="font-medium">{{ cliente.tipo_identificacion }}</TableCell>
              <TableCell>{{ cliente.numero_identificacion }}</TableCell>
              <TableCell>{{ cliente.nombre }}</TableCell>
              <TableCell>{{ cliente.apellido }}</TableCell>
              <TableCell>{{ cliente.direccion }}</TableCell>
              <TableCell>{{ cliente.telefono }}</TableCell>
              <TableCell>{{ cliente.correo }}</TableCell>
              <TableCell class="text-center space-x-2">
                <Button as-child size="sm" class="bg-blue-500 hover:bg-blue-700 text-white">
                  <Link :href="`/clientes/${cliente.id}/edit`">
                    <Pencil />
                  </Link>
                </Button>
                <Button size="sm" class="bg-rose-500 hover:bg-rose-700 text-white" @click="deleteCliente(cliente.id)">
                  <Trash2 />
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Paginación -->
      <div class="flex justify-center mt-4 gap-2 flex-wrap">
        <template v-for="(link, i) in props.clientes.links" :key="i">
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
