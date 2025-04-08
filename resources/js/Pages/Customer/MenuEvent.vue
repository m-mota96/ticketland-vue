<template>
    <el-menu
        class="el-menu-demo"
        mode="horizontal"
        :ellipsis="false"
        background-color="#100e0d"
        text-color="white"
    >
        <el-menu-item index="0" class="ms-10"><font-awesome-icon :icon="['fas', 'link']" /> <a class="ml-1 mt-1" :href="'../../'+$page.props.event.url" target="_blank">https://ticketland.mx/{{$page.props.event.url}}</a></el-menu-item>
        <el-menu-item index="1"><font-awesome-icon :icon="['fas', 'house-chimney']" /><a class="ml-1 mt-1" :href="route('cliente.mis_eventos')">Dashboard</a></el-menu-item>
        <el-sub-menu index="2" class="me-10 mt-1">
            <template #title>{{ $page.props.auth.user.name }}</template>
            <el-menu-item index="2-1">
                <ResponsiveNavLink
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="is-justify-content-center is-flex"
                >
                    Cerrar sesión
                </ResponsiveNavLink>
            </el-menu-item>
        </el-sub-menu>
    </el-menu>
</template>
<script>
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
  
export default {
    components: {
        //Aqui se agregan los componentes, en dado caso que quiereas usar, para separar código, yo separo los modales y aqui los agrego
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
    .el-menu--horizontal > .el-menu-item:nth-child(1) {
        margin-right: auto;
    }
</style>