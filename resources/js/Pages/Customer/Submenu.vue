<template>
    <el-menu
        class="el-menu-demo text-left"
        mode="horizontal"
        :ellipsis="false"
        background-color="#2f2c2b "
        text-color="white"
        style="margin-top: -1px !important;"
    >
        <el-menu-item index="1" class="ms-10"><Link :href="route('cliente.estadisticas', dadEvent.id)"><font-awesome-icon class="mr-2" :icon="['fas', 'chart-line']" /> Estadísticas</Link></el-menu-item>
        <el-sub-menu index="2">
            <template #title><font-awesome-icon class="mr-2" :icon="['fas', 'gear']" />Configuración</template>
            <el-menu-item index="2-1"><Link class="has-text-white" :href="route('cliente.evento', dadEvent.id)"><font-awesome-icon class="mr-2" :icon="['fas', 'eye']" /> Apariencia</Link></el-menu-item>
            <!-- <el-menu-item index="2-2"><font-awesome-icon class="mr-2" :icon="['fas', 'ticket-simple']" /> Forma por boleto</el-menu-item> -->
        </el-sub-menu>
        <el-menu-item index="3"><Link :href="route('cliente.boletos', dadEvent.id)"><font-awesome-icon class="mr-1" :icon="['fas', 'ticket']" /> Boletos</Link></el-menu-item>
        <el-menu-item index="4"><Link :href="route('cliente.descuentos', dadEvent.id)"><font-awesome-icon class="mr-1" :icon="['fas', 'tags']" /> Cupones</Link></el-menu-item>
        <el-menu-item index="4"><Link :href="route('cliente.reservaciones', dadEvent.id)"><font-awesome-icon class="mr-1" :icon="['fas', 'cart-shopping']" /> Reservaciones</Link></el-menu-item>
        <el-menu-item index="5"><Link :href="route('cliente.escaner', dadEvent.id)"><font-awesome-icon class="mr-1" :icon="['fas', 'qrcode']" /> Escaner</Link></el-menu-item>
    </el-menu>
</template>
<script>
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
  
export default {
    props: {
        dadEvent: Object
    },
    components: {
        ResponsiveNavLink,
        Link
    },
    mounted() {
        document.title = `Ticketland - ${this.dadEvent.name}`;
    }
}
</script>
<style scoped>
    .el-menu--horizontal>.el-menu-item {
        font-size: 0.95rem !important;
    }
    .el-menu--horizontal>.el-menu-item.is-active {
        border-bottom: unset !important;
        color: white !important;
    }
    .ms-10 {
        margin-left: 41vh !important;
    }
    .me-10 {
        margin-right: 41vh !important;
    }
</style>