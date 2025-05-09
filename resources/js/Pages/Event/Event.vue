<template>
    <el-row class="container-fluid has-background-white-ter">
        <el-col :xs="0" :sm="0" :md="0" :lg="24" :xl="24" class="row content-head p-r">
            <div class="col-xl-12 p-a opacy">
                <img class="h-100 w-100 img-transparent" :src="appUrl+'/events/images/'+event.profile.name" :alt="event.name">
            </div>
        </el-col>
        <el-col :xs="0" :sm="0" :md="0" :lg="{span: 12, offset: 6}" :xl="{span: 12, offset: 6}">
            <img class="p-a img-event p-0" :src="appUrl+'/events/images/'+event.profile.name" :alt="event.name">
        </el-col>
        <el-col :xs="24" :sm="24" :md="24" :lg="0" :xl="0">
            <img class="w-100 shadow" :src="appUrl+'/events/images/'+event.profile.name" :alt="event.name">
        </el-col>
    </el-row>
    <el-row class="container-fluid pt-6 pb-6 has-background-white-ter padding">
        <el-col :xs="24" :sm="24" :md="24" :lg="{span: 12, offset: 6}" :xl="{span: 12, offset: 6}">
            <el-row :gutter="20">
                <el-col :xs="24" :sm="24" :md="18" :lg="16" :xl="16">
                    <h1 class="title is-1 mb-0 bold has-text-dark">{{ event.name }}</h1>
                    <div class="mt-3 has-text-dark">
                        <b><font-awesome-icon :icon="['fas', 'calendar-days']" /> Fechas:</b>
                        <p v-for="(d, index) in event.event_dates" :key="index"><b>Día {{ index + 1 }}: </b>{{ formatDate(d.date) }} - {{ formatTime(d.initial_time) }} a {{ formatTime(d.final_time) }}</p>
                    </div>
                    <p class="bold has-text-link mt-6 pointer"><font-awesome-icon :icon="['fas', 'plus']" /> Más información del evento</p>
                </el-col>
                <el-col :xs="24" :sm="24" :md="6" :lg="8" :xl="8">
                    <h6 class="subtitle is-6 bold has-text-dark mb-5">COMPARTE ESTE EVENTO</h6>
                    <a class="subtitle is-5 p-3 bg-green-500 has-text-white rounded-circle" :href="`https://api.whatsapp.com/send?text=Voy a asistir al evento ${event.name}: https://ticketland.mx/evento/${event.url}`" target="_blank"><font-awesome-icon :icon="['fab', 'whatsapp']" /></a>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    <el-row class="container-fluid has-background-white pt-6 pb-6 mb-6 padding">
        <el-col :xs="24" :sm="24" :md="24" :lg="{span: 12, offset: 6}" :xl="{span: 12, offset: 6}">
            <el-row :gutter="20">
                <el-col :span="16">
                    <div class="mb-5" v-for="(t, index) in event.tickets" :key="index">
                        <h5 class="subtitle is-5 bold has-text-dark mb-0">{{ t.name }}</h5>
                        <h5 class="subtitle is-5 has-text-link">{{ formatCurrency(t.price) }} MXN</h5>
                    </div>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
</template>

<script>
import { dateEs, time } from '@/dateEs';

export default {
    components: {

    },
    data() {
        return {
            appUrl: window.location.origin,
            event: this.$page.props.event,
        }
    },
    beforeMount() {

    },
    mounted() {

    },
    methods: {
        formatDate(_date) {
            return dateEs(_date, 1, ' ');
        },
        formatTime(_time) {
            return time(_time);
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
.rounded-circle {
    border-radius: 50% !important;
}
.w-100 {
    width: 100% !important;
}
.content-head {
    height: 20rem;
}
.opacy {
    overflow: hidden;
    height: 90%;
    background: rgba(105, 120, 134, 0.2);
}
.img-transparent {
    filter: blur(30px);
    object-fit: cover;
    object-position: center;
    opacity: 1;
    transition: opacity 0.3s ease 0s;
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
    height: 282px;
    width: 50%;
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
        height: 25rem !important;
    }
    .img-event {
        height: 354px !important;
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
        padding-left: 5vh;
        padding-right: 5vh;
    }
}
</style>