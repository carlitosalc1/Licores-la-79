<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { Venta, type BreadcrumbItem, type SharedData } from '@/types'
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell, TableCaption } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { ref, computed } from 'vue'
import { FileText, Trash2, CirclePlus, Pencil } from 'lucide-vue-next'

interface VentaPageProps extends SharedData {
  ventas: {
    data: Venta[]
    meta: {
      current_page: number
      last_page: number
    }
    links: {
      url: string | null
      label: string
      active: boolean
    }[]
  }
}

const { props } = usePage<VentaPageProps>()
const search = ref('')
const ventas = computed(() => props.ventas.data)

const ventasFiltradas = computed(() =>
  ventas.value.filter((venta) =>
    [
      venta.id,
      venta.cliente?.nombre,
      venta.user.name,
      venta.fecha_venta,
      venta.metodo_pago,
      venta.estado,
      venta.total,
      venta.tipo_comprobante
    ]
      .some((value) =>
        String(value).toLowerCase().includes(search.value.toLowerCase())
      )
  )
)

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Ventas', href: '/ventas' }]

const deleteVenta = (id: number) => {
  if (!confirm('¿Estás seguro de eliminar esta venta? Esta acción no se puede deshacer.')) return
  router.delete(`/ventas/${id}`, {
    preserveScroll: true,
    onSuccess: () => router.visit('/ventas', { replace: true }),
    onError: (errors) => console.error(errors),
  })
}

const getEstadoVariant = (estado: string): "default" | "destructive" | "outline" | "secondary" | null | undefined => {
  switch (estado.toLowerCase()) {
    case 'pagado':
      return 'default'      // O 'secondary', si quieres algo más visible
    case 'pendiente':
      return 'outline'      // Alternativa visual
    case 'cancelada':
      return 'destructive'
    default:
      return 'default'
  }
}

</script>

<template>
  <Head title="Ventas" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4 bg-gray-100 dark:bg-gray-900">
      <div class="flex justify-between items-center flex-wrap gap-2">
        <Button as-child size="sm" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-500 text-white font-medium shadow-lg">
          <Link href="/ventas/create">
            <CirclePlus class="mr-1" /> Nueva Venta
          </Link>
        </Button>

        <input v-model="search" type="text" placeholder="Buscar venta..."
          class="px-4 py-2 rounded border border-zinc-300 w-full sm:w-64 dark:bg-zinc-800 dark:text-white" />
      </div>

      <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <Table class="min-w-[1200px] w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <TableCaption class="p-4 text-lg font-semibold text-left text-gray-900 dark:text-white">Lista de Ventas</TableCaption>
          <TableHeader class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-zinc-700 dark:text-gray-400">
            <TableRow>
              <TableHead>#</TableHead>
              <TableHead>Cliente</TableHead>
              <TableHead>Cajero</TableHead>
              <TableHead>Fecha</TableHead>
              <TableHead>Pago</TableHead>
              <TableHead>Estado</TableHead>
              <TableHead>Total</TableHead>
              <TableHead>Comprobante</TableHead>
              <TableHead>Productos</TableHead>
              <TableHead class="text-center">Acciones</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="venta in ventasFiltradas" :key="venta.id">
              <TableCell>{{ venta.id }}</TableCell>
              <TableCell>{{ venta.cliente?.nombre }}</TableCell>
              <TableCell>{{ venta.user.name }}</TableCell>
              <TableCell>{{ venta.fecha_venta }}</TableCell>
              <TableCell>{{ venta.metodo_pago }}</TableCell>
              <TableCell>
                <Badge :variant="getEstadoVariant(venta.estado)">
                  {{ venta.estado }}
                </Badge>
              </TableCell>
              <TableCell>
                {{ Number(venta.total).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }) }}
              </TableCell>
              <TableCell>{{ venta.tipo_comprobante }}</TableCell>
              <TableCell>
                   <div v-for="detalle in venta.detalle_ventas" :key="detalle.id">
                       {{ detalle.producto?.nombre || 'Producto eliminado' }}
                   </div>
              </TableCell>
              <TableCell class="text-center space-x-2">
                <Button as-child size="sm" class="bg-blue-500 hover:bg-blue-700 text-white">
                  <Link :href="route('ventas.edit', venta.id)"> <Pencil /></Link>
                </Button>
                <Button size="sm" class="bg-rose-500 hover:bg-rose-700 text-white" @click="deleteVenta(venta.id)">
                  <Trash2 />
                </Button>
                <Button as-child size="sm" class="bg-green-500 hover:bg-green-700 text-white">
                 <a :href="route('ventas.factura', venta.id)" target="_blank" rel="noopener">
                   <FileText /> </a>
               </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div class="flex justify-center mt-4 gap-2 flex-wrap">
        <template v-for="(link, i) in props.ventas.links" :key="i">
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
