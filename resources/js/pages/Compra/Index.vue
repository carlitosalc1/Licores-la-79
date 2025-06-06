<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { Compra, type BreadcrumbItem, type SharedData } from '@/types'
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell, TableCaption } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { ref, computed } from 'vue'
import {  Trash2, CirclePlus, Pencil } from 'lucide-vue-next'

interface CompraPageProps extends SharedData {
  compras: {
    data: Compra[]
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

const { props } = usePage<CompraPageProps>()
const search = ref('')
const compras = computed(() => props.compras.data)

const comprasFiltradas = computed(() =>
  compras.value.filter((compra) =>
    [
      compra.id,
      compra.proveedor?.razon_social,
      compra.user.name,
      compra.fecha_compra,
      compra.estado,
      compra.total,
    ]
      .some((value) =>
        String(value).toLowerCase().includes(search.value.toLowerCase())
      )
  )
)

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Compras', href: '/compras' }]

const deleteCompra = (id: number) => {
  if (!confirm('¿Estás seguro de eliminar esta compra? Esta acción no se puede deshacer.')) return
  router.delete(`/compras/${id}`, {
    preserveScroll: true,
    onSuccess: () => router.visit('/compras', { replace: true }),
    onError: (errors) => console.error(errors),
  })
}

const getEstadoVariant = (estado: string): "default" | "destructive" | "outline" | "secondary" | null | undefined => {
  switch (estado.toLowerCase()) {
    case 'pagado':
      return 'default'
    case 'cancelada':
      return 'destructive'
    default:
      return 'default'
  }
}

</script>

<template>
  <Head title="Compras" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4 bg-gray-100 dark:bg-gray-900">
      <div class="flex justify-between items-center flex-wrap gap-2">
        <Button as-child size="sm" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-500 text-white font-medium shadow-lg">
          <Link href="/compras/create">
            <CirclePlus class="mr-1" /> Nueva Compra
          </Link>
        </Button>

        <input v-model="search" type="text" placeholder="Buscar compra..."
          class="px-4 py-2 rounded border border-zinc-300 w-full sm:w-64 dark:bg-zinc-800 dark:text-white" />
      </div>

      <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <Table class="min-w-[1200px] w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <TableCaption class="p-4 text-lg font-semibold text-left text-gray-900 dark:text-white">Lista de Compras</TableCaption>
          <TableHeader class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-zinc-700 dark:text-gray-400">
            <TableRow>
              <TableHead>#</TableHead>
              <TableHead>Proveedor</TableHead>
              <TableHead>Cajero</TableHead>
              <TableHead>Fecha</TableHead>
              <TableHead>Estado</TableHead>
              <TableHead>Total</TableHead>
              <TableHead>Productos</TableHead>
              <TableHead class="text-center">Acciones</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="compra in comprasFiltradas" :key="compra.id">
              <TableCell>{{ compra.id }}</TableCell>
              <TableCell>{{ compra.proveedor?.razon_social }}</TableCell>
              <TableCell>{{ compra.user.name }}</TableCell>
              <TableCell>{{ compra.fecha_compra }}</TableCell>
              <TableCell>
                <Badge :variant="getEstadoVariant(compra.estado)">
                  {{ compra.estado }}
                </Badge>
              </TableCell>
              <TableCell>
                {{ Number(compra.total).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }) }}
              </TableCell>
              <TableCell>
                   <div v-for="detalle in compra.detalle_compras" :key="detalle.id">
                       {{ detalle.producto?.nombre || 'Producto eliminado' }}
                   </div>
              </TableCell>
              <TableCell class="text-center space-x-2">
                <Button as-child size="sm" class="bg-blue-500 hover:bg-blue-700 text-white">
                  <Link :href="route('compras.edit', compra.id)"> <Pencil /></Link>
                </Button>
                <Button size="sm" class="bg-rose-500 hover:bg-rose-700 text-white" @click="deleteCompra(compra.id)">
                  <Trash2 />
                </Button>

              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div class="flex justify-center mt-4 gap-2 flex-wrap">
        <template v-for="(link, i) in props.compras.links" :key="i">
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
