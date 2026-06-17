<template>
    <MenuEvent></MenuEvent>
    <Submenu :dadEvent="event"></Submenu>
    <el-row class="wrapper">
        <el-col :span="24">
            <el-card class="w-100 pt-2 pb-5">
                <el-row :gutter="20">
                    <el-col :span="24" class="pl-5">
                        <h3 class="title is-3 !text-blue-500">
                            <el-tooltip
                                class="box-item"
                                effect="dark"
                                content="Regresar a boletos"
                                placement="bottom"
                            >
                                <Link class="!text-blue-500" :href="route('cliente.boletos', event.id)">
                                    <font-awesome-icon class="mr-2" :icon="['fas', 'arrow-left']" />
                                </Link>
                            </el-tooltip>
                            {{ ticket.name }}
                        </h3>
                    </el-col>
                    <el-col :span="6">
                        <!-- <br>
                        <el-button  type="success" @click="downloadReservations" :loading="loadingFile">
                            <font-awesome-icon class="mr-2" :icon="['fas', 'file-excel']" />
                            Generar reporte de reservaciones
                        </el-button> -->
                    </el-col>
                    <el-col class="mb-5" :span="4" :offset="9">
                        <label for="order">Ordernar por</label>
                        <el-select v-model="order.orderBy" @change="getBuyers" id="order">
                            <el-option :key="1" label="Cliente" value="name" />
                            <el-option :key="1" label="Correo electrónico" value="email" />
                            <el-option :key="1" label="Teléfono" value="phone" />
                            <el-option :key="1" label="Fecha de compra" value="created_at" />
                        </el-select>
                    </el-col>
                    <el-col class="mb-5" :span="4">
                        <br>
                        <el-select v-model="order.order" @change="getBuyers">
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
                        <el-table class="w-100" v-loading="loadingTable" :data="buyers" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="has-text-dark">
                            <!-- <el-table-column prop="date" label="Date">
                                <template #header>
        
                                </template>
                                <template #default="scope">
        
                                </template>
                            </el-table-column> -->
                            <el-table-column prop="id" label="#" width="70" align="center" />
                            <el-table-column prop="name" min-width="200">
                                <template #header>
                                    <el-input v-model="search.name" placeholder="Buscar Cliente" @input="getBuyers" clearable />
                                </template>
                            </el-table-column>
                            <el-table-column prop="email" min-width="200">
                                <template #header>
                                    <el-input v-model="search.email" placeholder="Buscar Correo electrónico" @input="getBuyers" clearable />
                                </template>
                            </el-table-column>
                            <el-table-column prop="phone" min-width="150">
                                <template #header>
                                    <el-input v-model="search.phone" placeholder="Buscar Teléfono" @input="getBuyers" clearable />
                                </template>
                            </el-table-column>
                            <el-table-column label="Precio" width="130" align="center">
                                <template #default="scope">
                                    {{ formatCurrency(scope.row.price) }}
                                </template>
                            </el-table-column>
                            <el-table-column label="Descuento del boleto" width="130" align="center">
                                <template #default="scope">
                                    {{ scope.row.promotion ? scope.row.promotion+'%' : 'N/A' }}
                                </template>
                            </el-table-column>
                            <el-table-column label="Cupón de descuento" width="150" align="center">
                                <template #default="scope">
                                    {{ scope.row.code_name ? scope.row.code_name : 'N/A' }}
                                </template>
                            </el-table-column>
                            <el-table-column width="130" align="center">
                                <template #header>
                                    Descuento<br>del cupón
                                </template>
                                <template #default="scope">
                                    {{ scope.row.code_discount ? scope.row.code_discount+'%' : '0%' }}
                                </template>
                            </el-table-column>
                            <el-table-column label="Total" width="130" align="center">
                                <template #default="scope">
                                    {{ formatCurrency(calculateTotal(scope.row)) }}
                                </template>
                            </el-table-column>
                            <el-table-column label="Fecha de compra" align="center" min-width="150">
                                <template #default="scope">
                                    {{ formatDate(scope.row.created_at) }}<br>{{ formatTime(scope.row.created_at) }}
                                </template>
                            </el-table-column>
                            <!-- <el-table-column label="Acciones" width="130" align="center">
                                <template #default="scope">
                                    <el-button-group>
                                        <el-tooltip
                                            class="box-item"
                                            effect="dark"
                                            :content="scope.row.status == 'payed' ? 'Reenviar boletos' : 'Reenviar ficha de pago'"
                                            placement="top"
                                            v-if="scope.row.status !== 'expired'"
                                        >
                                            <el-button class="pl-2 pr-2" type="primary" @click="resendEmail(scope.row.id, scope.row.email, scope.row.status)">
                                                <font-awesome-icon :icon="['fas', 'share']" />
                                            </el-button>
                                        </el-tooltip>
                                        <el-tooltip
                                            class="box-item"
                                            effect="dark"
                                            content="Ver boletos de la orden"
                                            placement="top"
                                            v-if="scope.row.status == 'payed' || scope.row.status == 'pending'"
                                        >
                                            <el-button class="pl-2 pr-2" type="success" @click="$refs.ViewTickets.showTickets(scope.row.accesses)">
                                                <font-awesome-icon :icon="['fas', 'eye']" />
                                            </el-button>
                                        </el-tooltip>
                                        <el-tooltip
                                            class="box-item"
                                            effect="dark"
                                            content="Descargar boletos"
                                            placement="top"
                                            v-if="scope.row.status == 'payed'"
                                        >
                                            <el-button class="pl-2 pr-2" color="#626aef" @click="downloadTickets(scope.row.id)">
                                                <font-awesome-icon :icon="['fas', 'download']" />
                                            </el-button>
                                        </el-tooltip>
                                    </el-button-group>
                                </template>
                            </el-table-column> -->
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
    <Footer :dataExpand="true"></Footer>
