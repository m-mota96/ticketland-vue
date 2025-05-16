<template>
    <MenuEvent></MenuEvent>
    <Submenu :dadEvent="event"></Submenu>
    <el-row class="wrapper">
        <el-col :span="14" :offset="5" class="pt-6">
            <el-row :gutter="50">
                <el-col :span="17">
                    <el-card class="p-0 mb-5" v-for="(t, index) in tickets" :key="index">
                        <el-row>
                            <el-col :span="18">
                                <h4 class="title is-4 has-text-black mb-2">{{ t.name }}</h4>
                                <h4 class="title is-5 has-text-grey mb-2">{{ formatCurrency(t.price) }} MXN</h4>
                                <h6 class="subtitle is-6"><a class="has-text-link" :href="appUrl+'/'+event.url+'/'+t.name" target="_blank"><font-awesome-icon :icon="['fas', 'link']" /> {{ appUrl+'/'+event.url+'/'+t.name }}</a></h6>
                                <span class="pointer mr-5" @click="$refs.EditTicket.showModal(t)"><font-awesome-icon :icon="['fas', 'pencil']" /> Editar</span>
                                <el-popconfirm
                                    confirm-button-text="Eliminar"
                                    cancel-button-text="Cancelar"
                                    :hide-icon="true"
                                    confirm-button-type="danger"
                                    cancel-button-type="primary"
                                    :width="200"
                                    title="¿Seguro que desea eliminar este boleto?"
                                    @confirm="deleteTicket(t.id)"
                                >
                                    <template #reference>
                                        <span class="pointer"><font-awesome-icon :icon="['fas', 'trash-can']" /> Eliminar</span>
                                    </template>
                                </el-popconfirm>
                                <span v-if="t.status == 1" class="pointer ml-5" @click="updateStatus(t.id, 0)">
                                    <font-awesome-icon :icon="['far', 'circle-xmark']" /> Desactivar
                                </span>
                                <span v-if="t.status == 0" class="pointer ml-5" @click="updateStatus(t.id, 1)">
                                    <font-awesome-icon :icon="['far', 'circle-check']" /> Activar
                                </span>
                            </el-col>
                            <el-col :span="6" class="text-right">
                                <h4 class="subtitle is-4 has-text-black mb-0">
                                    <span class="text-blue">{{ t.access.length }}</span>
                                    <span class="text-blue">/</span>
                                    <span class="text-blue-ligth">{{ t.quantity }}</span>
                                </h4>
                                <span class="reserved mb-5">BOLETOS RESERVADOS</span>
                                <p class="mt-2">
                                    Estatus: 
                                    <span v-if="t.status == 1" class="has-text-success">Activo</span>
                                    <span v-if="t.status == 0" class="has-text-danger">Inactivo</span>
                                </p>
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
    <EditTicket ref="EditTicket" :eventId="event.id" :daysEvent="event.event_dates.length" :getTickets="getTickets"></EditTicket>
</template>

<script>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
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
            tickets: [],
        }
    },
    beforeMount() {
        this.getTickets();
    },
    mounted() {
        
    },
    created() {
        
    },
    methods: {
        async getTickets() {
            const response = await apiClient('customer/tickets', 'GET', {event_id: this.event.id});
            this.tickets   = response.data;
        },
        async updateStatus(id, status) {
            const response = await apiClient('customer/ticketStatus', 'PUT', {event_id: this.event.id, id, status});
            if (response.error) {
                showNotification('¡Error!', response.msj, 'error');
                return false;
            }
            this.getTickets();
            showNotification('¡Correcto!', response.msj, 'success');
        },
        async deleteTicket(ticket_id) {
            const response = await apiClient('customer/ticket', 'DELETE', {event_id: this.event.id, ticket_id});
            if (response.error) {
                showNotification('¡Error!', response.msj, 'error', 6000);
                return false;
            }
            this.getTickets();
            showNotification('¡Correcto!', response.msj, 'success');
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }).format(value);
        }
    }
}
</script>

<style scoped>
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