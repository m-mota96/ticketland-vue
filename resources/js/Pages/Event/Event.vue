<script setup lang="js">
import { ref, onMounted } from 'vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { dateEs, time } from '@/dateEs';
import Tickets from './Tickets.vue';

const { event_id, name_ticket } = defineProps({
    event_id: {
        type: Number,
        required: true
    },
    name_ticket: {
        type: String,
        required: false,
    }
});

onMounted(() => {
    getInformationEvent();
});

const appUrl           = window.location.origin;
const gutterValue      = window.innerWidth <= 768 ? 0 : 20;
const gutterValue2     = window.innerWidth < 768 ? 0 : 80;
const ticketsRef       = ref(null);
const event            = ref({});
const tickets          = ref([]);
const viewFormTickets = ref(false);
const loading          = false;
const data             = ref({
    tickets: [],
    ticketsReserved: [],
    selected: 0,
    subtotal: 0,
    discount: 0,
    discountAmount: 0,
    total: 0,
    commission: 0,
});

const getInformationEvent = async () => {
    const response = await apiClient(`event/${event_id}`, 'GET');
    if (response.error) {
        showNotification('¡Error!', response.msj, 'error', 8000);
        return false;
    }
    event.value   = response.data.event;
    tickets.value = response.data.tickets;
    tickets.value.forEach(t => {
        if (t.questions.length) { // Verificamos si ese boletos tiene campos adicionales agregados.
            t.questions.forEach(q => {
                if (q.type === 'select') {
                    // Si el campo es de tipo select convertimos el atring separado por comas a array.
                    q.options = q.options.split(',');
                }
            });
        }
    });
};

// Calcula el total a pagar por el cliente.
const totals = () => {
    data.value.subtotal = 0;
    data.value.total    = 0;
    tickets.value.forEach(t => {
        const price = t.promotion
            ? parseInt(t.priceDiscount) // Si el boleto tiene descuento tomamos el precio con descuento.
            : parseInt(t.price) // Si no tiene ningún descuento tomamos el precio base.
        
        data.value.subtotal = data.value.subtotal + (price * t.quantity_to_purchase);
    });

    data.value.total = data.value.subtotal;
};

// Hace el cálculo de los boletos que quieren comprar
const calculate = (val, oldVal, t) => {
    const quantity = totalTickets();
    if (quantity > 10) {
        // Evitar que compren mas de 10 boletos en total.
        t.quantity_to_purchase = t.quantity_to_purchase - 1;
    }
    if ((data.value.selected + (val - oldVal)) > 10) {
        // Evitar que elijan mas de 10 veces el boleto.
        return false;
    }
    data.value.selected = data.value.selected + (val - oldVal);
    totals();
};

// Calcula el total de boletos que va a comprar el cliente
const totalTickets = () => {
    let total = 0;
    tickets.value.forEach(t => {
        total = total + t.quantity_to_purchase;
    });
    return total;
};

const loadInfo = () => {
    if (!data.value.selected) {
        showNotification('¡Atención!', 'Debes tener seleccionado al menos 1 boleto', 'warning', 6500);
        return;
    }

    // Obtenemos solo los boletos que quieren comprar (quantity_to_purchase > 0).
    const ticketsFiltered = tickets.value.filter(t => t.quantity_to_purchase !== 0);
    ticketsRef.value?.loadForm(event_id, ticketsFiltered, event.value.payment_methods);
    // this.verifyCodes();
    // viewFormTickets.value = true;
    // this.$nextTick(() => {
    //     // Espera a que el DOM se actualice
    //     if (this.$refs.dataOrder) {
    //         this.$refs.dataOrder.$el.scrollIntoView({ behavior: 'smooth' });
    //     }
    // });
};

const formatDate = (_date) => {
    return dateEs(_date, 1, ' ');
};
const formatTime = (_time) => {
    return time(_time);
};
const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
};
const isNumber = (evt) => {
    const charCode = evt.which ? evt.which : evt.keyCode;
    if (charCode < 48 || charCode > 57) {
        evt.preventDefault();
    }
};
</script>

