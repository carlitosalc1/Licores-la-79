<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2, CirclePlus } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface CategoriaProducto {
  id: number;
  nombre: string;
  descripcion: string | null;
}

interface CategoriaProductoPageProps extends SharedData {
  categoriadeproductos: {
    data: CategoriaProducto[];
    meta: any;
    links: any;
  };
}

const { props } = usePage<CategoriaProductoPageProps>();
const search = ref('');

// Datos base (paginados)
const categoriadeproductos = computed(() => props.categoriadeproductos.data);

// Filtro local
const categoriadeproductosFiltrados = computed(() =>
  categoriadeproductos.value.filter((categoria) =>
    Object.values(categoria).some((value) =>
      String(value).toLowerCase().includes(search.value.toLowerCase())
    )
  )
);

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Categorías de Producto', href: '/categoria_productos' }];

const deleteCategoriadeproducto = async (id: number) => {
  if (!window.confirm('¿Seguro que quieres eliminar esta categoría?')) return;
  router.delete(`/categoria_productos/${id}`, {
    preserveScroll: true,
    onSuccess: () => {
      router.visit('/categoria_productos', { replace: true });
    },
    onError: (errors) => {
      console.error('Error eliminando categoría:', errors);
    }
  });
};
</script>

<template>
  <Head title="Categorías de Producto" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4 bg-gray-100 dark:bg-gray-900">
      <!-- Botón Crear y Buscador -->
      <div class="flex justify-between items-center flex-wrap gap-2">
        <Button as-child size="sm" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all">
          <Link href="/categoria_productos/create">
            <CirclePlus class="mr-1" /> Crear
          </Link>
        </Button>

        <input
          v-model="search"
          type="text"
          placeholder="Buscar Categoría..."
          class="px-4 py-2 rounded border border-zinc-300 w-full sm:w-64 dark:bg-zinc-800 dark:text-white"
        />
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <Table class="min-w-[900px] w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <TableCaption class="p-4 text-lg font-semibold text-left rtl:text-right text-gray-900 dark:text-white">Lista de Categorías de Producto</TableCaption>
          <TableHeader class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-zinc-700 dark:text-gray-400">
            <TableRow>
              <TableHead>Nombre</TableHead>
              <TableHead>Descripción</TableHead>
              <TableHead class="text-center">Acciones</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="categoria in categoriadeproductosFiltrados" :key="categoria.id">
              <TableCell class="font-medium">{{ categoria.nombre }}</TableCell>
              <TableCell>{{ categoria.descripcion || 'Sin descripción' }}</TableCell>
              <TableCell class="text-center space-x-2">
                <Button as-child size="sm" class="bg-blue-500 hover:bg-blue-700 text-white">
                  <Link :href="`/categoria_productos/${categoria.id}/edit`">
                    <Pencil />
                  </Link>
                </Button>
                <Button size="sm" class="bg-rose-500 hover:bg-rose-700 text-white" @click="deleteCategoriadeproducto(categoria.id)">
                  <Trash2 />
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Paginación -->
      <div class="flex justify-center mt-4 gap-2 flex-wrap">
        <template v-for="(link, i) in props.categoriadeproductos.links" :key="i">
          <button
            v-if="link.url"
            :class="[
              'px-3 py-1 rounded border text-sm',
              link.active
                ? 'bg-cyan-700 text-white font-bold'
                : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-zinc-800 dark:text-white dark:hover:bg-cyan-700'
            ]"
            @click="router.visit(link.url, { preserveScroll: true })"
            v-html="link.label"
          />
          <span v-else class="px-3 py-1 text-gray-400 text-sm" v-html="link.label" />
        </template>
      </div>
    </div>
  </AppLayout>
</template>
