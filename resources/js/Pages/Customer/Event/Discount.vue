<template>
    <MenuEvent></MenuEvent>
    <Submenu :dadEvent="event"></Submenu>
    <el-row class="wrapper">
        <el-col :span="14" :offset="5" class="pt-6">
            <el-row :gutter="50">
                <el-col :span="17">
                    <el-card class="p-0 mb-5" v-for="(d, index) in discounts" :key="index">
                        <el-row>
                            <el-col :span="18">
                                <h4 class="title is-4 has-text-black mb-2">{{ d.code }}</h4>
                                <h4 class="title is-5 has-text-grey mb-3">Descuento: {{ d.discount }}%</h4>
                                <p class="mb-5 has-text-link">
                                    [<span v-for="(t, index) in d.tickets" :key="index">
                                        <span v-if="index == 0">{{ t.name }}</span><span v-if="index != 0">, {{ t.name }}</span>
                                    </span>]
                                </p>
                                <span class="pointer mr-5" @click="$refs.EditDiscount.showModal(d)"><font-awesome-icon :icon="['fas', 'pencil']" /> Editar</span>
                                <el-popconfirm
                                    confirm-button-text="Eliminar"
                                    cancel-button-text="Cancelar"
                                    :hide-icon="true"
                                    confirm-button-type="danger"
                                    cancel-button-type="primary"
                                    :width="200"
                                    title="¿Seguro que desea eliminar este código?"
                                    @confirm="deleteDiscount(d.id)"
                                >
                                    <template #reference>
                                        <span class="pointer"><font-awesome-icon :icon="['fas', 'trash-can']" /> Eliminar</span>
                                    </template>
                                </el-popconfirm>
                                <span v-if="d.status == 1" class="pointer ml-5" @click="updateStatus(d.id, 0)">
                                    <font-awesome-icon :icon="['far', 'circle-xmark']" /> Desactivar
                                </span>
                                <span v-if="d.status == 0" class="pointer ml-5" @click="updateStatus(d.id, 1)">
                                    <font-awesome-icon :icon="['far', 'circle-check']" /> Activar
                                </span>
                            </el-col>
                            <el-col :span="6" class="text-right">
                                <h4 class="subtitle is-4 has-text-black mb-0">
                                    <span class="text-blue">0</span>
                                    <span class="text-blue">/</span>
                                    <span class="text-blue-ligth">{{ d.quantity }}</span>
                                </h4>
                                <span class="reserved mb-5">CANTIDAD UTILIZADA</span>
                                <p class="mt-4">
                                    Estatus: 
                                    <span v-if="d.status == 1" class="has-text-success">Activo</span>
                                    <span v-if="d.status == 0" class="has-text-warning">Inactivo</span>
                                </p>
                            </el-col>
                        </el-row>
                    </el-card>
                </el-col>
                <el-col :span="7">
                    <el-card class="text-center pb-4">
                        <h5 class="subtitle is-5 has-text-dark mb-5">OPCIONES</h5>
                        <el-button class="bold w-100" type="warning" size="large" @click="$refs.EditDiscount.showModal()">
                            <font-awesome-icon class="mr-1 bold" :icon="['fas', 'plus']" /> Nuevo código
                        </el-button>
                    </el-card>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    <Footer></Footer>
    <EditDiscount ref="EditDiscount" :eventId="event.id" :getDiscounts="getDiscounts" :tickets="tickets"></EditDiscount>
</template>

<script>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import MenuEvent from '../MenuEvent.vue';
import Submenu from '../Submenu.vue';
import Footer from '../Footer.vue';
import { EditDiscount } from './Modals';

export default {
    components: {
        MenuEvent,
        Submenu,
        Footer,
        EditDiscount
    },
    data() {
        return {
            appUrl: window.location.origin,
            event: this.$page.props.event,
            tickets: this.$page.props.tickets,
            discounts: [],
        }
    },
    beforeMount() {
        this.getDiscounts();
    },
    mounted() {
        
    },
    created() {
        
    },
    methods: {
        async getDiscounts() {
            const response = await apiClient('customer/discounts', 'GET', {event_id: this.event.id});
            this.discounts = response.data;
        },
        async updateStatus(id, status) {
            const response = await apiClient('customer/discountStatus', 'PUT', {event_id: this.event.id, id, status});
            if (response.error) {
                showNotification('¡Error!', response.msj, 'error');
                return false;
            }
            this.getDiscounts();
            showNotification('¡Correcto!', response.msj, 'success');
        },
        async deleteDiscount(id) {
            const response = await apiClient('customer/discount', 'DELETE', {event_id: this.event.id, id});
            if (response.error) {
                showNotification('¡Error!', response.msj, 'error', 6000);
                return false;
            }
            this.getDiscounts();
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