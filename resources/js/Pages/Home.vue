<script setup>
import apiClient from '@/apiClient';
import { ref, onMounted } from 'vue';

const appUrl = ref(window.location.origin);
const events = ref([]);
const months = [
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Septiembre',
    'Octubre',
    'Noviembre',
    'Diciembre'
];
const monthsAbrev = [
    'Ene',
    'Feb',
    'Mar',
    'Abr',
    'May',
    'Jun',
    'Jul',
    'Ago',
    'Sep',
    'Oct',
    'Nov',
    'Dic'
];

onMounted(() => {
    getEvents();
});

const getEvents = async () => {
    const response = await apiClient('eventsPublic');
    if (!response.errror) {
        events.value = response.data;
    }
};

const viewDates = (dates) => {
    let txt = '';
    switch (dates.length) {
        case 1:
            txt = dates[0].date.substring(8, 10) + ' - ' + months[parseInt(dates[0].date.substring(5, 7)) - 1] + ' - ' + dates[0].date.substring(0, 4);
            break;
        case 2:
            // Si son 2 fechas del mismo año
            txt = dates[0].date.substring(8, 10) + ' y ' + dates[dates.length - 1].date.substring(8, 10) + ' - '+months[parseInt(dates[0].date.substring(5, 7)) - 1] + ' - ' + dates[0].date.substring(0, 4);
            // Si son 2 fechas de distinto año (Ej. 31/12/2021 y 01/Ene/2026)
            if (dates[0].date.substring(0, 4) !== dates[dates.length - 1].date.substring(0, 4)) {
                txt = dates[0].date.substring(8, 10) + ' - ' + monthsAbrev[parseInt(dates[0].date.substring(5, 7)) - 1] + ' - ' + dates[0].date.substring(0, 4);
                txt += ' y ';
                txt += dates[dates.length - 1].date.substring(8, 10) + ' - ' + monthsAbrev[parseInt(dates[dates.length - 1].date.substring(5, 7)) - 1] + ' - ' + dates[dates.length - 1].date.substring(0, 4);
            }
            break;
        default:
            txt = '';
            break;
    }
    return txt;
};
</script>

