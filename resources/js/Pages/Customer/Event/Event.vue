<template>
    <MenuEvent></MenuEvent>
    <Submenu :dadEvent="event"></Submenu>
    <el-row :gutter="20" class="wrapper">
        <el-col :span="18" :offset="3" class="p-0 bg-profile" :style="{ 'background-image': `url(${imageProfile})` }">
            <div class="action-profile" @click="$refs.UploadImages.showUploadImages('profile')">
                <font-awesome-icon class="mr-2" :icon="['fas', 'pencil']" />
                Cambiar imagen de fondo
            </div>
            <el-col :span="24" class="back-title-edit">
                <h1 class="title is-1 has-text-white mb-2">{{ event.name }}</h1>
                <h5 class="title is-5 has-text-white mb-2">Categoría: {{ event.category.name }}</h5>
                <h5 class="title is-5 has-text-white"><a class="text-white" :href="appUrl+'/evento/'+event.url" target="_blank">Ver sitio web</a></h5>
                <div class="content-logo" :style="{ 'background-image': imageLogo == '' ? 'unset': `url(${imageLogo})` }">
                    <div class="text-center pt-1 container-logo" v-if="imageLogo == ''" @click="$refs.UploadImages.showUploadImages('logo')">
                        <h5 class="subtitle is-5 mt-4 text-logo">Agregar logo</h5>
                    </div>
                    <span class="p-1 edit-logo pointer" v-if="imageLogo != ''" @click="$refs.UploadImages.showUploadImages('logo')">
                        <font-awesome-icon class="mr-2" :icon="['fas', 'pencil']" />
                        Editar logo
                    </span>
                    <el-tooltip
                        class="box-item"
                        content="Eliminar logo"
                        placement="top"
                        v-if="imageLogo != ''"
                    >
                        <el-button class="p-2 btn-delete-logo" type="danger" @click="deleteLogo"><font-awesome-icon :icon="['fas', 'trash-can']" /></el-button>
                    </el-tooltip>
                </div>
                <div class="action-event" @click="$refs.EditEvent.activeEditEvent = true">
                    <font-awesome-icon class="mr-2" :icon="['fas', 'pencil']" />
                    Editar nombre y sitio de ventas
                </div>
            </el-col>
        </el-col>
        <el-col :span="16" :offset="4">
            <el-card class="pt-3">
                <el-row :gutter="30">
                    <el-col :span="16">
                        <h3 class="title is-3 has-text-dark">Acerca de <span class="subtitle is-6 ml-4 pointer" @click="$refs.EditDescription.showModal()"><font-awesome-icon :icon="['fas', 'pencil']" /> Editar</span></h3>
                        <p class="has-text-grey justify multiline-text">
                            {{event.description}}
                        </p>
                    </el-col>
                    <el-col :span="8">
                        <el-card class="card-personalized pb-3">
                            <div>
                                <span class="subtitle is-6 has-text-dark bold"><font-awesome-icon :icon="['fas', 'calendar-days']" /> CUÁNDO</span>
                                <!-- <span class="text-edit has-text-dark bold ml-5 pointer"><font-awesome-icon :icon="['fas', 'pencil']" /> Editar</span> -->
                                <div class="mt-2 mb-0">
                                    <span class="normal">DE: </span>
                                    <span class="hast-text-dark bold">{{ formatDate(event.event_dates[0].date, 1) }} - {{ formatTime(event.event_dates[0].initial_time) }}</span><br>
                                    <span class="normal">AL: </span>
                                    <span class="hast-text-dark bold">{{ formatDate(event.event_dates[event.event_dates.length-1].date, 1) }} - {{ formatTime(event.event_dates[event.event_dates.length-1].final_time) }}</span>
                                </div>
                            </div>
                            <hr>
                            <div>
                                <span class="subtitle is-6 has-text-dark bold"><font-awesome-icon :icon="['fas', 'location-dot']" /> DÓNDE</span>
                                <span class="text-edit has-text-dark bold ml-5 pointer" @click="$refs.EditLocation.showModal()"><font-awesome-icon :icon="['fas', 'pencil']" /> Editar</span>
                                <div class="mt-2 mb-0" v-if="event.location">
                                    <span class="bold">{{ event.location.name }}</span><br>
                                    <span class="bold">{{ event.location.address }}</span><br>
                                    <p class="mt-2" v-if="event.location.iframe">
                                        <iframe :src="event.location.iframe" allowfullscreen width="100%" height="200vh"></iframe>
                                    </p>
                                </div>
                            </div>
                            <hr>
                            <div>
                                <span class="subtitle is-6 has-text-dark bold"><font-awesome-icon :icon="['fas', 'address-card']" /> CONTACTO</span>
                                <span class="text-edit has-text-dark bold ml-5 pointer" @click="$refs.EditContact.showModal()"><font-awesome-icon :icon="['fas', 'pencil']" /> Editar</span>
                                <div class="mt-2 mb-0" v-if="event.email || event.phone || event.twitter || event.facebook || event.instagram || event.website">
                                    <p class="size-contact mb-1" v-if="event.email"><font-awesome-icon class="bold" :icon="['fas', 'envelope']" /> {{ event.email }}</p>
                                    <p class="size-contact mb-1" v-if="event.phone"><font-awesome-icon class="bold" :icon="['fas', 'phone-flip']" /> {{ event.phone }}</p>
                                    <p class="size-contact mb-1" v-if="event.twitter"><a class="has-text-dark links" :href="`https://x.com/${event.twitter}`" target="_blank"><font-awesome-icon :icon="['fab', 'x-twitter']" /> X (Twitter)</a></p>
                                    <p class="size-contact mb-1" v-if="event.facebook"><a class="has-text-dark links" :href="event.facebook" target="_blank"><font-awesome-icon class="bold" :icon="['fab', 'facebook-f']" /> Facebook</a></p>
                                    <p class="size-contact mb-1" v-if="event.instagram"><a class="has-text-dark links" :href="event.instagram" target="_blank"><font-awesome-icon class="bold" :icon="['fab', 'instagram']" /> Instagram</a></p>
                                    <p class="size-contact mb-1" v-if="event.website"><a class="has-text-dark links" :href="event.website" target="_blank"><font-awesome-icon class="bold" :icon="['fas', 'link']" /> {{ event.website }}</a></p>
                                </div>
                            </div>
                            <hr>
                            <div>
                                <span class="subtitle is-6 has-text-dark bold"><font-awesome-icon :icon="['fas', 'circle-dollar-to-slot']" /> TIPO DE EVENTO</span>
                                <div class="mt-2 mb-0">
                                    <span>{{ event.cost_type == 'paid' ? 'DE CONSUMO' : 'DE REGISTRO' }}</span>
                                </div>
                            </div>
                        </el-card>
                    </el-col>
                </el-row>
            </el-card>
        </el-col>
    </el-row>
    <Footer></Footer>
    <EditEvent ref="EditEvent" v-bind:dadEvent="event"></EditEvent>
    <EditDescription ref="EditDescription" v-bind:dadEvent="event"></EditDescription>
    <EditLocation ref="EditLocation" v-bind:dadEvent="event"></EditLocation>
    <EditContact ref="EditContact" v-bind:dadEvent="event"></EditContact>
    <UploadImages 
        ref="UploadImages"
        :dadEvent="event"
        :dadImageProfile="imageProfile"
        :dadImageLogo="imageLogo"
        @update-profile="imageProfile = $event"
        @update-logo="imageLogo = $event"
    >
    </UploadImages>