<template>
    <el-row class="container-fluid has-background-white-ter" ref="dataPrincipal">
        <el-col :xs="0" :sm="0" :md="0" :lg="24" :xl="24" class="row content-head p-r">
            <div class="p-a opacy w-100">
                <img v-if="event.profile" class="h-100 w-100 img-transparent" :src="appUrl+'/events/images/'+event.profile.name" :alt="event.name">
                <img v-if="!event.profile" class="h-100 w-100 img-transparent" :src="appUrl+'/general/slide_ticketland.png'" :alt="event.name">
            </div>
        </el-col>
        <el-col :xs="0" :sm="0" :md="0" :lg="{span: 14, offset: 5}" :xl="{span: 14, offset: 5}" class="p-r">
            <img  v-if="event.profile" class="p-a img-event p-0 w-100" :src="appUrl+'/events/images/'+event.profile.name" :alt="event.name">
            <img  v-if="!event.profile" class="p-a img-event p-0 w-100" :src="appUrl+'/general/slide_ticketland.png'" :alt="event.name">
        </el-col>
        <el-col :xs="24" :sm="24" :md="24" :lg="0" :xl="0">
            <img  v-if="event.profile" class="shadow w-100" :src="appUrl+'/events/images/'+event.profile.name" :alt="event.name">
            <img  v-if="!event.profile" class="shadow w-100" :src="appUrl+'/general/slide_ticketland.png'" :alt="event.name">
        </el-col>
    </el-row>
    <el-row class="container-fluid pt-6 pb-6 has-background-white-ter padding">
        <el-col :xs="24" :sm="24" :md="24" :lg="{span: 14, offset: 5}" :xl="{span: 14, offset: 5}">
            <el-row>
                <el-col :xs="24" :sm="24" :md="18" :lg="18" :xl="18">
                    <h1 class="title is-1 mb-0 bold has-text-black">{{ event.name }}</h1>
                    <div class="mt-3 has-text-black">
                        <b><font-awesome-icon :icon="['fas', 'calendar-days']" /> Fechas:</b>
                        <p v-for="(d, index) in event.event_dates" :key="index"><b>Día {{ index + 1 }}: </b>{{ formatDate(d.date) }} - {{ formatTime(d.initial_time) }} a {{ formatTime(d.final_time) }}</p>
                    </div>
                    <p class="bold has-text-link mt-6 mb-5 pointer" @click="moreInfo"><font-awesome-icon :icon="['fas', 'plus']" /> Más información del evento</p>
                </el-col>
                <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
                    <h6 class="subtitle is-6 bold has-text-black mb-5">COMPARTE ESTE EVENTO</h6>
                    <a class="subtitle is-5 pt-2 pb-2 pl-3 pr-3 bg-green-500 has-text-white rounded-circle" :href="`https://api.whatsapp.com/send?text=Voy a asistir al evento ${event.name}: ${appUrl}/evento/${event.url}`" target="_blank"><font-awesome-icon :icon="['fab', 'whatsapp']" /></a>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    <el-row class="container-fluid has-background-white pt-6 padding" v-if="!viewFormTickets" ref="dataTickets">
        <el-col :xs="24" :sm="24" :md="24" :lg="{span: 14, offset: 5}" :xl="{span: 14, offset: 5}">
            <el-row :gutter="gutterValue">
                <el-col :span="24" class="mb-6">
                    <h2 class="title is-2 mb-0 bold has-text-black mb-2">Selecciona tus boletos</h2>
                    <h3 class="subtitle is-4 mb-0 has-text-grey">Máximo 10 boletos por orden</h3>
                </el-col>
                <el-col :span="24">
                    <el-row class="mb-6" :gutter="gutterValue2" v-for="(t, index) in tickets" :key="index">
                        <div class="w-100" :ref="t.name" >
                            <el-col :span="24">
                                <el-row :class="{'card has-background-light p-5': t.name == name_ticket}">
                                    <el-col class="mb-3" :sm="24" :md="16" :lg="18" :xl="18">
                                            <h4 class="subtitle is-4 has-text-dark mb-0" v-if="!t.promotion">{{ t.name }}</h4>
                                            <el-badge :value="`${t.promotion}% Descuento`" class="item" :offset="[10, 5]" v-if="t.promotion">
                                                <h4 class="subtitle is-4 has-text-dark mb-0">{{ t.name }}</h4>
                                            </el-badge>
                                            <h5 class="subtitle is-6 has-text-gray mb-0" v-if="t.promotion"><del>{{ formatCurrency(t.price) }} MXN</del></h5>
                                            <h5 class="subtitle is-5 has-text-link mb-1" v-if="t.promotion">{{ formatCurrency(t.priceDiscount) }} MXN</h5>
                                            <h5 class="subtitle is-5 has-text-link mb-1" v-if="!t.promotion">{{ formatCurrency(t.price) }} MXN</h5>
                                            <p class="has-text-black justify mb-0 multiline-text" v-if="t.description">{{ t.description }}</p>
                                    </el-col>
                                    <el-col class="mb-6" :xs="24" :sm="24" :md="8" :lg="6" :xl="6">
                                        <el-input-number
                                            class="w-100"
                                            v-model="t.quantity_to_purchase"
                                            size="large"
                                            :min="0"
                                            :max="10"
                                            @change="(val, oldVal) => calculate(val, oldVal, t)"
                                            :controls="true"
                                        />
                                    </el-col>
                                </el-row>
                            </el-col>
                        </div>
                    </el-row>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    <el-row class="has-background-white pb-5 b-t b-b pt-6 pb-5 padding" v-if="!viewFormTickets">
        <el-col :xs="24" :sm="24" :md="24" :lg="{span: 14, offset: 5}" :xl="{span: 14, offset: 5}">
            <el-row :gutter="gutterValue2">
                <el-col :xs="24" :sm="24" :md="16" :lg="15" :xl="18" class="mb-3">
                    <h4 class="subtitle is-4 has-text-dark mb-2" v-if="data.selected != 1">Tienes <b>{{ data.selected }}</b> boletos seleccionados</h4>
                    <h4 class="subtitle is-4 has-text-dark mb-2" v-if="data.selected == 1">Tienes <b>{{ data.selected }}</b> boleto seleccionado</h4>
                    <h4 class="title is-4 has-text-link">{{ formatCurrency(data.subtotal) }} MXN <span class="subtitle is-6 has-text-grey" v-if="event.model_payment == 'separated'"> + CARGOS</span></h4>
                </el-col>
                <el-col :xs="24" :sm="24" :md="8" :lg="9" :xl="6">
                    <el-button class="bold w-100" type="success" size="large" @click="loadInfo">
                        <font-awesome-icon :icon="['fas', 'money-check-dollar']" />&nbsp;&nbsp;Comprar boletos
                    </el-button>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    <Tickets ref="ticketsRef" v-model="viewFormTickets" />
    <el-row class="container-fluid has-background-white pb-6 pt-6 padding" ref="moreInfo">
        <el-col :xs="24" :sm="24" :md="24" :lg="{span: 14, offset: 5}" :xl="{span: 14, offset: 5}">
            <el-row :gutter="gutterValue2">
                <el-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14" class="mb-3">
                    <h3 class="subtitle is-3 has-text-grey mb-2">Sobre el evento</h3>
                    <p class="justify has-text-black multiline-text" v-if="event.description">{{ event.description }}</p>
                    <div v-if="event.location">
                        <h3 class="subtitle is-3 has-text-grey mb-2 mt-5">Lugar del evento</h3>
                        <h5 class="title is-5 has-text-dark mb-1">{{ event.location.name }}</h5>
                        <p class="has-text-dark mb-4">{{ event.location.address }}</p>
                        <iframe v-if="event.location.iframe" :src="event.location.iframe" allowfullscreen width="100%" height="400vh"></iframe>
                    </div>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10" class="pr-0 mr-0">
                    <h3 class="subtitle is-3 has-text-grey mb-2">Contacta al organizador</h3>
                    <p class="mb-1 has-text-black" v-if="event.email"><font-awesome-icon class="bold" :icon="['fas', 'envelope']" /> {{ event.email }}</p>
                    <p class="mb-1 has-text-black" v-if="event.phone"><font-awesome-icon class="bold" :icon="['fas', 'phone-flip']" /> {{ event.phone }}</p>
                    <p class="mb-1" v-if="event.twitter"><a class="has-text-black links" :href="`https://x.com/${event.twitter}`" target="_blank"><font-awesome-icon :icon="['fab', 'x-twitter']" /> X (Twitter)</a></p>
                    <p class="mb-1" v-if="event.facebook"><a class="has-text-black links" :href="event.facebook" target="_blank"><font-awesome-icon class="bold" :icon="['fab', 'facebook-f']" /> Facebook</a></p>
                    <p class="mb-1" v-if="event.instagram"><a class="has-text-black links" :href="event.instagram" target="_blank"><font-awesome-icon class="bold" :icon="['fab', 'instagram']" /> Instagram</a></p>
                    <p class="mb-1" v-if="event.website"><a class="has-text-black links" :href="event.website" target="_blank"><font-awesome-icon class="bold" :icon="['fas', 'link']" /> {{ event.website }}</a></p>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    <el-row class="has-background-dark" style="height: 15vh;">
        
    </el-row>
