<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, Users, UsersRound, ScrollText, RectangleHorizontal, Truck, Computer, Fish, SquareUserRound,
         Soup, ReceiptText, Receipt, ShoppingCart, Utensils, ClipboardList, PackageOpen,} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
{
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Usuario',
        href: '/users',
        icon: Users,
    },
    {
        title: 'Cliente',
        href: '/clientes',
        icon: UsersRound,
    },
    {
        title: 'Proveedor',
        href: '/proveedors',
        icon: Truck,
    },
    {
        title: 'Categoria de Productos',
        href: '/categoria_productos',
        icon: Fish,
    },
    {
        title: 'Producto',
        href: '/productos',
        icon: Fish,
    },
    {
        title: 'Venta',
        href: '/ventas',
        icon: Receipt,
    },
    {
        title: 'Detalle de Venta',
        href: '/detalle_ventas',
        icon: ClipboardList,
    },
    {
        title: 'Compra',
        href: '/compras',
        icon: ShoppingCart,
    },
    {
        title: 'Detalle de Compra',
        href: '/detalle_compras',
        icon: ClipboardList,
    },
    {
        title: 'Factura',
        href: '/facturas',
        icon: ReceiptText,
    },
    {
        title: 'Detalle de Factura',
        href: '/detalle_facturas',
        icon: ClipboardList,
    },
    {
        title: 'Rol',
        href: '/rols',
        icon: SquareUserRound,
    },
    {
        title: 'Inventario',
        href: '/inventarios',
        icon: PackageOpen,
    },
    {
        title: 'Reporte',
        href: '/reportes',
        icon: ScrollText,
    },
    {
        title: 'Pago',
        href: '/pagos',
        icon: Computer,
    },
];


</script>

<template>
    <Sidebar collapsible="icon" variant="floating">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
