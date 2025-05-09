<template>
    <el-dialog
        v-model="activeEditDiscount"
        title="Agrega un código de descuento"
        width="500"
        align-center
        style="margin-top: 2% !important;"
    >
        <el-row :gutter="20" class="mb-4">
            <el-col :span="12" class="mb-5">
                <label class="bold" for="code">Ingrese el código <span class="has-text-danger">*</span></label>
                <el-input
                    class="el-form-item mb-0 mt-1"
                    :class="{'is-error': errors.code}"
                    size="large"
                    id="code"
                    v-model="code.code"
                    placeholder="p.ej: BUENFIN, BLACKFRIDAY"
                    @input="formatInput"
                />
                <span class="text-error" v-if="errors.code">El código es obligatorio.</span>
            </el-col>
            <el-col :span="12" class="mb-5">
                <label class="bold" for="discount">Porcentaje de descuento <span class="has-text-danger">*</span></label>
                <el-input
                    class="el-form-item mb-0 mt-1"
                    :class="{'is-error': errors.discount}"
                    size="large"
                    id="discount"
                    v-model="code.discount"
                    @keypress="isNumber($event)"
                />
                <span class="text-error" v-if="errors.discount">El porcentaje es obligatorio.</span>
            </el-col>
            <el-col :span="12" class="mb-5">
                <label class="bold" for="quantity">Cantidad disponible <span class="has-text-danger">*</span></label>
                <el-input
                    class="el-form-item mb-0 mt-1"
                    :class="{'is-error': errors.quantity}"
                    size="large"
                    id="quantity"
                    v-model="code.quantity"
                    @keypress="isNumber($event)"
                />
                <span class="text-error" v-if="errors.quantity">La cantidad es obligatoria.</span>
            </el-col>
            <el-col :span="12" class="mb-5">
                <label class="bold" for="tickets">Asignar código a <span class="has-text-danger">*</span></label>
                <el-select
                    class="el-form-item mb-0 mt-1"
                    :class="{'is-error': errors.tickets}"
                    v-model="ticketsCheck"
                    id="tickets"
                    multiple
                    clearable
                    collapse-tags
                    placeholder="Elija uno o más boletos"
                    popper-class="custom-header"
                    :max-collapse-tags="1"
                    size="large"
                    no-data-text="Sin datos"
                >
                    <template #header>
                    <el-checkbox
                        v-model="code.checkAll"
                        :indeterminate="indeterminate"
                        @change="handleCheckAll"
                    >
                        Seleccionar todo
                    </el-checkbox>
                    </template>
                    <el-option
                    v-for="t in tickets"
                    :key="t.id"
                    :label="t.name"
                    :value="t.id"
                    />
                </el-select>
                <span class="text-error" v-if="errors.tickets">Debe elegir al menos un boleto.</span>
            </el-col>
            <el-col :span="12" class="mb-5">
                <label class="bold" for="expiration">Fecha de expiración <span class="has-text-grey-light">(Opcional)</span></label>
                <el-date-picker
                    class="el-form-item mb-0 mt-1 w-100"
                    :class="{'is-error': errors.expiration}"
                    size="large"
                    id="expiration"
                    v-model="code.expiration"
                    type="date"
                    placeholder="Elija la fecha"
                    format="DD/MM/YYYY"
                    value-format="YYYY-MM-DD"
                />
                <span class="text-error" v-if="errors.expiration">La fecha de expiración es obligatoria.</span>
            </el-col>
        </el-row>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="activeEditDiscount = false">Cancelar</el-button>
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
        getDiscounts: Function,
        tickets: Array
    },
    data() {
        return {
            activeEditDiscount: false,
            isDisabled: false,
            indeterminate: false,
            ticketsCheck: [],
            code: {
                id: null,
                event_id: this.eventId,
                code: '',
                discount: 0,
                quantity: 0,
                expiration: '',
                checkAll: false,
                tickets: []
            },
            errors: {
                name: false,
                discount: false,
                quantity: false,
                tickets: false,
                expiration: false,
            }
        }
    },
    mounted() {
        // console.log(this.tickets);
    },
    watch: {
        ticketsCheck(val) {
            if (val.length === 0) {
                this.checkAll = false;
                this.indeterminate = false;
            } else if (val.length === this.tickets.length) {
                this.checkAll = true;
                this.indeterminate = false;
            } else {
                this.checkAll = false;
                this.indeterminate = true;
            }
        }
    },
    methods: {
        async saveInfo() {
            if (this.validate()) {
                let response      = '';
                this.isDisabled   = true;
                this.code.tickets = this.ticketsCheck;
                if (!this.code.id) {
                    response = await apiClient('customer/discount', 'POST', this.code);
                } else {
                    response = await apiClient('customer/discount', 'PUT', this.code);
                }
                this.isDisabled = false;
                if (response.error) {
                    showNotification('¡Error!', response.msj, 'error');
                    return false;
                }
                this.getDiscounts();
                showNotification('¡Correcto!', response.msj, 'success');
                this.activeEditDiscount = false;
            }
        },
        handleCheckAll(val) {
            this.indeterminate = false;
            if (val) {
                this.ticketsCheck = this.tickets.map(ticket => ticket.id);
            } else {
                this.ticketsCheck = [];
            }
        },
        validate() {
            this.resetErrors();
            let valid = true;
            if (!this.code.code) {
                this.errors.code = true;
                valid            = false;
            }
            if (!this.code.discount) {
                this.errors.discount = true;
                valid                = false;
            }
            if (!this.code.quantity) {
                this.errors.quantity = true;
                valid                = false;
            }
            if (this.ticketsCheck.length === 0) {
                this.errors.tickets = true;
                valid               = false;
            }
            return valid;
        },
        showModal(_code = null) {
            this.resetErrors();
            this.code.id         = null;
            this.code.event_id   = this.eventId;
            this.code.code       = '';
            this.code.discount   = 0;
            this.code.quantity   = 0;
            this.code.expiration = '';
            this.code.checkAll   = false;
            this.indeterminate   = false;
            this.ticketsCheck    = [];
            if (_code) {
                // console.log(_code.tickets.length, this.tickets.length);
                this.code.id         = _code.id;
                this.code.code       = _code.code;
                this.code.discount   = _code.discount;
                this.code.quantity   = _code.quantity;
                this.code.expiration = _code.expiration;
                if (_code.tickets.length === this.tickets.length) {
                    this.code.checkAll = true;
                    this.ticketsCheck  = _code.tickets.map(ticket => ticket.id);
                } else if(_code.tickets.length > 0) {
                    this.indeterminate = true;
                    this.ticketsCheck  = _code.tickets.map(ticket => ticket.id);
                }
            }
            this.activeEditDiscount = true;
        },
        resetErrors() {
            this.errors.code       = false;
            this.errors.discount   = false;
            this.errors.quantity   = false;
            this.errors.tickets    = false;
            this.errors.expiration = false;
        },
        isNumber(evt) {
            const charCode = evt.which ? evt.which : evt.keyCode;
            if (charCode < 48 || charCode > 57) {
                evt.preventDefault();
            }
        },
        formatInput(event) {
            const formatted = event.toUpperCase().replace(/\s+/g, '');
            this.code.code = formatted;
        }
    }
}
</script>

<style scoped>
    
</style>