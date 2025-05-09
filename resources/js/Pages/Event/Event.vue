<template>
    <el-row class="container-fluid has-background-white-ter">
        <el-col :xs="0" :sm="0" :md="0" :lg="24" :xl="24" class="row content-head p-r">
            <div class="p-a opacy w-100">
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
                    <a class="subtitle is-5 p-3 bg-green-500 has-text-white rounded-circle" :href="`https://api.whatsapp.com/send?text=Voy a asistir al evento ${event.name}: https://ticketland.mx/evento/${event.url}`" target="_blank"><font-awesome-icon :icon="['fab', 'whatsapp']" /></a>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    <el-row class="container-fluid has-background-white pt-6 padding" v-if="!viewInfoCustomer" ref="dataTickets">
        <el-col :xs="24" :sm="24" :md="24" :lg="{span: 12, offset: 6}" :xl="{span: 12, offset: 6}">
            <el-row :gutter="20">
                <el-col :span="24" class="mb-6">
                    <h2 class="title is-2 mb-0 bold has-text-black mb-2">Selecciona tus boletos</h2>
                    <h3 class="subtitle is-4 mb-0 has-text-grey">Máximo 10 boletos por orden</h3>
                </el-col>
                <el-col :span="24">
                    <el-row class="mb-6" :gutter="80" v-for="(t, index) in data.tickets" :key="index">
                        <el-col class="mb-3" :sm="24" :md="16" :lg="18" :xl="18">
                                <h4 class="subtitle is-4 has-text-dark mb-0">{{ t.name }}</h4>
                                <h5 class="subtitle is-5 has-text-link mb-1">{{ formatCurrency(t.price) }} MXN</h5>
                                <p class="has-text-black justify mb-0" v-if="t.description">{{ t.description }}</p>
                        </el-col>
                        <el-col class="mb-6" :xs="24" :sm="24" :md="8" :lg="6" :xl="6">
                            <el-input-number
                                class="w-100"
                                v-model="t.quantity"
                                size="large"
                                :min="0"
                                :max="t.max_reservation"
                                @change="calculate"
                            />
                        </el-col>
                    </el-row>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    <el-row class="container-fluid has-background-white pb-5 padding b-t b-b" v-if="!viewInfoCustomer">
        <el-col :xs="24" :sm="24" :md="24" :lg="{span: 12, offset: 6}" :xl="{span: 12, offset: 6}">
            <el-row :gutter="20">
                <el-col :span="24" class="pt-6 pb-4">
                    <el-row :gutter="80">
                        <el-col :xs="24" :sm="24" :md="16" :lg="18" :xl="18" class="mb-3">
                            <h4 class="subtitle is-4 has-text-dark mb-2" v-if="data.selected != 1">Tienes <b>{{ data.selected }}</b> boletos seleccionados</h4>
                            <h4 class="subtitle is-4 has-text-dark mb-2" v-if="data.selected == 1">Tienes <b>{{ data.selected }}</b> boleto seleccionado</h4>
                            <h4 class="title is-4 has-text-link">{{ formatCurrency(data.subtotal) }} MXN <span class="subtitle is-6 has-text-grey"> + CARGOS</span></h4>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="6" :xl="6">
                            <el-button class="bold w-100" type="success" size="large" @click="loadInfo">
                                <font-awesome-icon :icon="['fas', 'money-check-dollar']" />&nbsp;&nbsp;Comprar boletos
                            </el-button>
                        </el-col>
                    </el-row>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    <el-row
        ref="dataOrder"
        class="custom-loading-svg container-fluid has-background-white pt-6 pb-6 padding b-b"
        v-if="viewInfoCustomer"
        element-loading-text="¡Procesando tu compra, por favor espera!"
        v-loading="false"
        :element-loading-svg="svg"
        element-loading-svg-view-box="-10, -10, 50, 50"
        element-loading-background="rgba(0, 0, 0, 0.9)"
    >
        <el-col :xs="24" :sm="24" :md="24" :lg="{span: 12, offset: 6}" :xl="{span: 12, offset: 6}">
            <el-row :gutter="20" class="mb-6">
                <el-col :span="24" class="mb-3">
                    <el-row>
                        <el-col :xs="24" :sm="24" :md="12" :lg="18" :xl="18">
                            <h3 class="title is-3 has-text-dark mb-2">Datos de la orden</h3>
                        </el-col>
                        <el-col :xs="0" :sm="0" :md="12" :lg="6" :xl="6" class="has-text-right">
                            <p class="has-text-link pointer" @click="viewTickets"><font-awesome-icon :icon="['fas', 'arrow-left']" /> Regresar a boletos</p>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="0" :lg="0" :xl="0" class="mt-1">
                            <p class="has-text-link pointer" @click="viewTickets"><font-awesome-icon :icon="['fas', 'arrow-left']" /> Regresar a boletos</p>
                        </el-col>
                    </el-row>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mb-3">
                    <label class="bold has-text-dark" for="name">Nombre completo <span class="has-text-danger">*</span></label>
                    <el-input
                        class="el-form-item mb-0 mt-1"
                        :class="{'is-error': errors.name}"
                        id="name"
                        v-model="data.order.name"
                        placeholder="Nombre completo"
                    />
                    <span class="text-error" v-if="errors.name">El nombre es obligatorio.</span>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mb-3">
                    <label class="bold has-text-dark" for="email">Correo <span class="has-text-danger">*</span></label>
                    <el-input
                        class="el-form-item mb-0 mt-1"
                        :class="{'is-error': errors.email || errors.confirm_email_invalid2}"
                        id="email"
                        v-model="data.order.email"
                        placeholder="Correo electrónico"
                    />
                    <span class="text-error" v-if="errors.email">El correo es obligatorio.</span>
                    <span class="text-error" v-if="errors.email_invalid">Correo inválido.</span>
                    <span class="text-error" v-if="errors.confirm_email_invalid2">Los correos no coinciden.</span>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mb-3">
                    <label class="bold has-text-dark" for="confirm_email">Confirmar correo <span class="has-text-danger">*</span></label>
                    <el-input
                        class="el-form-item mb-0 mt-1"
                        :class="{'is-error': errors.confirm_email || errors.confirm_email_invalid || errors.confirm_email_invalid2}"
                        id="confirm_email"
                        v-model="data.order.confirm_email"
                        placeholder="Confirmar correo electrónico"
                    />
                    <span class="text-error" v-if="errors.confirm_email">Confirme el correo.</span>
                    <span class="text-error" v-if="errors.confirm_email_invalid">Correo inválido.</span>
                    <span class="text-error" v-if="errors.confirm_email_invalid2">Los correos no coinciden.</span>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mb-3">
                    <label class="bold has-text-dark" for="phone">Teléfono <span class="has-text-danger">*</span></label>
                    <el-input
                        class="el-form-item mb-0 mt-1"
                        :class="{'is-error': errors.phone}"
                        id="phone"
                        v-model="data.order.phone"
                        placeholder="Teléfono"
                    />
                    <span class="text-error" v-if="errors.phone">El teléfono es obligatorio.</span>
                    <span class="text-error" v-if="errors.phone_invalid">Teléfono inválido.</span>
                </el-col>
                <el-col :span="24">
                    <i class="has-text-dark"><font-awesome-icon :icon="['fas', 'circle-info']" /> Debes de tener acceso al correo ya que a esta dirección se enviarán los boletos.</i>
                </el-col>
            </el-row>
            <el-row :gutter="50" class="mb-6">
                <el-col :span="24" class="mb-3">
                    <el-row>
                        <el-col :xs="24" :sm="24" :md="12" :lg="18" :xl="18">
                            <h3 class="title is-3 has-text-dark mb-2">Datos de los boletos</h3>
                        </el-col>
                        <el-col :xs="0" :sm="0" :md="12" :lg="6" :xl="6" class="has-text-right">
                            <p class="has-text-link pointer" @click="viewTickets"><font-awesome-icon :icon="['fas', 'arrow-left']" /> Regresar a boletos</p>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="0" :lg="0" :xl="0" class="mt-1">
                            <p class="has-text-link pointer" @click="viewTickets"><font-awesome-icon :icon="['fas', 'arrow-left']" /> Regresar a boletos</p>
                        </el-col>
                    </el-row>
                </el-col>
                <el-col :span="24">
                    <el-row>
                        <el-card class="w-100 mb-5" v-for="(t, index) in data.ticketsReserved" :key="index">
                            <template #header>
                                <div class="card-header">
                                    <el-col :span="24">
                                        <el-row :gutter="20">
                                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                                <span>Boleto {{ (index + 1) }} - <b class="has-text-primary">{{ t.name }}</b></span>
                                            </el-col>
                                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="has-text-right">
                                                <el-checkbox class="w-100" v-model="t.checked" label="Autocompletar este boleto con datos de la orden." size="large" @change="(val) => autoComplete(val, index)" />
                                            </el-col>
                                        </el-row>
                                    </el-col>
                                </div>
                            </template>
                            <el-row :gutter="20">
                                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mb-3">
                                    <label class="bold has-text-dark">Nombre completo <span class="has-text-danger">*</span></label>
                                    <el-input
                                        class="el-form-item mb-0 mt-1"
                                        :class="{'is-error': errors.names[index]}"
                                        v-model="t.customer_name"
                                        placeholder="Nombre completo"
                                    />
                                    <span class="text-error" v-if="errors.names[index]">El nombre es obligatorio.</span>
                                </el-col>
                                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mb-3">
                                    <label class="bold has-text-dark">Correo</label>
                                    <el-input
                                        class="el-form-item mb-0 mt-1"
                                        :class="{'is-error': false}"
                                        v-model="t.email"
                                        placeholder="Correo"
                                    />
                                </el-col>
                                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mb-3">
                                    <label class="bold has-text-dark">Teléfono</label>
                                    <el-input
                                        class="el-form-item mb-0 mt-1"
                                        :class="{'is-error': false}"
                                        v-model="t.phone"
                                        placeholder="Teléfono"
                                    />
                                </el-col>
                                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mb-3">
                                    <label class="bold has-text-dark">¿Tienes un código de descuento?</label>
                                    <el-input
                                        class="el-form-item mb-0 mt-1"
                                        :class="{'is-error': false}"
                                        v-model="t.code"
                                        placeholder="Ingresa tu código"
                                    />
                                </el-col>
                            </el-row>
                        </el-card>
                    </el-row>
                </el-col>
            </el-row>
            <el-row :gutter="50" class="mb-6">
                <el-col :span="24" class="mb-3">
                    <el-row>
                        <el-col :xs="24" :sm="24" :md="12" :lg="18" :xl="18">
                            <h3 class="title is-3 has-text-dark mb-2">Datos del pago</h3>
                        </el-col>
                        <el-col :xs="0" :sm="0" :md="12" :lg="6" :xl="6" class="has-text-right">
                            <p class="has-text-link pointer" @click="viewTickets"><font-awesome-icon :icon="['fas', 'arrow-left']" /> Regresar a boletos</p>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="0" :lg="0" :xl="0" class="mt-1">
                            <p class="has-text-link pointer" @click="viewTickets"><font-awesome-icon :icon="['fas', 'arrow-left']" /> Regresar a boletos</p>
                        </el-col>
                    </el-row>
                </el-col>
                <el-col :span="24">
                    <el-table class="w-100" :data="filteredTickets" stripe header-cell-class-name="has-text-dark" empty-text="Ningún dato disponible en esta tabla">
                        <el-table-column prop="name" label="Producto" />
                        <el-table-column label="Cantidad" align="center">
                            <template #default="scope">
                                {{ scope.row.quantity }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Precio unitario">
                            <template #default="scope">
                                {{ scope.row.priceUnit }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Subtotal">
                            <template #default="scope">
                                {{ scope.row.subtotal }}
                            </template>
                        </el-table-column>
                    </el-table>
                </el-col>
                <el-col :span="24" class="has-text-left mt-6">
                    <h6 class="subtitle is-5 has-text-black mb-2">
                        Subtotal: <b>{{ formatCurrency(data.subtotal) }} MXN</b>
                    </h6>
                    <h6 class="subtitle is-5 has-text-black mb-2">
                        Cupones: <b>N/A</b>
                    </h6>
                    <h6 class="subtitle is-5 has-text-black mb-2">
                        Cargo por servicio: <b>{{ formatCurrency(data.subtotal * .12) }} MXN</b>
                    </h6>
                    <h6 class="subtitle is-5 has-text-black mb-2">
                        Total: <b class="has-text-success">{{ formatCurrency(data.subtotal + (data.subtotal * .12)) }} MXN</b>
                    </h6>
                </el-col>
                <el-col :span="24" class="has-text-centered mt-6">
                    <el-button type="primary" size="large"><font-awesome-icon :icon="['fas', 'dollar-sign']" />&nbsp;&nbsp;Realizar pago</el-button>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    <el-row class="container-fluid has-background-white pb-6 padding" ref="moreInfo">
        <el-col :xs="24" :sm="24" :md="24" :lg="{span: 12, offset: 6}" :xl="{span: 12, offset: 6}">
            <el-row :gutter="20">
                <el-col :span="24" class="pt-6 pb-4">
                    <el-row :gutter="80">
                        <el-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14" class="mb-3">
                            <h3 class="subtitle is-3 has-text-grey mb-2">Sobre el evento</h3>
                            <p class="justify has-text-black" v-if="event.description">{{ event.description }}</p>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">
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
        </el-col>
    </el-row>
    <el-row class="has-background-dark" style="height: 15vh;">
        
    </el-row>
</template>

<script>
import { dateEs, time } from '@/dateEs';
import { showNotification } from '@/notification';

export default {
    components: {

    },
    data() {
        return {
            appUrl: window.location.origin,
            event: this.$page.props.event,
            data: {
                tickets: [],
                ticketsReserved: [],
                selected: 0,
                subtotal: 0,
                order: {
                    name: '',
                    email: '',
                    confirm_email: '',
                    phone: ''
                }
            },
            viewInfoCustomer: false,
            errors: {
                name: false,
                email: false,
                email_invalid: false,
                confirm_email: false,
                confirm_email_invalid: false,
                confirm_email_invalid2: false,
                phone: false,
                phone_invalid: false,
                names: []
            },
            svg: `
                <path class="path" d="
                M 30 15
                L 28 17
                M 25.61 25.61
                A 15 15, 0, 0, 1, 15 30
                A 15 15, 0, 1, 1, 27.99 7.5
                L 15 15
                " style="stroke-width: 4px; fill: rgba(0, 0, 0, 0)"/>
            `,
        }
    },
    beforeMount() {
        this.event.tickets.forEach(t => {
            this.data.tickets.push({
                id: t.id,
                name: t.name,
                description: t.description,
                price: t.price,
                priceUnit: this.formatCurrency(t.price) + ' MXN',
                subtotal: '',
                quantity: 0,
            })
        });
    },
    mounted() {
        
    },
    methods: {
        loadInfo() {
            if (!this.data.selected) {
                showNotification('¡Atención!', 'Debes tener seleccionado al menos 1 boleto', 'warning', 6500);
                return;
            }
            this.viewInfoCustomer = true;
            this.$nextTick(() => {
                // Espera a que el DOM se actualice
                if (this.$refs.dataOrder) {
                    this.$refs.dataOrder.$el.scrollIntoView({ behavior: 'smooth' });
                }
            });
        },
        calculate(val, oldVal) {
            this.data.selected        = this.data.selected + (val - oldVal);
            this.data.subtotal        = 0;
            this.data.ticketsReserved = [];
            this.errors.names         = [];
            this.data.tickets.forEach((t, i) => {
                this.data.subtotal = this.data.subtotal + (t.quantity * t.price);
                t.subtotal         = this.formatCurrency(t.quantity * t.price) + ' MXN';
                if (t.quantity > 0) {
                    for (let j = 0; j < t.quantity; j++) {
                        this.errors.names.push(false);
                        this.data.ticketsReserved.push({
                            name: t.name, customer_name: '', email: '', phone: '', code: '', checked: false
                        });
                    }
                }
            });
        },
        autoComplete(checked, index) {
            this.data.ticketsReserved[index].customer_name = '';
            this.data.ticketsReserved[index].email         = '';
            this.data.ticketsReserved[index].phone         = '';
            if(checked) {
                this.data.ticketsReserved[index].customer_name = this.data.order.name;
                this.data.ticketsReserved[index].email         = this.data.order.email;
                this.data.ticketsReserved[index].phone         = this.data.order.phone;
            }
        },
        viewTickets() {
            this.viewInfoCustomer = false;
            this.$nextTick(() => {
                // Espera a que el DOM se actualice
                if (this.$refs.dataTickets) {
                    this.$refs.dataTickets.$el.scrollIntoView({ behavior: 'smooth' });
                }
            });
        },
        moreInfo() {
            this.viewInfoCustomer = false;
            this.$nextTick(() => {
                // Espera a que el DOM se actualice
                if (this.$refs.moreInfo) {
                    this.$refs.moreInfo.$el.scrollIntoView({ behavior: 'smooth' });
                }
            });
        },
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
        },
        validate() {
            this.resetErrors();
            const intRegex  = /^\d{10}$/;
            const mailRegex =  /^\w+([.-_+]?\w+)*@\w+([.-]?\w+)*(\.\w{2,10})+$/;
            let valid       = true;

            if (!this.data.order.name) {
                this.errors.name = true;
                valid            = false;
            }
            if (!this.data.order.email) {
                this.errors.email = true;
                valid             = false;
            }
            if (!this.data.order.confirm_email) {
                this.errors.confirm_email = true;
                valid                     = false;
            }
            if (!this.data.order.phone) {
                this.errors.phone = true;
                valid             = false;
            }
            if (this.data.order.email) {
                if (!mailRegex.test(this.data.order.email)) {
                    this.errors.email_invalid = true;
                    valid                     = false;
                }
            }
            if (this.data.order.confirm_email) {
                if (!mailRegex.test(this.data.order.confirm_email)) {
                    this.errors.confirm_email_invalid = true;
                    valid                             = false;
                }
            }
            if (this.data.order.email && this.data.order.confirm_email) {
                if (this.data.order.email != this.data.order.confirm_email) {
                    this.errors.confirm_email_invalid2 = true;
                    valid                              = false;
                }
            }
            if (this.data.order.phone) {
                if (!intRegex.test(this.data.order.phone)) {
                    this.errors.phone_invalid = true;
                    valid                     = false;
                }
            }

            this.data.ticketsReserved.forEach((t, i) => {
                if (!t.customer_name) {
                    this.errors.names[i] = true;
                    valid                = false;
                }
            });

            return valid;
        },
        resetErrors() {
            this.errors.name                   = false;
            this.errors.email                  = false;
            this.errors.email_invalid          = false;
            this.errors.confirm_email          = false;
            this.errors.confirm_email_invalid  = false;
            this.errors.confirm_email_invalid2 = false;
            this.errors.phone                  = false;
            this.errors.phone_invalid          = false;
            this.errors.names                  = [];
        }
    },
    computed: {
        filteredTickets() {
            return this.data.tickets.filter(t => t.quantity > 0);
        }
    }
}
</script>
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
        padding-left: 1vh;
        padding-right: 1vh;
    }
}
</style>