</template>

<script>
import apiClient from '@/apiClient';
import MenuEvent from '../MenuEvent.vue';
import Submenu from '../Submenu.vue';
import Footer from '../Footer.vue';
import { dateEs, time } from '@/dateEs';
import { Link } from '@inertiajs/vue3';

export default {
    components: {
        MenuEvent,
        Submenu,
        Footer,
        Link
    },
    data() {
        return {
            appUrl: window.location.origin,
            event: this.$page.props.event,
            ticket: this.$page.props.ticket,
            buyers: [],
            loading: false,
            loadingTable: false,
            loadingFile: false,
            search: {
                name: '',
                email: '',
                phone: '',
            },
            pagination: {
                totalRows: 0,
                currentPage: 1,
                pageSize: 25,
            },
            order: {
                orderBy: 'created_at',
                order: 'DESC'
            },
        }
    },
    beforeMount() {
        this.getBuyers();
    },
    mounted() {
        
    },
    created() {
        
    },
    methods: {
        async getBuyers() {
            this.loadingTable         = true;
            const response            = await apiClient('customer/buyers', 'POST', {event_id: this.event.id, ticket_id: this.ticket.id, search: this.search, pagination: this.pagination, order: this.order});
            this.loadingTable         = false;
            this.buyers               = response.data.buyers;
            this.pagination.totalRows = response.data.count;
        },
        calculateTotal(data) {
            if (!data.promotion && !data.code_discount) {
                return data.price;
            }
            if (data.promotion) {
                return data.price - Math.round(data.price * (data.promotion / 100));
            }
            if (data.code_discount) {
                return data.price - Math.round(data.price * (data.code_discount / 100));
            }
        },
        handleSizeChange(val) {
            // console.log(`${val} items per page`)
            this.getBuyers();
        },
        handleCurrentChange(val) {
            // console.log(`current page: ${val}`)
            this.getBuyers();
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
        formatTime(_time) {
            return time(_time.substring(11, 16));
        },
        resetFilters() {
            this.search.name   = '';
            this.search.email  = '';
            this.search.phone  = '';
            this.pagination.totalRows   = 0;
            this.pagination.currentPage = 1;
            this.pagination.pageSize    = 25;
            this.order.orderBy = 'created_at';
            this.order.order   = 'DESC';
            this.getBuyers();
        }
    }
}
</script>

<style scoped>
    
</style>