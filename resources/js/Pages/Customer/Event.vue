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
                <div class="content-logo">
                    <div class="text-center pt-4 container-logo">
                        <h5 class="subtitle is-5 mt-5 text-logo">Agregar Logo</h5>
                    </div>
                </div>
                <div class="action-event">
                    <font-awesome-icon class="mr-2" :icon="['fas', 'pencil']" />
                    Editar nombre y sitio de ventas
                </div>
            </el-col>
        </el-col>
        <el-col :span="12" :offset="6">
            <el-card class="pt-3 mb-6">
                <el-row :gutter="30">
                    <el-col :span="16">
                        <h3 class="title is-3 has-text-dark">Acerca de <span class="subtitle is-6 ml-4 pointer"><font-awesome-icon :icon="['fas', 'pencil']" /> Editar</span></h3>
                        <p class="has-text-grey justify">
                            {{event.description}}
                        </p>
                    </el-col>
                    <el-col :span="8">
                        <el-card class="card-personalized pb-3">
                            <div>
                                <span class="subtitle is-6 has-text-dark bold"><font-awesome-icon :icon="['fas', 'calendar-days']" /> CUÁNDO</span>
                                <span class="text-edit has-text-dark bold ml-5 pointer"><font-awesome-icon :icon="['fas', 'pencil']" /> Editar</span>
                                <div class="mt-2 mb-0">
                                    <span class="normal">DE: </span>
                                    <span class="hast-text-dark bold">{{ formatDate(event.event_dates[0].date, 1) }} - {{ formatTime(event.event_dates[0].initial_time) }}</span><br>
                                    <span class="normal">AL: </span>
                                    <span class="hast-text-dark bold">{{ formatDate(event.event_dates[event.event_dates.length-1].date, 1) }} - {{ formatTime(event.event_dates[event.event_dates.length-1].final_time) }}</span>
                                </div>
                            </div>
                            <hr>
                            <div>
                                <span class="subtitle is-6 has-text-dark bold"><font-awesome-icon :icon="['fas', 'location-dot']" /> DÓNDE</span>
                                <span class="text-edit has-text-dark bold ml-5 pointer"><font-awesome-icon :icon="['fas', 'pencil']" /> Editar</span>
                            </div>
                            <hr>
                            <div>
                                <span class="subtitle is-6 has-text-dark bold"><font-awesome-icon :icon="['fas', 'address-card']" /> CONTACTO</span>
                                <span class="text-edit has-text-dark bold ml-5 pointer"><font-awesome-icon :icon="['fas', 'pencil']" /> Editar</span>
                            </div>
                            <hr>
                            <div>
                                <span class="subtitle is-6 has-text-dark bold"><font-awesome-icon :icon="['fas', 'circle-dollar-to-slot']" /> TIPO DE EVENTO</span>
                                <div class="mt-2 mb-0">
                                    {{ event.cost_type == 'paid' ? 'DE CONSUMO' : 'DE REGISTRO' }}
                                </div>
                            </div>
                        </el-card>
                    </el-col>
                </el-row>
            </el-card>
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
        console.log(this.event);
    },
    created() {
        //Aqui se pueden mandar llamar métodos, cuando se crea el componente
    },
    methods: {
        formatDate(_date) {
            return dateEs(_date, 1, ' ');
        },
        formatTime(_time) {
            return time(_time);
        }
    }
}
</script>

<style scope>
    .card-personalized {
        background-color: #f8f7f7 !important;
    }
    hr {
        border: 1px solid #eeeceb;
    }
    .text-edit {
        font-size: 0.8rem;
    }
    .bold {
        font-weight: bold !important;
    }
    .normal {
        font-weight: normal !important;
    }
    .justify {
        text-align: justify;
    }
    .pointer {
        cursor: pointer;
    }
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
        margin-top: -1px !important;
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
    .content-logo {
        position: absolute;
        top: -1vh;
        left: 2vh;
        height: 17vh;
        width: 15vh;
        background-color: white;
        padding: 0.5rem;
    }
    .container-logo {
        width: 100%;
        height: 100%;
        border: 2px dashed grey;
    }
    .text-logo {
        color: #55504f !important;
        font-weight: bold !important;
        cursor: pointer;
    }
</style>