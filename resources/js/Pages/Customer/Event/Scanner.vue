<template>
    <MenuEvent></MenuEvent>
    <Submenu :dadEvent="event"></Submenu>
    <el-row :gutter="20" class="wrapper">
        <el-col :span="14" :offset="5">
            <el-card>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <!-- <qrcode-stream @decode="onDecode" @init="onInit" @detect="onDetect" /> -->
                         <qrcode-stream
                            :paused="paused"
                            @detect="onDetect"
                            @init="onInit"
                            @camera-on="resetValidationState"
                        >

                            <div
                                v-if="validationPending"
                                class="validation-pending"
                            >
                                {{ txtScan }}
                            </div>
                        </qrcode-stream>
                    </el-col>
                    <el-col :span="12">
                        <div v-if="ticket">
                            <p class="subtitle is-4 has-text-black mb-2">Tipo de boleto: <b>{{ ticket }}</b></p>
                            <p class="subtitle is-4 has-text-black mb-2">Nombre: <b>{{ name }}</b></p>
                            <p class="subtitle is-4 has-text-black mb-2">Fecha de compra: <b>{{ purchaseDate }}</b></p>
                            <p class="subtitle is-4 has-text-black mb-2">Acceso: <b class="has-text-success">{{ access }}</b></p>
                        </div>
                        <el-button class="mt-4" type="success" v-if="customerName" @click="reprintTicket"><font-awesome-icon :icon="['fas', 'print']" />&nbsp;&nbsp;&nbsp;Reimprimir etiqueta</el-button>
                        <el-button class="mt-2" type="primary" v-if="resetScan" @click="resetScanner"><font-awesome-icon :icon="['fas', 'arrows-rotate']" />&nbsp;&nbsp;&nbsp;Recargar escáner</el-button>
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
import { QrcodeStream } from 'vue-qrcode-reader';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { dateEs } from '@/dateEs';
import Swal from 'sweetalert2';
import { printZPL } from '@/zebraPrinter';

export default {
    components: {
        MenuEvent,
        Submenu,
        Footer,
        QrcodeStream
    },
    data() {
        return {
            event: this.$page.props.event,
            result: null,
            paused: false,
            isValid: '',
            txtScan: 'Validando código de acceso...',
            ticket: '',
            name:'',
            purchaseDate: '',
            access: '',
            resetScan: false,
            code: [],
            customerName: ''
        }
    },
    mounted() {
        window.addEventListener('keypress', this.checkKey);
    },
    beforeUnmount() {
        window.removeEventListener('keypress', this.checkKey);
    },
    methods: {
        onDetect(detectedCodes) {
            // console.log(detectedCodes)
            this.result = JSON.stringify(detectedCodes.map((code) => code.rawValue));
            this.validateAccess(this.result);
        },
        onInit(promise) {
            promise.catch(error => {
                if (error.name === 'NotAllowedError') {
                    showNotification('¡Error!', 'Permiso denegado para usar la cámara', 'error');
                } else {
                    showNotification('¡Error!', 'Error al iniciar cámara', 'error');
                }
            })
        },
        resetValidationState() {
            this.isValid = '';
        },
        async validateAccess(access) {
            this.paused       = true;
            this.customerName = '';
            const response    = await apiClient('customer/validateAccess', 'POST', {event_id: this.event.id, access});
            if (response.error) {
                this.paused = false;
                if (!response.data.type) {
                    showNotification('¡Error!', response.msj, 'error', 7000);
                    return false;
                }
                this.txtScan      = 'Recargue el escáner para continuar';
                this.resetScan    = true;
                this.paused       = true;
                Swal.fire({
                    icon: 'error',
                    title: '¡ERROR!',
                    html: response.msj,
                    scrollbarPadding: false,
                    allowEnterKey: false
                });
                return false;
            }
            this.txtScan      = 'Recargue el escáner para continuar';
            this.resetScan    = true;
            this.ticket       = response.data.ticket.name;
            this.name         = response.data.name;
            this.purchaseDate = dateEs(response.data.created_at, 1, '/');
            this.access       = 'Correcto';
            this.customerName = response.data.name;
            this.printTicket(response.data.name);
            // this.printTicket('Federico Sebastián Fernández de la Torre');
        },
        resetScanner() {
            this.txtScan      = 'Validando código de acceso...';
            this.resetScan    = false;
            this.paused       = false;
            this.ticket       = '';
            this.name         = '';
            this.purchaseDate = '';
            this.access       = '';
            this.code         = [];
            this.customerName = '';
        },
        checkKey(tecla) {
            if (tecla.key === 'Enter' && !this.resetScan) {
                const codeQr = this.code.join('');
                this.validateAccess(codeQr);
                this.code = [];
            } else {
                this.code.push(tecla.key);
            }
        },
        async printTicket(name) {
            const top = name.length > 35 ? 70 : 95; 
            const zpl = `
                ^XA
                ^CI28
                ^FO80,${top}
                ^A0N,35,35
                ^FB300,3,20,C,0
                ^FD${name}^FS
                ^XZ
            `;
            const print = await printZPL(zpl);
            console.log(print);
        },
        reprintTicket() {
            this.printTicket(this.customerName);
        }
    },
    computed: {
        validationPending() {
            return this.paused
        },
    },
}
</script>

<style scoped>
.validation-pending {
    position: absolute;
    width: 100%;
    height: 100%;

    background-color: rgba(255, 255, 255, 0.8);
    padding: 10px;
    text-align: center;
    font-weight: bold;
    font-size: 1.4rem;
    color: black;

    display: flex;
    flex-flow: column nowrap;
    justify-content: center;
}
</style>