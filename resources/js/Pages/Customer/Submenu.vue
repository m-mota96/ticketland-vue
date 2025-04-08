<template>
    <el-menu
        class="el-menu-demo text-left"
        mode="horizontal"
        :ellipsis="false"
        background-color="#2f2c2b "
        text-color="white"
        style="margin-top: -1px !important;"
    >
        <el-menu-item index="1" class="ms-10"><font-awesome-icon :icon="['fas', 'chart-line']" /> <a class="ml-2">Estadísticas</a></el-menu-item>
        <el-sub-menu index="2">
            <template #title><font-awesome-icon class="mr-2" :icon="['fas', 'gear']" />Configuración</template>
            <el-menu-item index="2-1"><font-awesome-icon class="mr-2" :icon="['fas', 'eye']" /> Apariencia</el-menu-item>
            <el-menu-item index="2-2"><font-awesome-icon class="mr-2" :icon="['fas', 'ticket-simple']" /> Forma por boleto</el-menu-item>
        </el-sub-menu>
        <el-menu-item index="3"><font-awesome-icon :icon="['fas', 'ticket']" /> <a class="ml-2">Boletos</a></el-menu-item>
        <el-menu-item index="4"><font-awesome-icon :icon="['fas', 'barcode']" /> <a class="ml-2">Códigos</a></el-menu-item>
        <el-menu-item index="4"><font-awesome-icon :icon="['fas', 'cart-shopping']" /> <a class="ml-2">Reservaciones</a></el-menu-item>
        <el-menu-item index="4"><font-awesome-icon :icon="['fas', 'qrcode']" /> <a class="ml-2">Escaner</a></el-menu-item>
    </el-menu>
</template>
<script>
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
  
export default {
    components: {
        ResponsiveNavLink
    },
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