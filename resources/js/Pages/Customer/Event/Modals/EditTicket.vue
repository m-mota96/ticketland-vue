<template>
    <el-dialog
        v-model="activeEditTicket"
        title="Agrega un tipo de boleto"
        width="800"
        align-center
        style="margin-top: 2% !important;"
    >
        <el-row :gutter="20" class="mb-4">
            <el-col :span="12">
                <el-row :gutter="10">
                    <el-col :span="24" class="mb-5">
                        <label class="bold" for="name">Nombre <span class="has-text-danger">*</span></label>
                        <el-input
                            class="el-form-item mb-0 mt-1"
                            :class="{'is-error': errors.name}"
                            size="large"
                            id="name"
                            v-model="ticket.name"
                            placeholder="p.ej: General, VIP, Paquete Full Beneficios"
                        />
                        <span class="text-error" v-if="errors.name">El nombre es obligatorio.</span>
                    </el-col>
                    <el-col :span="24" class="mb-5">
                        <label class="bold" for="description">Descripción</label>
                        <el-mention
                            class="w-100 mt-1"
                            id="description"
                            v-model="ticket.description"
                            type="textarea"
                            :rows="4"
                        />
                    </el-col>
                    <el-col :span="24" class="mb-5">
                        <label class="bold" for="days">¿Cuántas veces se puede escanear este boleto? <span class="has-text-danger">*</span></label>
                        <el-select
                        class="el-form-item mb-0 mt-1"
                        :class="{'is-error': errors.name}"
                        size="large"
                        id="days"
                            v-model="ticket.valid"
                            placeholder="Seleccione una opción"
                            >
                            <el-option
                            v-for="n in daysEvent"
                            :key="n"
                            :label="n"
                            :value="n"
                            />
                        </el-select>
                        <p class="text-error text-justify" v-if="errors.valid">
                            Indique la cantidad de veces que pueden utilizar el boleto.
                        </p>
                        <p class="text-error text-justify">
                            <b>Ejemplo:</b> si el evento dura 2 días y este boleto lo pueden utilizar ambos días 
                            para ingresar debe elegir 2, de lo contrario indique para cuántos días funciona.
                        </p>
                    </el-col>
                    <el-col :span="24" class="mb-5" v-if="ticket.cost_type == 'paid'">
                        <label class="bold" for="discount">¿Deseas aplicar descuento para este boleto?</label><br>
                        <el-switch
                            v-model="ticket.discount"
                            inline-prompt
                            style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949"
                            :active-value="true"
                            :inactive-value="false"
                            active-text="Si"
                            inactive-text="No"
                        />
                    </el-col>
                </el-row>
            </el-col>
            <el-col :span="12">
                <el-row :gutter="10">
                    <el-col :span="24" class="mb-4">
                        <label class="bold">Costo <span class="has-text-danger">*</span></label><br>
                        <el-radio-group class="mb-0 mt-1" v-model="ticket.cost_type" @change="validateCost">
                            <el-radio value="paid" v-if="eventType == 'paid'">Con pago</el-radio>
                            <el-radio value="free">Gratis</el-radio>
                        </el-radio-group><br>
                        <div v-if="eventType == 'paid'">
                            <el-input
                                class="el-form-item mt-2 mb-0 w-50"
                                :class="{'is-error': errors.price || errors.price_incorrect}"
                                v-if="ticket.cost_type == 'paid'"
                                v-model="ticket.price"
                                :formatter="(value) => {
                                    const intValue = parseInt(value, 10);
                                    return isNaN(intValue) ? null : `$ ${intValue.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
                                }"
                                :parser="(value) => value.replace(/\$\s?|(,*)/g, '')"
                            />
                            <p class="text-error mt-0" v-if="errors.price && ticket.cost_type == 'paid'">El precio mínimo es de $100.</p>
                            <p class="text-error mt-0" v-if="errors.price_incorrect && ticket.cost_type == 'paid'">El precio es obligatorio y debe ser entero.</p>
                        </div>
                        <p class="mt-2" v-if="modelPayment == 'separated'"><i>* Las comisiones se cobran aparte del precio de tu boleto</i></p>
                        <p class="mt-2" v-if="modelPayment == 'including'"><i>* Las comisiones se cobran del precio de tu boleto</i></p>
                    </el-col>
                    <!-- <el-col :span="24" class="mb-5">
                        <el-row :gutter="20">
                            <el-col :span="12">
                                <label class="bold" for="min_tickets">Min. boletos por reservación <span class="has-text-danger">*</span></label>
                                <el-input-number
                                    class="el-form-item mb-0 mt-1 w-100"
                                    :class="{'is-error': errors.min_reservation || errors.min}"
                                    id="min_tickets"
                                    v-model="ticket.min_reservation"
                                    :min="1"
                                    :max="10"
                                    size="large"
                                    :step="1"
                                    :precision="0"
                                />
                                <span class="text-error" v-if="errors.min_reservation">La cantidad mínima es obligatoria.</span>
                                <span class="text-error" v-if="errors.min">La cantidad mínima debe ser menor o igual que la máxima.</span>
                            </el-col>
                            <el-col :span="12">
                                <label class="bold" for="max_tickets">Max. boletos por reservación <span class="has-text-danger">*</span></label>
                                <el-input-number
                                    class="el-form-item mb-0 mt-1 w-100"
                                    :class="{'is-error': errors.max_reservation}"
                                    id="max_tickets"
                                    v-model="ticket.max_reservation"
                                    :min="1"
                                    size="large"
                                    :step="1"
                                    :precision="0"
                                />
                                <span class="text-error" v-if="errors.max_reservation">La cantidad máxima es obligatoria.</span>
                            </el-col>
                        </el-row>
                    </el-col> -->
                    <el-col :span="24" class="mb-5" :class="{'mt-5 pt-4': ticket.cost_type != 'paid'}">
                        <label class="bold" for="min_tickets">Cantidad disponible <span class="has-text-danger">*</span></label>
                        <el-input-number
                            class="el-form-item mb-0 mt-1 w-100"
                            :class="{'is-error': errors.quantity}"
                            id="min_tickets"
                            v-model="ticket.quantity"
                            :min="1"
                            size="large"
                            :step="1"
                            :precision="0"
                        />
                        <span class="text-error" v-if="errors.quantity">La cantidad es obligatoria.</span>
                    </el-col>
                    <el-col :span="24" class="mb-5">
                        <el-row :gutter="20">
                            <el-col :span="12">
                                <label class="bold" for="start_sale">A la venta desde... <span class="has-text-danger">*</span></label>
                                <el-date-picker
                                    class="el-form-item mb-0 mt-1 w-100"
                                    :class="{'is-error': errors.start_sale || errors.dates}"
                                    size="large"
                                    id="start_sale"
                                    v-model="ticket.start_sale"
                                    type="date"
                                    placeholder="Elija la fecha"
                                    format="DD/MM/YYYY"
                                    value-format="YYYY-MM-DD"
                                />
                                <span class="text-error" v-if="errors.start_sale">La fecha inicial es obligatoria.</span>
                                <span class="text-error" v-if="errors.dates">La fecha inicial debe ser menor o igual que la final.</span>
                            </el-col>
                            <el-col :span="12">
                                <label class="bold" for="stop_sale">...hasta <span class="has-text-danger">*</span></label>
                                <el-date-picker
                                    class="el-form-item mb-0 mt-1 w-100"
                                    :class="{'is-error': errors.stop_sale}"
                                    size="large"
                                    id="stop_sale"
                                    v-model="ticket.stop_sale"
                                    type="date"
                                    placeholder="Elija la fecha"
                                    format="DD/MM/YYYY"
                                    value-format="YYYY-MM-DD"
                                />
                                <span class="text-error" v-if="errors.start_sale">La fecha final es obligatoria.</span>
                            </el-col>
                        </el-row>
                    </el-col>
                    <el-col :span="24" v-if="ticket.discount">
                        <el-row :gutter="20">
                            <el-col :span="12">
                                <label class="bold" for="start_sale">Porcentaje de descuento <span class="has-text-danger">*</span></label>
                                <el-input
                                    class="el-form-item mb-0 mt-1"
                                    :class="{'is-error': errors.promotion || errors.promotion_invalid}"
                                    v-model="ticket.promotion"
                                    size="large"
                                    @keypress="isNumber($event)"
                                />
                                <span class="text-error" v-if="errors.promotion">El porcentaje es obligatorio.</span>
                                <span class="text-error" v-if="errors.promotion_invalid">El porcentaje solo puede ser máximo de 60%.</span>
                            </el-col>
                            <el-col :span="12">
                                <label class="bold" for="stop_sale">Expiración <span class="has-text-danger">*</span></label>
                                <el-date-picker
                                    class="el-form-item mb-0 mt-1 w-100"
                                    :class="{'is-error': errors.date_promotion}"
                                    size="large"
                                    id="stop_sale"
                                    v-model="ticket.date_promotion"
                                    type="date"
                                    placeholder="Elija la fecha"
                                    format="DD/MM/YYYY"
                                    value-format="YYYY-MM-DD"
                                />
                                <span class="text-error" v-if="errors.date_promotion">La expiración es obligatoria.</span>
                            </el-col>
                        </el-row>
                    </el-col>
                </el-row>
            </el-col>
        </el-row>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="activeEditTicket = false">Cancelar</el-button>
                <el-button type="primary" @click="saveInfo" :disabled="isDisabled">Guardar cambios</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';

export default {
    props: {
        eventId: Number,
        daysEvent: Number,
        getTickets: Function,
        modelPayment: String
    },
    data() {
        return {
            activeEditTicket: false,
            isDisabled: false,
            eventType: 'paid',
            ticket: {
                ticket_id: null,
                event_id: this.eventId,
                name: '',
                description: '',
                price: 0,
                quantity: 50,
                start_sale: '',
                stop_sale: '',
                cost_type: 'paid',
                min_reservation: 1,
                max_reservation: 10,
                valid: '',
                discount: false,
                promotion: 0,
                date_promotion: ''
            },
            errors: {
                name: false,
                quantity: false,
                start_sale: false,
                stop_sale: false,
                dates: false,
                min_reservation: false,
                max_reservation: false,
                min: false,
                price: false,
                price_incorrect: false,
                valid: false,
                promotion: false,
                promotion_invalid: false,
                date_promotion: false
            }
        }
    },
    methods: {
        async saveInfo() {
            if (this.validate()) {
                let response    = '';
                this.isDisabled = true;
                if (!this.ticket.ticket_id) {
                    response = await apiClient('customer/ticket', 'POST', this.ticket);
                } else {
                    response = await apiClient('customer/ticket', 'PUT', this.ticket);
                }
                this.isDisabled = false;
                if (response.error) {
                    showNotification('¡Error!', response.msj, 'error');
                    return false;
                }
                this.getTickets();
                showNotification('¡Correcto!', response.msj, 'success');
                this.activeEditTicket = false;
            }
        },
        validateCost() {
            if (this.ticket.cost_type == 'free') {
                this.ticket.price          = 0;
                this.ticket.discount       = false;
                this.ticket.promotion      = 0;
                this.ticket.date_promotion = '';
            }
        },
        validate() {
            this.resetErrors();
            let valid = true;
            if (!this.ticket.name) {
                this.errors.name = true;
                valid            = false;
            }
            if (!this.ticket.quantity) {
                this.errors.quantity = true;
                valid                = false;
            }
            if (!this.ticket.valid) {
                this.errors.valid = true;
                valid             = false;
            }
            if (!this.ticket.start_sale) {
                this.errors.start_sale = true;
                valid                  = false;
            }
            if (!this.ticket.stop_sale) {
                this.errors.stop_sale = true;
                valid                 = false;
            }
            if (this.ticket.start_sale > this.ticket.stop_sale) {
                this.errors.dates = true;
                valid             = false;
            }
            // if (!this.ticket.min_reservation) {
            //     this.errors.min_reservation = true;
            //     valid                       = false;
            // }
            // if (!this.ticket.max_reservation) {
            //     this.errors.max_reservation = true;
            //     valid                       = false;
            // }
            // if (this.ticket.min_reservation > this.ticket.max_reservation) {
            //     this.errors.min = true;
            //     valid           = false;
            // }
            if (this.eventType == 'paid' && this.ticket.cost_type == 'paid') {
                if (!this.ticket.price || !parseInt(this.ticket.price)) {
                    this.errors.price_incorrect = true;
                    valid                       = false;
                } else if (this.ticket.price < 100) {
                    this.errors.price = true;
                    valid             = false;
                }
            }
            if (this.ticket.discount) {
                if (!this.ticket.promotion || this.ticket.promotion == 0) {
                    this.errors.promotion = true;
                    valid                 = false;
                } else if (this.ticket.promotion > 60) {
                    this.errors.promotion_invalid = true;
                    valid                         = false;
                }
                if (!this.ticket.date_promotion) {
                    this.errors.date_promotion = true;
                    valid                      = false;
                }
            }
            return valid;
        },
        showModal(_ticket = null) {
            this.resetErrors();
            this.ticket.ticket_id       = null;
            this.ticket.name            = '';
            this.ticket.description     = '';
            this.ticket.price           = 0;
            this.ticket.quantity        = 50;
            this.ticket.start_sale      = '';
            this.ticket.stop_sale       = '';
            this.ticket.cost_type       = 'paid';
            this.ticket.min_reservation = 1;
            this.ticket.max_reservation = 10;
            this.ticket.valid           = '';
            this.ticket.discount        = false;
            this.ticket.promotion       = 0;
            this.ticket.date_promotion  = '';
            if (_ticket) {
                this.eventType              = _ticket.event.cost_type;
                this.ticket.ticket_id       = _ticket.id;
                this.ticket.name            = _ticket.name;
                this.ticket.description     = _ticket.description;
                this.ticket.valid           = _ticket.valid;
                this.ticket.start_sale      = _ticket.start_sale;
                this.ticket.stop_sale       = _ticket.stop_sale;
                this.ticket.price           = _ticket.price ? _ticket.price : 0;
                this.ticket.min_reservation = _ticket.min_reservation;
                this.ticket.max_reservation = _ticket.max_reservation;
                this.ticket.quantity        = _ticket.quantity;
                this.ticket.cost_type       = _ticket.event.cost_type == 'paid' ? 'paid' : 'free';
                this.ticket.discount        = _ticket.promotion ? true : false;
                this.ticket.promotion       = _ticket.promotion ? _ticket.promotion : 0;
                this.ticket.date_promotion  = _ticket.date_promotion ? _ticket.date_promotion : '';
            }
            this.activeEditTicket = true;
        },
        resetErrors() {
            this.errors.name              = false;
            this.errors.quantity          = false;
            this.errors.start_sale        = false;
            this.errors.stop_sale         = false;
            this.errors.dates             = false;
            this.errors.min_reservation   = false;
            this.errors.max_reservation   = false;
            this.errors.min               = false;
            this.errors.price             = false;
            this.errors.price_incorrect   = false;
            this.errors.valid             = false;
            this.errors.promotion         = false,
            this.errors.promotion_invalid = false,
            this.errors.date_promotion    = false;
        },
        isNumber(evt) {
            const charCode = evt.which ? evt.which : evt.keyCode;
            if (charCode < 48 || charCode > 57) {
                evt.preventDefault();
            }
        },
    }
}
</script>

<style scoped>
    
</style>