<template>
    <el-menu
        class="el-menu-demo"
        mode="horizontal"
        :ellipsis="false"
        background-color="#100e0d"
        text-color="white"
    >
        <el-menu-item index="0"></el-menu-item>
        <el-menu-item index="1" v-if="viewDashboard"><a :href="route('cliente.mis_eventos')">Mis eventos</a></el-menu-item>
        <el-menu-item index="2" v-if="!viewDashboard" :class="{ zoom: animation }" @animationend="$emit('activate-animation')"><a :href="route('cliente.mi_perfil')">Mi perfil</a></el-menu-item>
        <el-sub-menu index="3" class="me-10">
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
//import Api from '@/store/modules/Api' // Clase Api donde se declara Axios y la ruta al servidor
//import ModalTipoPermisos from './components/ModalTipoPermisos'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
  
export default {
    props: {
        viewDashboard: {
            type: Boolean,
            required: false,
            default: false
        },
        animation: {
            type: Boolean,
            required: false,
            default: false
        }
    },
    components: {
        //Aqui se agregan los componentes, en dado caso que quiereas usar, para separar código, yo separo los modales y aqui los agrego
        ResponsiveNavLink
    },
    data() {
        return {
            
        }
    },
    beforeMount() {
        //Aqui se pueden mandar llamar metodos antes de que se monte el componente
    },
    mounted() {
        
    },
    created() {
        //Aqui se pueden mandar llamar métodos, cuando se crea el componente
    },
   
    methods: {
    // Aqui van los métodos
        newMethod() {
        
        }
    }
}
</script>
  
<style scoped>
    .el-menu--horizontal>.el-menu-item {
        font-size: 1rem !important;
    }
    .me-10 {
        margin-right: 50vh;
    }
    .el-menu--horizontal > .el-menu-item:nth-child(1) {
        margin-right: auto;
    }
    .zoom {
        animation: zoomInOut 1.5s ease;
        animation-iteration-count: 10;
    }

    @keyframes zoomInOut {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.5); /* tamaño máximo */
        }
        100% {
            transform: scale(1); /* vuelve a tamaño original */
        }
    }
</style>