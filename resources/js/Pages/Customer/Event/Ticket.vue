<template>
    <MenuEvent></MenuEvent>
    <Submenu :dadEvent="event"></Submenu>
    <el-row class="wrapper">
        <el-col :span="14" :offset="5" class="pt-6">
            <el-row :gutter="50">
                <el-col :span="17">
                    <el-row class="text-center pt-5" v-if="!tickets.length">
                        <h3 class="subtitle is-3 has-text-grey w-100">Crea tu primer boleto para comenzar.</h3>
                    </el-row>
                    <el-card class="p-0 mb-5" v-for="(t, index) in tickets" :key="index">
                        <el-row>
                            <el-col :span="18">
                                <h4 class="title is-4 has-text-black mb-2">{{ t.name }}</h4>
                                <h4 class="title is-5 has-text-grey mb-1">{{ formatCurrency(t.price) }} MXN</h4>
                                <h6
                                    class="subtitle is-6 mb-2 mt-2 has-text-link bold pointer"
                                    @click="copyUrl(t.name)"
                                    title="Haz click para copiar URL"
                                >
                                    <font-awesome-icon :icon="['fas', 'link']" /> {{ appUrl+'/evento/'+event.url+'/'+t.name }}
                                </h6>
                                <h6 v-if="t.promotion" class="has-text-warning bold">{{ t.promotion }}% de descuento hasta el {{ formatDate(t.date_promotion) }}</h6>
                                <div class="mt-5">
                                    <span class="pointer mt-5 mr-5" @click="$refs.EditTicket.showModal(t)"><font-awesome-icon :icon="['fas', 'pencil']" /> Editar</span>
                                    <el-popconfirm
                                        confirm-button-text="Eliminar"
                                        cancel-button-text="Cancelar"
                                        :hide-icon="true"
                                        confirm-button-type="danger"
                                        cancel-button-type="info"
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
                                </div>
                            </el-col>
                            <el-col :span="6" class="text-right">
                                <h4 class="subtitle is-4 has-text-black mb-0">
                                    <span class="text-blue">{{ t.sales }}</span>
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
                        <!-- <el-button class="w-100 mt-4 bg-purple has-text-white bold" size="large">
                            <font-awesome-icon class="mr-1 bold" :icon="['fas', 'plus']" /> Generar coresías
                        </el-button> -->
                    </el-card>
                    <el-card class="text-center pb-4 mt-5">
                        <h5 class="subtitle is-5 has-text-dark mb-5">MODELO DE COBRO</h5>
                        <el-button-group class="w-100 mb-4">
                            <el-button class="w-50" type="primary" size="large" v-if="event.model_payment == 'separated'">
                                SEPARADO
                            </el-button>
                            <el-button class="w-50" type="primary" size="large" @click="modelPayment('separated')" plain v-if="event.model_payment == 'including'">
                                SEPARADO
                            </el-button>
                            <el-button class="w-50" type="primary" size="large" @click="modelPayment('including')" plain v-if="event.model_payment == 'separated'">
                                INCLUIDO
                            </el-button>
                            <el-button class="w-50" type="primary" size="large" v-if="event.model_payment == 'including'">
                                INCLUIDO
                            </el-button>
                        </el-button-group>
                        <template #footer>
                            <span class="has-text-link pointer" @click="info">
                                <font-awesome-icon class="mr-1" :icon="['fas', 'circle-question']" /> ¿Que es esto?
                            </span>
                        </template>
                    </el-card>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    <Footer></Footer>
    <EditTicket ref="EditTicket" :eventId="event.id" :daysEvent="event.event_dates.length" :getTickets="getTickets" :modelPayment="event.model_payment"></EditTicket>
</template>

<script>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import MenuEvent from '../MenuEvent.vue';
import Submenu from '../Submenu.vue';
import Footer from '../Footer.vue';
import { EditTicket } from './Modals';
import Swal from 'sweetalert2';
import { dateEs } from '@/dateEs';

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
        async modelPayment(model_payment) {
            const response = await apiClient('customer/editModelPayment', 'PUT', {event_id: this.event.id, model_payment});
            if (response.error) {
                showNotification('¡Error!', response.msj, 'error', 7000);
                return false;
            }
            this.event.model_payment = model_payment;
            showNotification('¡Correcto!', response.msj, 'success');
        },
        info() {
            Swal.fire({
                icon: 'info',
                title: 'Información',
                html: `
                    <p class="justify"><b>Separado: </b>Las comosiones de Ticketland serán pagadas por el cliente.</p>
                    <p class="justify"><b>Incluído: </b>Las comosiones de Ticketland serán descontadas del precio de los boletos.</p>
                `,
                confirmButtonText: 'Cerrar',
                scrollbarPadding: false
            });
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }).format(value);
        },
        formatDate(_date) {
            return dateEs(_date, 1, '/');
        },
        copyUrl(ticketName) {
            const url = this.appUrl + '/evento/' + this.event.url + '/' + ticketName.replaceAll(' ', '%20');

            if (navigator.clipboard) {
                navigator.clipboard.writeText(url)
                .then(() => showNotification(null, 'URL copiada al portapapeles.', 'success'))
                .catch(err => console.error('Error copiando la URL:', err));
            } else {
                const input = document.createElement('input');
                input.value = url;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                showNotification(null, 'URL copiada al portapapeles.', 'success');
            }
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