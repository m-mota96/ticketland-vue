<template>
    <MenuEvent></MenuEvent>
    <Submenu :dadEvent="event"></Submenu>
    <el-row class="wrapper">
        <el-col class="pl-4 pr-4" :span="24">
            <el-card class="w-100 pt-5 pb-5 pl-1 pr-1">
                <el-row :gutter="20">
                    <el-col class="mb-5" :span="4" :offset="15">
                        <label for="order">Ordernar por</label>
                        <el-select v-model="order.orderBy" @change="getPayments" id="order">
                            <el-option :key="0" label="Id" value="id" />
                            <el-option :key="1" label="Cliente" value="name" />
                            <el-option :key="1" label="Correo electrónico" value="email" />
                            <el-option :key="1" label="Teléfono" value="phone" />
                            <el-option :key="1" label="Método de pago" value="type" />
                            <el-option :key="1" label="Monto" value="amount" />
                            <el-option :key="1" label="Estatus" value="status" />
                            <el-option :key="1" label="Fecha" value="created_at" />
                        </el-select>
                    </el-col>
                    <el-col class="mb-5" :span="4">
                        <br>
                        <el-select v-model="order.order" @change="getPayments">
                            <el-option :key="0" label="Ascendente" value="ASC" />
                            <el-option :key="1" label="Descendente" value="DESC" />
                        </el-select>
                    </el-col>
                    <el-col :span="1">
                        <br>
                        <el-tooltip
                            class="box-item"
                            effect="dark"
                            content="Limpiar filtros"
                            placement="top"
                        >
                            <font-awesome-icon class="mt-2 pointer" :icon="['fas', 'filter-circle-xmark']" @click="resetFilters" />
                        </el-tooltip>
                    </el-col>
                    <el-col :span="24">
                        <el-table class="w-100" :data="payments" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="has-text-dark">
                            <!-- <el-table-column prop="date" label="Date">
                                <template #header>
        
                                </template>
                                <template #default="scope">
        
                                </template>
                            </el-table-column> -->
                            <el-table-column prop="id" label="#" width="70" align="center" />
                            <el-table-column prop="name">
                                <template #header>
                                    <el-input v-model="search.name" placeholder="Buscar Cliente" @input="getPayments" />
                                </template>
                            </el-table-column>
                            <el-table-column prop="email">
                                <template #header>
                                    <el-input v-model="search.email" placeholder="Buscar Correo electrónico" @input="getPayments" />
                                </template>
                            </el-table-column>
                            <el-table-column prop="phone">
                                <template #header>
                                    <el-input v-model="search.phone" placeholder="Buscar Teléfono" @input="getPayments" />
                                </template>
                            </el-table-column>
                            <el-table-column>
                                <template #header>
                                    <el-select v-model="search.type" placeholder="Método de pago" @change="getPayments">
                                        <el-option
                                            v-for="item in payment_methods"
                                            :key="item.value"
                                            :label="item.label"
                                            :value="item.value"
                                        />
                                    </el-select>
                                </template>
                                <template #default="scope">
                                    {{ scope.row.type == 'card' ? 'Tarjeta' : 'Efectivo' }}
                                </template>
                            </el-table-column>
                            <el-table-column label="Monto" width="130">
                                <template #default="scope">
                                    {{ formatCurrency(scope.row.amount) }}
                                </template>
                            </el-table-column>
                            <el-table-column>
                                <template #header>
                                    <el-select v-model="search.status" placeholder="Estatus" @change="getPayments">
                                        <el-option
                                            v-for="item in status"
                                            :key="item.value"
                                            :label="item.label"
                                            :value="item.value"
                                        />
                                    </el-select>
                                </template>
                                <template #default="scope">
                                    {{ verifyStatus(scope.row.status) }}
                                </template>
                            </el-table-column>
                            <el-table-column label="Fecha de compra">
                                <template #default="scope">
                                    {{ formatDate(scope.row.created_at) + ' - ' + formatTime(scope.row.created_at) }}
                                </template>
                            </el-table-column>
                            <el-table-column label="Acciones" width="110" align="center">
                                <!-- <template #default="scope">
                                    
                                </template> -->
                            </el-table-column>
                        </el-table>
                        <el-pagination
                            class="mt-4 w-100 has-text-center"
                            v-model:current-page="pagination.currentPage"
                            v-model:page-size="pagination.pageSize"
                            :page-sizes="[25, 50, 75, 100]"
                            layout="total, sizes, prev, pager, next"
                            :total="pagination.totalRows"
                            @size-change="handleSizeChange"
                            @current-change="handleCurrentChange"
                        />
                    </el-col>
                </el-row>
            </el-card>
        </el-col>
    </el-row>
    <Footer></Footer>
</template>

<script>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import MenuEvent from '../MenuEvent.vue';
import Submenu from '../Submenu.vue';
import Footer from '../Footer.vue';
import { dateEs, time } from '@/dateEs';

export default {
    components: {
        MenuEvent,
        Submenu,
        Footer,
    },
    data() {
        return {
            appUrl: window.location.origin,
            event: this.$page.props.event,
            payments: [],
            search: {
                name: '',
                email: '',
                phone: '',
                type: 'all',
                code: '',
                status: 'all'
            },
            pagination: {
                totalRows: 0,
                currentPage: 1,
                pageSize: 25,
            },
            order: {
                orderBy: 'id',
                order: 'DESC'
            },
            payment_methods: [{value: 'all', label: 'Todos los métodos de pago'},{value: 'oxxo', label: 'Efectivo'},{value: 'card', label: 'Tarjeta'}],
            status: [{value: 'all', label: 'Todos los estatus'},{value: 'expired', label: 'Expirado'},{value: 'payed', label: 'Pagado'},{value: 'pending', label: 'Pendiente'}]
        }
    },
    beforeMount() {
        this.getPayments();
    },
    mounted() {
        
    },
    created() {
        
    },
    methods: {
        async getPayments() {
            const response            = await apiClient('customer/reservations', 'POST', {event_id: this.event.id, search: this.search, pagination: this.pagination, order: this.order});
            this.payments             = response.data.payments;
            this.pagination.totalRows = response.data.count;
        },
        handleSizeChange(val) {
            // console.log(`${val} items per page`)
            this.getPayments();
        },
        handleCurrentChange(val) {
            // console.log(`current page: ${val}`)
            this.getPayments();
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }).format(value);
        },
        verifyStatus(status) {
            switch (status) {
                case 'expired':
                    return 'Expirado';
                case 'payed':
                    return 'Pagado';
                case 'pending':
                    return 'Pendiente';
            }
        },
        formatDate(_date) {
            return dateEs(_date, 1, '/');
        },
        formatTime(_time) {
            console.log(_time);
            return time(_time.substring(11, 16));
        },
        resetFilters() {
            this.search.name   = '';
            this.search.email  = '';
            this.search.phone  = '';
            this.search.type   = 'all';
            this.search.code   = '';
            this.search.status = 'all';
            this.pagination.totalRows   = 0;
            this.pagination.currentPage = 1;
            this.pagination.pageSize    = 25;
            this.order.orderBy = 'id';
            this.order.order   = 'DESC';
            this.getPayments();
        }
    }
}
</script>

<style scoped>
    
</style>