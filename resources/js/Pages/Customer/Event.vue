<template>
    <MenuEvent></MenuEvent>
    <Submenu></Submenu>
    <el-row :gutter="20">
        <el-col :span="14" :offset="5" class="p-0 bg-profile" style="background-image: url('../../../../general/not_image.png');">
            <div class="action-profile">
                <font-awesome-icon class="mr-2" :icon="['fas', 'pencil']" />
                Cambiar imagen de fondo
            </div>
            <el-col :span="24" class="back-title-edit">
                <h1 class="title is-1 has-text-white mb-2">{{ event.name }}</h1>
                <h5 class="title is-5 has-text-white mb-2">Categoría: {{ event.category.name }}</h5>
                <h5 class="title is-5 has-text-white"><a class="text-white" :href="'../../'+event.url" target="_blank">Ver sitio web</a></h5>
                <div style="position: absolute; top: -1vh; left: 2vh; height: 17vh; width: 15vh; background-color: white;">

                </div>
                <div class="action-event">
                    <font-awesome-icon class="mr-2" :icon="['fas', 'pencil']" />
                    Editar nombre y sitio de ventas
                </div>
            </el-col>
        </el-col>
    </el-row>
</template>

<script>
import MenuEvent from './MenuEvent.vue';
import Submenu from './Submenu.vue';
import apiClient from '@/apiClient';
import { dateEs, time } from '@/dateEs';
import { showNotification } from '@/notification';

export default {
    components: {
        MenuEvent,
        Submenu
    },
    data() {
        return {
            //Aquí se declaran las variables
            isActive: false,
            event: this.$page.props.event
        }
    },
    beforeMount() {
        //Aqui se pueden mandar llamar metodos antes de que se monte el componente
    },
    mounted() {
        document.title = 'Ticketland - Evento';
        console.log(this.event);
    },
    created() {
        //Aqui se pueden mandar llamar métodos, cuando se crea el componente
    },
}
</script>

<style scope>
    .action-profile {
        position: absolute;
        top: 15vh;
        right: 5vh;
        background-color: black;
        color: white;
        padding-left: 1vh;
        padding-right: 1vh;
        cursor: pointer;
    }
    .bg-profile {
        height: 45vh;
        background-size: 100%;
        background-position: center;
        position: relative;
    }
    .back-title-edit {
        background: rgba(0, 0, 0, 0.6);
        padding-left: 22vh !important;
        position: absolute;
        bottom: 0;
        color: white !important;
        padding-top: 2vh;
        padding-bottom: 1vh;
        width: 100%;
    }
    .action-event {
        position: absolute;
        background-color: black;
        color: white;
        top: 0px;
        right: 1vh;
        padding-left: 1vh;
        padding-right: 1vh;
        cursor: pointer;
    }
</style>