</template>

<style scoped>
.example-showcase .el-loading-mask {
    z-index: 999;
}
.b-t {
    border-top: 1px solid #eeeceb;
}
.b-b {
    border-bottom: 1px solid #eeeceb;
}
::v-deep(.el-input-number__decrease), ::v-deep(.el-input-number__increase) {
    background-color: #007bff !important;
    color: white !important;
    font-weight: bold !important;
}
/* ::v-deep(.el-input-number) {
    border: 1px solid #007bff;
    border-radius: 6px !important;
} */
.rounded-circle {
    border-radius: 30% !important;
}
.w-100 {
    width: 100% !important;
}
.content-head {
    height: 30rem;
}
.opacy {
    overflow: hidden;
    height: 90%;
    background: rgba(105, 120, 134, 0.2);
}
.img-transparent {
    filter: blur(30px);
    object-fit: cover;
    object-position: center !important;
    opacity: 1;
    transition: opacity 0.3s ease 0s;
    height: 100%;
}
.p-r {
    position: relative;
}
.p-a {
    position: absolute;
}
.img-event {
    border-radius: 3px;
    bottom: 0;
    margin-top: 5vh !important;
    height: 430px;
    /* width: 50%; */
    object-fit: cover;
    object-position: center;
    -webkit-box-shadow: 0px 0px 15px -2px rgba(0,0,0,0.75);
    -moz-box-shadow: 0px 0px 15px -2px rgba(0,0,0,0.75);
    box-shadow: 0px 0px 15px -2px rgba(0,0,0,0.75);
}
.shadow {
    border-radius: 3px;
    -webkit-box-shadow: 0px 0px 15px -2px rgba(0,0,0,0.75);
    -moz-box-shadow: 0px 0px 15px -2px rgba(0,0,0,0.75);
    box-shadow: 0px 0px 15px -2px rgba(0,0,0,0.75);
}
.bold {
    font-weight: bold;
}
.pointer {
    cursor: pointer;
}
body {
    background-color: #f0f1f3;
}
.d-i {
    display: inline;
}
.footer-top {
    background-color: #4D5D6C;
}
.footer-bottom {
    background-color: #354350;
}
.map {
    height: 300px;
}
.hidden {
    display: none;
}
#WindowLoad
{
    position:fixed;
    top:0px;
    left:0px;
    z-index:3200;
    filter:alpha(opacity=65);
    -moz-opacity:65;
    opacity:0.9;
    background:#999;
}
.card-tickets {
    -webkit-box-shadow: 0px 0px 15px -2px rgba(0,0,0,0.75);
    -moz-box-shadow: 0px 0px 15px -2px rgba(0,0,0,0.75);
    box-shadow: 0px 0px 15px -2px rgba(0,0,0,0.75);
}
.cover {
    object-fit: cover;
}
.to-uppercase {
    text-transform: uppercase;
}
.heigth-tickets {
    max-height: 30vh;
    overflow-x: auto;
}
@media only screen and (max-width: 2500px) and (min-width: 1700px) {
    .content-head {
        height: 35rem !important;
    }
    .img-event {
        height: 500px !important;
    }
}
@media only screen and (max-width: 1024px) and (min-width: 501px) {
    .padding {
        padding-left: 5vh;
        padding-right: 5vh;
    }
}
@media only screen and (max-width: 500px) and (min-width: 200px) {
    .content-head {
        height: 10rem !important;
    }
    .img-event {
        height: unset !important;
        bottom: unset !important;
    }
    .row {
        margin-left: auto !important;
    }
    .location {
        padding-left: 0px !important;
    }
    #modalSale .col-xl-6 {
        padding-left: 0px !important;
    }
    #modalSale .col-xl-6 .col-xl-4 {
        padding-left: 0px !important;
        margin-bottom: 2vh !important;
    }
    .badge {
        font-size: 0.6rem;
    }
    .padding {
        padding-left: 1vh;
        padding-right: 1vh;
    }
}
.multiline-text {
    white-space: pre-line;
}
:global(input:-webkit-autofill) {
    box-shadow: 0 0 0px 1000px white inset !important;
    -webkit-text-fill-color: #000 !important;
}
.error-phone {
    border: 1px solid red !important;
}
</style>