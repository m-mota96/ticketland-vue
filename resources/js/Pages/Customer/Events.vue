<template>
    <Menu></Menu>
    <div class="container">
        <el-row :gutter="50" class="mt-10">
            <el-col class="mb-5" :span="24">
                <h1 class="title has-text-dark">Listado de eventos</h1>
            </el-col>
            <el-col :xs="24" :sm="24" :md="12" :lg="18" :xl="18">
                <el-input placeholder="Buscar por nombre de evento" size="large" v-model="paramsGetEvents.search" @keyup="searchEvent" />
                <el-row class="mt-5" :gutter="20">
                    <el-col class="mb-5" :span="6">
                        <span class="subtitle is-6 has-text-success pointer" @click="changeStatus(1)"><b>ACTIVOS ({{ count.active }})</b></span>
                    </el-col>
                    <el-col class="mb-5" :span="6">
                        <span class="subtitle is-6 has-text-success pointer" @click="changeStatus(0)"><b>INACTIVOS ({{ count.inactive }})</b></span>
                    </el-col>
                    <el-col class="mb-5" :span="6">
                        <span class="subtitle is-6 has-text-success pointer" @click="changeStatus(2)"><b>PASADOS ({{ count.past }})</b></span>
                    </el-col>
                    <el-col class="mb-5" :span="6">
                        <span class="subtitle is-6 has-text-success pointer" @click="changeStatus()"><b>TODOS ({{ count.all }})</b></span>
                    </el-col>
                    <el-col :span="24">
                        <h2 class="title is-4 has-text-grey mt-5 text-center w-100" v-if="!events.length">Lo sentimos, no hay eventos con este filtro.</h2>
                        <el-card class="mb-5 p-0" v-for="(event, index) in events" :key="event.id" body-class="p-0">
                            <div>
                                <el-row class="p-0" :gutter="5">
                                    <el-col :span="6">
                                        <img v-if="!event.profile" class="w-100" src="/general/not_image.png" alt="{{ event.name }}">
                                        <img v-if="event.profile" class="w-100" src="/general/{{ event.profile.name }}" alt="{{ event.name }}">
                                    </el-col>
                                    <el-col class="pt-5 pl-5" :span="14">
                                        <h4 class="title is-4 has-text-dark mb-0"><b>{{ event.name }}</b></h4>
                                        <span class="has-text-dark mt-0">
                                            {{ 
                                                parseDate(event.event_dates[0].date, 1, ' ')
                                                + ' - ' +
                                                parseTime(event.event_dates[0].initial_time)
                                                + ' a ' +
                                                parseDate(event.event_dates[event.event_dates.length - 1].date, 1, ' ') 
                                                + ' - ' +
                                                parseTime(event.event_dates[event.event_dates.length - 1].final_time)
                                            }}
                                        </span>
                                        <div class="w-100 mt-3">
                                            <span class="has-text-grey">EDITAR</span>
                                            <span class="has-text-grey ml-6" v-if="event.sales == 0">ELIMINAR</span>
                                            <el-switch
                                                v-if="event.status == 1 || event.status == 0"
                                                size="large"
                                                v-model="event.status"
                                                :active-value="1"
                                                :inactive-value="0"
                                                class="ml-6"
                                                inline-prompt
                                                style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949"
                                                active-text="Activo"
                                                inactive-text="Inactivo"
                                                :before-change="beforeChange2"
                                            />
                                        </div>
                                    </el-col>
                                    <el-col class="pt-5 pr-5 text-right" :span="4">
                                        <h3 class="subtitle mb-0 is-3 w-100 text-right">
                                            <span class="has-text-blue-400 has-text-weight-light">{{ event.sales }}/</span><span class="has-text-blue-300 has-text-weight-light">{{ event.quantity_tickets }}</span>
                                        </h3>
                                        <h3 class="subtitle is-7 has-text-grey mt-1">BOLETOS RESERVADOS</h3>
                                    </el-col>
                                </el-row>
                            </div>
                        </el-card>
                    </el-col>
                    <el-col class="text-center is-justify-content-center is-flex mt-5" :span="24" v-if="events.length">
                        <div class="demo-pagination-block">
                            <el-pagination
                            v-model:current-page="paramsGetEvents.currentPage"
                            :page-size="paramsGetEvents.sizePage"
                            size="large"
                            background
                            layout="Total, Anterior, pager, Siguiente"
                            :total="count.all"
                            @current-change="handleCurrentChange"
                            />
                        </div>
                    </el-col>
                </el-row>
            </el-col>
            <el-col :xs="24" :sm="24" :md="12" :lg="6" :xl="6">
                <el-card class="p-4">
                    <template #header>
                        <div class="card-header is-justify-content-center is-flex p-0">
                            <span>OPCIONES</span>
                        </div>
                    </template>
                    <el-button class="w-100 mt-1" type="warning" size="large" @click="$refs.CreateEvent.showHideModal()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'plus']" /> Crear nuevo evento
                    </el-button>
                </el-card>
            </el-col>
        </el-row>
    </div>

    <CreateEvent ref="CreateEvent" />
