<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2, CirclePlus } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Producto {
  id: number;
  nombre: string;
  descripcion: string;
  precio_compra: number;
  precio_venta: number;
  unidad_medida: string;
  stock: number;
  stock_minimo: number;
  categoria_producto_id: number;
  categoria_producto: {
    nombre: string;
  };
}

interface ProductoPageProps extends SharedData {
  productos: {
    data: Producto[];
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

const search = ref('');
const { props } = usePage<ProductoPageProps>();

// Filtro de productos
const productos = computed(() => props.productos.data);

const productosFiltrados = computed(() =>
  productos.value.filter((producto) =>
    Object.values(producto).some((value) => {
      if (typeof value === 'object' && value !== null) {
        return Object.values(value).some(
          (subValue) => String(subValue).toLowerCase().includes(search.value.toLowerCase())
        );
      }
      return String(value).toLowerCase().includes(search.value.toLowerCase());
    })
  )
);

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Productos', href: '/productos' }];

// Eliminar producto
const deleteProducto = async (id: number) => {
  if (!window.confirm('¿Seguro que quieres eliminar este producto?')) return;
  router.delete(`/productos/${id}`, {
    preserveScroll: true,
    onSuccess: () => router.visit('/productos', { replace: true }),
    onError: (errors) => console.error('Error eliminando producto:', errors),
  });
};
</script>

<template>
  <Head title="Productos" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4 bg-gray-100 dark:bg-gray-900">
      <!-- Crear y buscador -->
      <div class="flex justify-between items-center flex-wrap gap-2">
        <Button as-child size="sm" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-500 text-white font-medium shadow-lg">
          <Link href="/productos/create">
            <CirclePlus class="mr-1" /> Crear
          </Link>
        </Button>

        <input v-model="search" type="text" placeholder="Buscar Producto..."
          class="px-4 py-2 rounded border border-zinc-300 w-full sm:w-64 dark:bg-zinc-800 dark:text-white" />
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <Table class="min-w-[900px] w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <TableCaption class="p-4 text-lg font-semibold text-left rtl:text-right text-gray-900 dark:text-white">Lista de Productos</TableCaption>
          <TableHeader class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-zinc-700 dark:text-gray-400">
            <TableRow>
              <TableHead>Nombre</TableHead>
              <TableHead>Descripción</TableHead>
              <TableHead>Precio Compra</TableHead>
              <TableHead>Precio Venta</TableHead>
              <TableHead>Unidad Medida</TableHead>
              <TableHead>Stock</TableHead>
              <TableHead>Stock Mínimo</TableHead>
              <TableHead>Categoría</TableHead>
              <TableHead class="text-center">Acciones</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="producto in productosFiltrados" :key="producto.id">
              <TableCell>{{ producto.nombre }}</TableCell>
              <TableCell>{{ producto.descripcion }}</TableCell>
              <TableCell> {{ Number( producto.precio_compra).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }) }}</TableCell>
              <TableCell> {{ Number( producto.precio_venta).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }) }}</TableCell>
              <TableCell>{{ producto.unidad_medida }}</TableCell>
              <TableCell>{{ producto.stock }}</TableCell>
              <TableCell>{{ producto.stock_minimo }}</TableCell>
              <TableCell>{{ producto.categoria_producto.nombre }}</TableCell>
              <TableCell class="text-center space-x-2">
                <Button as-child size="sm" class="bg-blue-500 hover:bg-blue-700 text-white">
                  <Link :href="`/productos/${producto.id}/edit`">
                    <Pencil />
                  </Link>
                </Button>
                <Button size="sm" class="bg-rose-500 hover:bg-rose-700 text-white" @click="deleteProducto(producto.id)">
                  <Trash2 />
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Paginación -->
      <div class="flex justify-center mt-4 gap-2 flex-wrap">
        <template v-for="(link, i) in props.productos.links" :key="i">
          <button
            v-if="link.url"
            :class="[
              'px-3 py-1 rounded border text-sm',
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