<template>
    <div class="bg w-100">
    <nav class="navbar is-fixed-top pt-2 pb-2 pl-5 pr-5" style="background-color: rgba(33, 38, 43, 0.7);">
        <div class="navbar-brand">
            <a class="navbar-item" href="">
                <img src="/general/ticketland.png" alt="Ticketland" class="w-100">
                <span class="has-text-white bold" style="font-size: 1.5rem;">Ticketland</span>
            </a>

            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        <div id="navbarBasicExample" class="navbar-menu">
            <div class="navbar-start">
                
                <!-- <a class="navbar-item">
                    <el-input
                        placeholder="Buscar por nombre del evento"
                        style="width: 30vh;"
                    />
                </a> -->
            </div>

            <div class="navbar-end pl-4 pr-3" style="background-color: rgba(53, 67, 80, 0.8); border-radius: 5px;">
                <span class="has-text-white pt-4" style="font-size: 0.9rem;">Acceso a organizadores</span>
                <a class="navbar-item" href="/login">
                    <span class="pl-3 pr-3 pt-1 pb-1" style="border: 1px solid white; border-radius: 15px; color: white; font-weight: bold; font-size: 0.8rem;">Ingresar</span>
                </a>
            </div>
        </div>
    </nav>
    <el-carousel class="header">
        <el-carousel-item v-for="(item, i) in events" :key="i" :interval="4000">
            <div class="carousel-item">
                <img
                    :src="`/events/images/${item.profile.name}`"
                    :alt="item.name"
                />
                <div class="caption pt-4 pb-5">
                    <p class="title is-3 mb-1">{{ item.name }}</p>
                    <p class="mb-1 subtitle is-6 has-text-white location" v-if="item.location">{{ item.location.name }}</p>
                    <p class="mb-1 subtitle is-6 has-text-white dates">{{ viewDates(item.event_dates) }}</p>
                    <a class="button is-outlined is-white mt-3" :href="`/evento/${item.url}`">Comprar boletos</a>
                </div>
            </div>
        </el-carousel-item>
    </el-carousel>
    <el-row :gutter="20">
        <el-col :span="24" class="pl-6 p-6">
            <div class="card has-background-white p-5 mt-5 mb-5">
                <h2 class="title is-4 has-text-black mb-3">Ya puedes pagar con tarjetas <span class="has-text-link">emitidas fuera de México</span> en Ticketland</h2>
                <p class="has-text-dark mb-2">Disfruta de los mejores eventos sin limitaciones, ahora aceptamos pagos con Paypal para mayor comodidad para ti.</p>
                <p class="has-text-dark mb-0">Pero no te preocupes también podrás seguir realizando tus pagos en OXXO o con tu tarjeta de crédito/débito.</p>
            </div>
        </el-col>
        <el-col :span="24" class="has-text-centered p-6" style="background-image: url('/general/fondo.jpg');">
            <h4 class="title is-3 has-text-white mb-3">¿Tienes un evento?</h4>
            <p class="subtitle is-5 has-text-white">Regístrate en Ticketland y comienza a ver todos los beneficios que tenemos para ti.</p>
            <el-row :gutter="20">
                <el-col class="mb-4" :xs="12" :sm="12" :md="4" :lg="{span: 4, offset: 4}" :xl="{span: 4, offset: 4}">
                    <img src="/general/img1.png" alt="Ticketland">
                </el-col>
                <el-col class="mb-4" :xs="12" :sm="12" :md="4" :lg="{span: 4, offset: 4}" :xl="{span: 4, offset: 4}">
                    <img src="/general/img2.png" alt="Ticketland">
                </el-col>
                <el-col class="mb-4" :xs="12" :sm="12" :md="4" :lg="{span: 4, offset: 4}" :xl="{span: 4, offset: 4}">
                    <img src="/general/img3.png" alt="Ticketland">
                </el-col>
                <el-col class="mb-4" :xs="12" :sm="12" :md="4" :lg="{span: 4, offset: 4}" :xl="{span: 4, offset: 4}">
                    <img src="/general/img4.jpg" alt="Ticketland">
                </el-col>
            </el-row>
            <a class="button is-outlined is-white mt-6 bold w-100" href="/register">¡REGÍSTRATE Y VIVE UNA NUEVA EXPERIENCIA!</a>
        </el-col>
        <el-col :span="24" class="p-6">
            <h2 class="title is-2 has-text-black has-text-centered mt-6">Próximos eventos</h2>
            <el-row :gutter="20" class="mb-6">
                <el-col :xs="24" :sm="24" :md="12" :lg="6" :xl="6" v-for="(item, i) in events" :key="i">
                    <div class="card">
                        <div class="card-image">
                            <a :href="`/evento/${item.url}`">
                                <img :src="`events/images/${item.profile.name}`" :alt="item.name" class="w-100  image-card">
                            </a>
                        </div>
                        <div class="card-content has-background-white">
                            <div class="media">
                                <div class="media-content">
                                    <a class="title is-4 has-text-dark" :href="`/evento/${item.url}`">{{ item.name }}</a><br>
                                    <a class="subtitle is-6 has-text-link" :href="`/evento/${item.url}`">{{ appUrl }}/evento/{{ item.url }}</a>
                                </div>
                            </div>
                            <div class="content">
                                {{ item.description.length > 200 ? item.description.substring(0, 200)+'...' : item.description }}
                            </div>
                        </div>
                    </div>
                </el-col>
            </el-row>
        </el-col>
        <el-col :span="24">
            <img src="/general/footerTick.png" alt="Ticketland" class="w-100">
        </el-col>
        <el-col :span="24" class="has-background-black has-text-white p-6">
            <el-row :gutter="20" class="mb-5 pl-6 pr-6">
                <el-col :span="8">
                    <h4 class="title is-4">Ticketland</h4>
                    <p class="mb-2">Av. Tizoc 324, Colonia Ciudad del sol, Zapopan Jalisco</p>
                    <p class="mb-2">(33) 23 86 50 15</p>
                    <p class="mb-2">contacto@ticketland.mx</p>
                    <p>Una empresa de Maxwell Corp</p>
                </el-col>
                <el-col :span="8">
                    <p class="is-align-content-center is-justify-content-center is-flex">
                        <img src="/general/logoG.png" alt="Ticketland" class="w-25">
                    </p>
                </el-col>
                <el-col :span="8" class="has-text-right">
                    <h4 class="title is-4">Atención al cliente</h4>
                    <p>Lunes a viernes de 9:00 a.m - 6:00 p.m.</p>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    </div>
</template>

<style scoped>
.header {
    height: 75vh;
}
.bg {
    background-color: rgb(247, 241, 241);
}
a.navbar-item:focus, a.navbar-item:focus-within, a.navbar-item:hover, .navbar-link:focus, .navbar-link:focus-within, .navbar-link:hover {
    --bulma-navbar-item-background-l-delta: unset !important;
    --bulma-navbar-item-background-a: unset !important;
}
.carousel-item {
    position: relative;
    width: 100%;
    height: 100%;
}

.carousel-item img {
    object-fit: cover;
    width: 100%;
    height: 100%;
    object-position: center;
    border-radius: 8px;
}
.caption {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.4);
    color: #fff;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 1.2rem;
    text-align: center;
}
.image-card {
    height: 30vh;
    object-fit: cover;
    object-position: center;
}
@media only screen and (max-width: 500px) and (min-width: 200px) {
    .header {
        height: unset;
    }
    .dates {
        display: none;
    }
    .location {
        display: none;
    }
    .caption {
        bottom: 5px;
        padding: 4px 8px !important;
        width: 90%;
    }
}
</style>