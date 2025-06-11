<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell, TableCaption } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Trash2, CirclePlus, Pencil } from 'lucide-vue-next';

// Tipos
interface Factura {
  id: number;
  venta_id: number;
  user_id: number;
  cliente_id: number | null;
  numero_factura: string;
  fecha_emision: string;
  total: number;
  metodo_pago: 'efectivo' | 'tarjeta_credito' | 'tarjeta_debito';
  estado: 'pendiente' | 'pagada' | 'anulada';
  venta: {
    id: number;
    user: { name: string };
  };
  user: {
    id: number;
    name: string;
  };
  cliente: {
    id: number;
    nombre: string;
  } | null;
  created_at: string;
}

interface FacturaPageProps {
  [key: string]: any;
  facturas: {
    data: Factura[];
    meta: {
      current_page: number;
      last_page: number;
      from: number;
      to: number;
      total: number;
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

const page = usePage<FacturaPageProps>();
const props = page.props;

const search = ref(props.filters.search || '');

// debounce para búsqueda
let searchTimeout: ReturnType<typeof setTimeout> | null = null;
watch(search, (newValue) => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(route('facturas.index'), { search: newValue }, { preserveState: true, replace: true });
  }, 300);
});

const facturasData = computed(() => props.facturas.data);

interface BreadcrumbItem {
  title: string;
  href: string;
}
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Facturas', href: route('facturas.index') }];

const deleteFactura = (id: number) => {
  if (!confirm('¿Estás seguro de que quieres eliminar esta factura? Esta acción no se puede deshacer.')) return;

  router.delete(route('facturas.destroy', id), {
    preserveScroll: true,
    onSuccess: () => {
      // Eliminar la factura del array local sin recargar la página
      props.facturas.data = props.facturas.data.filter(factura => factura.id !== id);
    },
    onError: (errors) => {
      console.error(errors);
      alert('Hubo un error al eliminar la factura.');
    },
  });
};

const getEstadoVariant = (estado: string): "default" | "destructive" | "outline" => {
  switch (estado.toLowerCase()) {
    case 'pagada': return 'default';
    case 'pendiente': return 'outline';
    case 'anulada': return 'destructive';
    default: return 'default';
  }
};

const formatCurrency = (value: number) =>
  new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value);

const formatDate = (value: string) =>
  new Date(value).toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
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

        <input
          v-model="search"
          type="text"
          placeholder="Buscar factura..."
          class="px-4 py-2 rounded border border-zinc-300 w-full sm:w-64 dark:bg-zinc-800 dark:text-white"
          aria-label="Buscar factura"
        />
      </div>

      <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <Table class="min-w-[1200px] w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <TableCaption class="p-4 text-lg font-semibold text-left text-gray-900 dark:text-white">Lista de Facturas</TableCaption>
          <TableHeader class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-zinc-700 dark:text-gray-400">
            <TableRow>
              <TableHead>Número Factura</TableHead>
              <TableHead>Fecha Emisión</TableHead>
              <TableHead>Venta ID</TableHead>
              <TableHead>Cliente</TableHead>
              <TableHead>Cajero</TableHead>
              <TableHead>Total</TableHead>
              <TableHead>Método Pago</TableHead>
              <TableHead>Estado</TableHead>
              <TableHead class="text-center">Acciones</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="factura in facturasData" :key="factura.id">
              <TableCell>{{ factura.numero_factura }}</TableCell>
              <TableCell>{{ formatDate(factura.fecha_emision) }}</TableCell>
              <TableCell>
                <Link :href="route('ventas.show', factura.venta_id)" class="text-blue-600 hover:underline">
                  {{ factura.venta_id }}
                </Link>
              </TableCell>
              <TableCell>{{ factura.cliente?.nombre || 'N/A' }}</TableCell>
              <TableCell>{{ factura.user?.name || 'N/A' }}</TableCell>
              <TableCell>{{ formatCurrency(factura.total) }}</TableCell>
              <TableCell class="capitalize">{{ factura.metodo_pago.replace('_', ' ') }}</TableCell>
              <TableCell>
                <Badge :variant="getEstadoVariant(factura.estado)">
                  {{ factura.estado }}
                </Badge>
              </TableCell>
              <TableCell class="text-center">
                <div class="flex items-center justify-center gap-1">
                  <Button as-child size="sm" class="bg-blue-500 hover:bg-blue-700 text-white">
                    <Link :href="route('facturas.show', factura.id)">Ver</Link>
                  </Button>
                  <Button as-child size="sm" class="bg-yellow-500 hover:bg-yellow-700 text-white">
                    <Link :href="route('facturas.edit', factura.id)">
                      <Pencil />
                    </Link>
                  </Button>
                  <Button size="sm" class="bg-rose-500 hover:bg-rose-700 text-white" @click="deleteFactura(factura.id)">
                    <Trash2 />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="facturasData.length === 0">
              <TableCell :colspan="9" class="text-center text-gray-500 py-4">
                No hay facturas para mostrar.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div class="flex justify-center mt-4 gap-2 flex-wrap">
        <template v-for="(link, i) in props.facturas.links" :key="i">
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