</template>
  
<script>
import Menu from './Menu.vue';
import CreateEvent from './Modals/CreateEvent.vue';
import apiClient from '@/apiClient';
import { dateEs, time } from '@/dateEs';
  
export default {
    components: {
        //Aqui se agregan los componentes, en dado caso que quiereas usar, para separar código, yo separo los modales y aqui los agrego
        Menu,
        CreateEvent
    },
    data() {
        return {
            //Aquí se declaran las variables
            events: [],
            paramsGetEvents: {
                currentPage: 1,
                sizePage: 20,
                search: '',
                status: 0
            },
            count: {},
            parseDate: dateEs,
            parseTime: time
        }
    },
    beforeMount() {
        //Aqui se pueden mandar llamar metodos antes de que se monte el componente
    },
    mounted() {
        this.getevents();
    },
    created() {
        //Aqui se pueden mandar llamar métodos, cuando se crea el componente
    },
   
    methods: {
        // Aqui van los métodos
        async getevents() {
            const response = await apiClient('customer/events', 'POST', this.paramsGetEvents);
            this.events = response.data.events;
            // this.events.forEach(e => {
            //     switch (e.status) {
            //         case 1:
            //             e.status_aditional = true;
            //         break;
            //         case 0:
            //             e.status_aditional = false;
            //         break;
            //         default:
            //             e.status_aditional = e.status;
            //         break;
            //     }
            // });
            this.count  = response.data.count;
        },
        handleCurrentChange(val) {
            this.paramsGetEvents.currentPage = val;
            this.getevents();
        },
        changeStatus(status = null) {
            this.paramsGetEvents.status = status;
            this.getevents();
        },
        searchEvent() {
            if (this.paramsGetEvents.search.length > 3 || !this.paramsGetEvents.search) this.getevents()
        },
        parseStatus(status) {
            return (status == 1) ? true : false;
        },
        activeDiableEvent(index) {
            console.log(this.events[index].status_aditional);
            this.events[index].status_aditional = !this.events[index].status_aditional;
            console.log(this.events);
            // return !this.events[index].status;
        },
        beforeChange2() {
            console.log('ENTRE');
            return new Promise((resolve) => {
                setTimeout(() => {
                this.events[0].status = !this.events[0].status;
                // ElMessage.error('Switch failed')
                return resolve(true)
                }, 1000)
            })
        }
    }
}
</script>
  
<style>
    .w-100 {
        width: 100% !important;
    }
    .p-0 {
        padding: 0px !;
    }
    .pointer {
        cursor: pointer;
    }
    .has-text-blue-300 {
        color: #80bdff !important;
    }
    .has-text-blue-400 {
        color: #409cff !important;
    }
    input[type='text']:focus,
    input[type='email']:focus,
    input[type='url']:focus,
    input[type='password']:focus,
    input[type='number']:focus,
    input[type='date']:focus,
    input[type='datetime-local']:focus,
    input[type='month']:focus,
    input[type='search']:focus,
    input[type='tel']:focus,
    input[type='time']:focus,
    input[type='week']:focus,
    input[multiple]:focus,
    /* textarea:focus, */
    select:focus {
        box-shadow: unset !important;
    }
</style>