</template>

<script>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { dateEs, time } from '@/dateEs';
import MenuEvent from '../MenuEvent.vue';
import Submenu from '../Submenu.vue';
import Footer from '../Footer.vue';
import { EditEvent, UploadImages, EditDescription, EditLocation, EditContact } from './Modals';

export default {
    components: {
        MenuEvent,
        Submenu,
        Footer,
        EditEvent,
        UploadImages,
        EditDescription,
        EditLocation,
        EditContact
    },
    data() {
        return {
            //Aquí se declaran las variables
            appUrl: window.location.origin,
            event: this.$page.props.event,
            imageProfile: `${window.location.origin}/general/not_image.png`,
            imageLogo: ''
        }
    },
    beforeMount() {
        //Aqui se pueden mandar llamar metodos antes de que se monte el componente
        if (this.event.profile) {
            this.imageProfile = this.appUrl+'/events/images/'+this.event.profile.name;
        }
        if (this.event.logo) {
            this.imageLogo = this.appUrl+'/events/images/'+this.event.logo.name;
        }
    },
    mounted() {
        
    },
    created() {
        //Aqui se pueden mandar llamar métodos, cuando se crea el componente
    },
    methods: {
        async deleteLogo() {
            const response = await apiClient('customer/deleteLogo', 'DELETE', {event_id: this.event.id});
            if (response.error) {
                showNotification('¡Error!', response.msj, 'error');
                return false;
            }
            this.imageLogo = '';
            showNotification('¡Correcto!', response.msj, 'success');
        },
        formatDate(_date) {
            return dateEs(_date, 1, ' ');
        },
        formatTime(_time) {
            return time(_time);
        }
    }
}
</script>

<style scoped>
    .card-personalized {
        background-color: #f8f7f7 !important;
    }
    hr {
        border: 1px solid #eeeceb;
    }
    .text-edit {
        font-size: 0.8rem;
    }
    .action-profile {
        position: absolute;
        top: 5vh;
        right: 5vh;
        background-color: black;
        color: white;
        padding-left: 1vh;
        padding-right: 1vh;
        cursor: pointer;
    }
    .bg-profile {
        height: 65vh;
        background-size: 100%;
        background-position: center;
        background-repeat: no-repeat;
        position: relative;
        margin-top: -1px !important;
    }
    .back-title-edit {
        background: rgba(0, 0, 0, 0.6);
        padding-left: 22vh !important;
        position: absolute;
        bottom: 0;
        color: white !important;
        padding-top: 2vh;
        padding-bottom: 1vh;
        width: 100%;
    }
    .action-event {
        position: absolute;
        background-color: black;
        color: white;
        top: 0px;
        right: 1vh;
        padding-left: 1vh;
        padding-right: 1vh;
        cursor: pointer;
    }
    .content-logo {
        position: absolute;
        top: -1vh;
        left: 2vh;
        height: 16vh;
        width: 15vh;
        background-color: white;
        padding: 0.5rem;
        background-size: 90%;
        background-position: center;
        background-repeat: no-repeat;
    }
    .container-logo {
        width: 100%;
        height: 100%;
        border: 2px dashed grey;
    }
    .text-logo {
        color: #55504f !important;
        font-weight: bold !important;
        cursor: pointer;
    }
    .edit-logo {
        background-color: black;
        font-size: 0.75rem;
        text-align: right;
        position: absolute;
        top: 0px;
        right: 0px;
    }
    .btn-delete-logo {
        position: absolute;
        bottom: 0px;
        right: 0px;
    }
    .size-contact {
        font-size: 0.75rem;
    }
    .links:hover {
        text-decoration: underline;
    }
    .multiline-text {
        white-space: pre-line;
    }
</style>