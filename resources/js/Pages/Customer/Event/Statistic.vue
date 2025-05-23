<template>
    <MenuEvent></MenuEvent>
    <Submenu :dadEvent="event"></Submenu>
    <el-row :gutter="20" class="wrapper">
        <el-col :span="14" :offset="5">
            <el-card>
                <el-row :gutter="20">
                    <el-col :span="4">
                        <label for="year" class="bold">Año:</label>
                        <el-select v-model="currentYear" placeholder="Elije una opción" @change="getStatistics" id="year">
                            <el-option v-for="y in years" :key="y" :value="y" :label="y" />
                        </el-select>
                    </el-col>
                    <el-col :span="4">
                        <label for="month" class="bold">Mes:</label>
                        <el-select v-model="currentMonth" placeholder="Elije una opción" @change="getStatistics" id="month">
                            <el-option v-for="m in months" :key="m.value" :label="m.label" :value="m.value" />
                        </el-select>
                    </el-col>
                    <el-col :span="24" class="mb-6">
                        <highcharts :options="chartOptions" />
                    </el-col>
                    <el-col :span="12" :offset="2">
                        <el-row class="btn-totalSales has-text-centered">
                            <el-col :span="8" class="pt-4 pb-4">
                                <h1 class="title is-2 bold text-btn-totalSales mb-1">{{ totalNotDiscount }}</h1>
                                <p class="text-btn-totalSales">S/Descuento</p>
                            </el-col>
                            <el-col :span="8" class="pt-4 pb-4">
                                <h1 class="title is-2 bold text-btn-totalSales mb-1">{{ totalDiscount }}</h1>
                                <p class="text-btn-totalSales">C/Descuento</p>
                            </el-col>
                            <el-col :span="8" class="pt-4 pb-4">
                                <h1 class="title is-2 bold text-btn-totalSales mb-1">{{ total }}</h1>
                                <p class="text-btn-totalSales">Total</p>
                            </el-col>
                        </el-row>
                    </el-col>
                    <el-col :span="4" class="has-text-centered">
                        <div class="btn-totalPending pt-4 pb-4 ">
                            <h1 class="title is-2 bold text-btn-totalPending mb-1">{{ totalPending }}</h1>
                            <p class="text-btn-totalPending">Pendientes</p>
                        </div>
                    </el-col>
                    <el-col :span="4" class="has-text-centered">
                        <div class="btn-totalExpired pt-4 pb-4">
                            <h1 class="title is-2 bold text-btn-totalExpired mb-1">{{ totalExpired }}</h1>
                            <p class="text-btn-totalExpired">Expirados</p>
                        </div>
                    </el-col>
                    <el-col :span="24" class="mt-6 has-text-centered">
                        <h4 class="title is-4 has-text-black mb-3">Ingresos totales</h4>
                    </el-col>
                    <el-col :span="24" class="mb-6">
                        <el-table class="w-100" :data="amounts" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="has-text-dark">
                            <el-table-column prop="type" label="MÉTODO DE PAGO" />
                            <el-table-column label="VENTAS TOTALES">
                                <template #default="scope">
                                    {{ formatCurrency(scope.row.total) }} MXN
                                </template>
                            </el-table-column>
                            <el-table-column label="CARGO POR SERVICIO" v-if="event.model_payment == 'including'">
                                <template #default="scope">
                                    {{ formatCurrency(Math.round(scope.row.total * .12)) }} MXN
                                </template>
                            </el-table-column>
                            <el-table-column label="INGREO NETO" v-if="event.model_payment == 'including'">
                                <template #default="scope">
                                    {{ formatCurrency(scope.row.total - Math.round(scope.row.total * .12)) }} MXN
                                </template>
                            </el-table-column>
                            <!-- <el-table-column prop="name" label="Name" width="180" />
                            <el-table-column prop="address" label="Address" /> -->
                        </el-table>
                    </el-col>
                    <el-col :span="24" class="mb-6">
                        <h3 class="subtitle is-3 has-text-black">Ingreso total neto: <span class="has-text-success bold">{{ formatCurrency(totalAmount) }} MXN</span></h3>
                    </el-col>
                </el-row>
            </el-card>
        </el-col>
    </el-row>
    <Footer></Footer>
</template>

