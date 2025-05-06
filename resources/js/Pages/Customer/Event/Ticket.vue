<template>
    <MenuEvent></MenuEvent>
    <Submenu :dadEvent="event"></Submenu>
    <el-row>
        <el-col :span="14" :offset="5" class="pt-6">
            <el-row :gutter="50">
                <el-col :span="17">
                    <el-card class="p-0" v-for="(t, index) in tickets" :key="index">
                        <el-row>
                            <el-col :span="18">
                                <h4 class="title is-4 has-text-black mb-2">{{ t.name }}</h4>
                                <h4 class="title is-5 has-text-grey mb-2">{{ formatCurrency(t.price) }} MXN</h4>
                                <h6 class="subtitle is-6"><a class="has-text-link" :href="appUrl+'/'+event.url+'/'+t.name" target="_blank"><font-awesome-icon :icon="['fas', 'link']" /> {{ appUrl+'/'+event.url+'/'+t.name }}</a></h6>
                                <span class="pointer mr-5" @click="$refs.EditTicket.showModal(t)"><font-awesome-icon :icon="['fas', 'pencil']" /> Editar</span>
                                <span class="pointer"><font-awesome-icon :icon="['fas', 'trash-can']" /> Eliminar</span>
                            </el-col>
                            <el-col :span="6" class="text-right">
                                <h4 class="subtitle is-4 has-text-black mb-0">
                                    <span class="text-blue">0</span>
                                    <span class="text-blue">/</span>
                                    <span class="text-blue-ligth">{{ t.quantity }}</span>
                                </h4>
                                <span class="reserved">BOLETOS RESERVADOS</span>
                            </el-col>
                        </el-row>
                    </el-card>
                </el-col>
                <el-col :span="7">
                    <el-card class="text-center pb-4">
                        <h5 class="subtitle is-5 has-text-dark mb-5">OPCIONES</h5>
                        <el-button class="bold w-100" type="warning" size="large" @click="$refs.EditTicket.showModal()">
                            <font-awesome-icon class="mr-1 bold" :icon="['fas', 'plus']" /> Nuevo tipo de boleto
                        </el-button>
                        <br>
                        <el-button class="w-100 mt-4 bg-purple has-text-white bold" size="large">
                            <font-awesome-icon class="mr-1 bold" :icon="['fas', 'plus']" /> Generar coresías
                        </el-button>
                    </el-card>
                    <el-card class="text-center pb-4 mt-5">
                        <h5 class="subtitle is-5 has-text-dark mb-5">MODELO DE COBRO</h5>
                        <el-button-group class="w-100 mb-4">
                            <el-button class="w-50" type="primary" size="large">
                                SEPARADO
                            </el-button>
                            <el-button class="w-50" type="primary" size="large" plain>
                                INCLUIDO
                            </el-button>
                        </el-button-group>
                        <template #footer><span class="has-text-link"><font-awesome-icon class="mr-1" :icon="['fas', 'circle-question']" /> ¿Que es esto?</span></template>
                    </el-card>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    <Footer></Footer>
    <EditTicket ref="EditTicket" :eventId="event.id" :daysEvent="event.event_dates.length"></EditTicket>
</template>

<script>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { dateEs, time } from '@/dateEs';
import MenuEvent from '../MenuEvent.vue';
import Submenu from '../Submenu.vue';
import Footer from '../Footer.vue';
import { EditTicket } from './Modals';

export default {
    components: {
        MenuEvent,
        Submenu,
        Footer,
        EditTicket
    },
    data() {
        return {
            appUrl: window.location.origin,
            event: this.$page.props.event,
            tickets: this.$page.props.tickets,
        }
    },
    beforeMount() {
        
    },
    mounted() {
        
    },
    created() {
        
    },
    methods: {
        formatCurrency(value) {
            return new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }).format(value);
        }
    }
}
</script>

<style scope>
    .bg-purple {
        background-color: #6f42c1 !important;
    }
    .reserved {
        font-size: 0.75rem;;
    }
    .text-blue {
        color: #409cff !important;
    }
    .text-blue-ligth {
        color: #80bdff !important;
    }
</style>