<script>
import MenuEvent from '../MenuEvent.vue';
import Submenu from '../Submenu.vue';
import Footer from '../Footer.vue';
import apiClient from '@/apiClient';
import HighchartsVue from 'highcharts-vue';

export default {
    components: {
        MenuEvent,
        Submenu,
        Footer,
        highcharts: HighchartsVue.component
    },
    data() {
        return {
            currentYear: new Date().getFullYear(),
            years: [new Date().getFullYear(), new Date().getFullYear() - 1],
            currentMonth: new Date().getMonth() + 1,
            event: this.$page.props.event,
            chartOptions: {},
            months: [
                {value: 1, label: 'Enero'},
                {value: 2, label: 'Febrero'},
                {value: 3, label: 'Marzo'},
                {value: 4, label: 'Abril'},
                {value: 5, label: 'Mayo'},
                {value: 6, label: 'Junio'},
                {value: 7, label: 'Julio'},
                {value: 8, label: 'Agosto'},
                {value: 9, label: 'Septiembre'},
                {value: 10, label: 'Octubre'},
                {value: 11, label: 'Noviembre'},
                {value: 12, label: 'Diciembre'}
            ],
            totalDiscount: 0,
            totalNotDiscount: 0,
            total: 0,
            totalPending: 0,
            totalExpired: 0,
            amounts: [],
            totalAmount: 0
        }
    },
    beforeMount() {
        this.getStatistics();
    },
    mounted() {

    },
    methods: {
        async getStatistics() {
            const response = await apiClient(`customer/statistics`, 'GET', {event_id: this.event.id, year: this.currentYear, month: this.currentMonth});
            this.chart(response.data.sales, response.data.pending, response.data.expired);
            this.stats(response.data);
            this.amounts     = response.data.amounts;
            this.totalAmount = 0;
            for (let i = 0; i < response.data.amounts.length; i++) {
                if (this.event.model_payment == 'separated') {
                    this.totalAmount = this.totalAmount + response.data.amounts[i].total;
                } else {
                    this.totalAmount = this.totalAmount + (response.data.amounts[i].total - Math.round(response.data.amounts[i].total * .12));
                }
            }
        },
        stats(data) {
            this.totalDiscount    = data.ticketsDiscount;
            this.totalNotDiscount = data.ticketsNotDiscount;
            this.total            = data.ticketsDiscount + data.ticketsNotDiscount;
            this.totalPending     = data.ticketsPending;
            this.totalExpired     = data.ticketsExpired;
        },
        chart(sales, pending, expired) {
            const dates = this.getDaysInMonth(this.currentMonth, this.currentYear);
            
            this.chartOptions = {
                title: {
                    text: 'Historial de ventas'
                },
                xAxis: {
                    type: 'date',
                    categories: dates,
                    title: {
                        text: 'Día del mes'
                    },
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Boletos reservados por día'
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                        '<td style="padding:0"><b>{point.y} </b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [
                    {
                        name: 'Boletos pagados',
                        data: Object.values(sales),
                        colorByPoint: false,
                        color: '#22c7bf'
                    },
                    {
                        name: 'Boletos pendientes',
                        data: Object.values(pending),
                        colorByPoint: false,
                        color: '#ffa800'
                    },
                    {
                        name: 'Boletos expirados',
                        data: Object.values(expired),
                        colorByPoint: false,
                        color: '#f64e60'
                    }
                ]
            };
        },
        getDaysInMonth(month, year) {
            const days = [];
            const totalDays = new Date(year, month, 0).getDate(); // día 0 del siguiente mes

            for (let day = 1; day <= totalDays; day++) {
                const dd   = String(day).padStart(2, '0');
                const mm   = String(month).padStart(2, '0');
                const yyyy = String(year);
                days.push(`${dd}/${mm}/${yyyy}`);
            }

            return days;
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }).format(value);
        },
    }
}
</script>

<style scoped>
.btn-totalSales {
    border-radius: 1.25rem !important;
    background-color: #C9F7F5 !important;
}
.text-btn-totalSales {
    color: #22c7bf;
}
.btn-totalPending {
    border-radius: 1.25rem !important;
    background-color: #FFF4DE !important;
}
.text-btn-totalPending {
    color: #ffa800;
}
.btn-totalExpired {
    border-radius: 1.25rem !important;
    background-color: #FFE2E5 !important;
}
.text-btn-totalExpired {
    color: #f64e60;
}
</style>