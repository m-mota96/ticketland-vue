<script setup lang="js">
import { ref, onMounted, defineExpose } from 'vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { VueTelInput } from 'vue3-tel-input';
import 'vue3-tel-input/dist/vue3-tel-input.css';

const viewFormTickets = defineModel();

const appUrl           = window.location.origin;
const gutterValue      = window.innerWidth <= 768 ? 0 : 20;
const loading          = ref(false);
const loadingTickets   = ref(false);
const txtLoading       = ref('compra');
const formTickets      = ref([]);
const payment_methods  = ref([]);
const disabledDiscount = ref(false);
const svg              = ref(`
    <path class="path" d="
    M 30 15
    L 28 17
    M 25.61 25.61
    A 15 15, 0, 0, 1, 15 30
    A 15 15, 0, 1, 1, 27.99 7.5
    L 15 15
    " style="stroke-width: 4px; fill: rgba(0, 0, 0, 0)"/>
`);
const order = ref({
    event_id: null,
    name: '',
    email: '',
    confirm_email: '',
    phone: '',
    payment_method_id: '',
    payment_method: '',
    token_id: '',
    card: '',
    code_id: null,
    code: '',
    code_discount: 0,
    paypal_order_id: '',
    device_session_id: ''
});

const loadForm = (_event_id, _tickets, _payment_methods) => {
    payment_methods.value = _payment_methods;
    order.value.event_id  = _event_id;
    formTickets.value     = [];

    let pos = 0;
    _tickets.forEach(t => {
        for (let index = 0; index < t.quantity_to_purchase; index++) {
            formTickets.value.push({
                id: t.id,
                code_id: null,
                code: '',
                code_discount: 0,
                name: t.name,
                price: parseInt(t.price),
                priceDiscount: t.priceDiscount ? parseInt(t.priceDiscount) : null,
                promotion: t.promotion,
                checked: false,
                number_of_access: t.number_of_access,
                questions: t.questions,
                inputs: []
            });
            // Aquí se revisa cuántos accesos da ese boleto (si esta marcado como paquete debe dar de 2 accesos en adelante).
            for (let j = 0; j < t.number_of_access; j++) {
                // Los datos que se piden por default son nombre, correo y teléfono.
                formTickets.value[pos].inputs.push({
                    name: '',
                    email: '',
                    phone: '',
                    question0: '', // Agregamos estos campos por si el administrador añade campos adicionales para llenar el boleto.
                    question1: '', // Agregamos estos campos por si el administrador añade campos adicionales para llenar el boleto.
                    question2: '', // Agregamos estos campos por si el administrador añade campos adicionales para llenar el boleto.
                    question3: '', // Agregamos estos campos por si el administrador añade campos adicionales para llenar el boleto.
                    question4: '', // Agregamos estos campos por si el administrador añade campos adicionales para llenar el boleto.
                });
            }
            pos++;
        }
    });

    console.log(formTickets.value);
    viewFormTickets.value = true;
};

const verifyCodes = async (action = null) => {
    if (action === 'delete') {
        order.value.code_id       = null;
        order.value.code          = '';
        order.value.code_discount = 0;
        disabledDiscount.value    = false;
        return;
    }
    
    if (order.value.code) {
        loadingTickets.value = true;
        const response = await apiClient('verifyCodes', 'GET', {event_id: order.value.event_id, code: order.value.code});
        loadingTickets.value = false;
        if (response.error) {
            order.value.code_id       = null;
            order.value.code          = '';
            order.value.code_discount = 0;
            showNotification('¡Error!', response.msj, 'error', 8000);
            return false;
        }
        disabledDiscount.value = true;
        // this.data.discount     = response.data.discount;
        const idsSet           = new Set(response.data.tickets);
        console.log(idsSet);

        // Verifica si el cupón ingresado puede ser aplicado a alguno de los boletos que etsan comprando.
        let isApplicable = false;
        formTickets.value.forEach(t => {
            if (idsSet.has(t.id)) {
                isApplicable    = true;
                t.code_id       = response.data.code_id;
                t.code          = response.data.code;
                t.code_discount = response.data.discount;
            }
        });

        // this.data.tickets.forEach(t => {
        //     if (idsSet.has(t.id)) {
        //         t.code_id       = response.data.code_id;
        //         t.code          = response.data.code;
        //         t.code_discount = response.data.discount;
        //     }
        // });
        if (isApplicable) {
            // this.totals();
            showNotification('¡Correcto!', 'Cupón aplicado.', 'success', 5000);
        } else {
            showNotification('¡Atención!', 'Tu cupón es válido.<br>Pero no aplica para ningún boleto seleccionado (no se aplicaron descuentos).', 'warning', 15000);
            verifyCodes('delete');
        }
    }
};

const viewTickets = () => {
    viewFormTickets.value = false;
};

const autoComplete = () => {

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

const onPhoneChange = (val) => {
    if (typeof val === 'string') {
        // this.data.order.phone = val.replaceAll(' ', '');
    } else if (val && val.number) {
        // this.data.order.phone = val.number.replaceAll(' ', '');
    }
};

const onPhoneChangeTickets = (val, index) => {
    if (val) {
        if (typeof val === 'string') {
            // this.data.ticketsReserved[index].phone = val.replaceAll(' ', '');
        } else if (val && val.number) {
            // this.data.ticketsReserved[index].phone = val.number.replaceAll(' ', '');
        }
    }
};

const formatInput = (value) => {
    const formatted  = value.toUpperCase().replace(/[^A-Z0-9]/gi, '').trim();
    order.value.code = formatted;
};

defineExpose({
    loadForm
});
</script>

<template>
    <el-row
        ref="dataOrder"
        class="custom-loading-svg container-fluid has-background-white pt-6 pb-6 padding b-b"
        v-if="viewFormTickets"
        :element-loading-text="`¡Procesando tu ${txtLoading}. No cierres ni actualices esta página, por favor espera!`"
        v-loading="loading"
        :element-loading-svg="svg"
        element-loading-svg-view-box="-10, -10, 50, 50"
        element-loading-background="rgba(0, 0, 0, 0.9)"
    >
        <el-col :xs="24" :sm="24" :md="24" :lg="{span: 14, offset: 5}" :xl="{span: 14, offset: 5}">
            <el-row :gutter="gutterValue" class="mb-6">
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
                        
                        name="name"
                        id="name"
                        autocomplete="name"
                        v-model="order.name"
                        placeholder="Nombre completo"
                    />
                    <!-- <span class="text-error" v-if="errors.name">El nombre es obligatorio.</span> -->
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mb-3">
                    <label class="bold has-text-dark" for="phone">Teléfono <span class="has-text-danger">*</span></label>
                    <VueTelInput
                        v-model="order.phone"
                        mode="international"
                        class="mt-1"
                        
                        style="color: #606266; height: 32px;"
                        :auto-format="true"
                        :input-options="{ placeholder: 'Ingresa tu número de teléfono', name: 'phone', id: 'phone', autocomplete: 'phone' }"
                        @input="onPhoneChange"
                        defaultCountry="MX"
                    />
                    <!-- <span class="text-error" v-if="errors.phone">El teléfono es obligatorio.</span>
                    <span class="text-error" v-if="errors.phone_invalid">Teléfono inválido.</span> -->
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mb-3">
                    <label class="bold has-text-dark" for="email">Correo <span class="has-text-danger">*</span></label>
                    <el-input
                        class="el-form-item mb-0 mt-1"
                        
                        name="email"
                        id="email"
                        autocomplete="email"
                        v-model="order.email"
                        placeholder="Correo electrónico"
                    />
                    <!-- <span class="text-error" v-if="errors.email">El correo es obligatorio.</span>
                    <span class="text-error" v-if="errors.email_invalid">Correo inválido.</span>
                    <span class="text-error" v-if="errors.confirm_email_invalid2">Los correos no coinciden.</span> -->
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mb-3">
                    <label class="bold has-text-dark" for="confirm_email">Confirmar correo <span class="has-text-danger">*</span></label>
                    <el-input
                        class="el-form-item mb-0 mt-1"
                        
                        name="email"
                        id="confirm_email"
                        autocomplete="email"
                        v-model="order.confirm_email"
                        placeholder="Confirmar correo electrónico"
                    />
                    <!-- <span class="text-error" v-if="errors.confirm_email">Confirme el correo.</span>
                    <span class="text-error" v-if="errors.confirm_email_invalid">Correo inválido.</span>
                    <span class="text-error" v-if="errors.confirm_email_invalid2">Los correos no coinciden.</span> -->
                </el-col>
                <el-col :span="24">
                    <i class="has-text-dark"><font-awesome-icon :icon="['fas', 'circle-info']" /> Debes de tener acceso al correo ya que a esta dirección se enviarán los boletos.</i>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mb-3 mt-4" :inline="true">
                    <el-row :gutter="5">
                        <el-col :xs="17" :sm="17" :md="18" :lg="16" :xl="18">
                            <label class="bold has-text-dark">¿Tienes un cupón de descuento?</label>
                            <el-input
                                class="el-form-item mb-0"
                                :class="{'is-error': false}"
                                v-model="order.code"
                                placeholder="Ingresa tu cupón"
                                @input="formatInput"
                                :disabled="disabledDiscount"
                            />
                        </el-col>
                        <el-col :xs="7" :sm="7" :md="6" :lg="8" :xl="6">
                            <br>
                            <el-button class="w-100" type="success" @click="verifyCodes" v-if="!order.code_id">Validar cupón</el-button>
                            <el-button class="w-100" type="danger" @click="verifyCodes('delete')" v-if="order.code_id">Borrar cupón</el-button>
                        </el-col>
                    </el-row>
                </el-col>
            </el-row>
            <el-row :gutter="gutterValue" class="mb-6">
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
                <el-col :span="24" v-loading="loadingTickets">
                    <el-row>
                        <el-card class="w-100 mb-5 my-card" v-for="(t, index) in formTickets" :key="index">
                            <template #header>
                                <div class="card-header">
                                    <el-col :span="24">
                                        <el-row :gutter="gutterValue">
                                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                                <span>Boleto {{ (index + 1) }} - <b class="has-text-primary">{{ t.name }}</b></span>
                                                <p v-if="t.promotion && !t.code_id">
                                                    Precio
                                                    <span class="subtitle is-6 has-text-gray mb-0"><del>{{ formatCurrency(t.price) }}</del></span>
                                                    <span class="subtitle is-5 !text-orange-500 bold ml-2">{{ formatCurrency(t.price - Math.round(t.price * (t.promotion / 100))) }}</span>
                                                </p>
                                                <p v-if="!t.promotion && !t.code_id">
                                                    Precio
                                                    <span class="subtitle is-5 !text-orange-500 bold">{{ formatCurrency(t.price) }}</span>
                                                </p>
                                                <p v-if="t.code_id">
                                                    Precio
                                                    <span class="subtitle is-6 has-text-gray mb-0"><del>{{ formatCurrency(t.price) }}</del></span>
                                                    <span class="subtitle is-5 !text-orange-500 bold ml-2">{{ formatCurrency(t.price - Math.round(t.price * (t.code_discount / 100))) }}</span>
                                                </p>
                                                <p class="mb-0" v-if="t.code_id">
                                                    Cupón de descuento aplicado
                                                    <span class="text-blue-500 ml-2">
                                                        <font-awesome-icon :icon="['fas', 'tags']" />
                                                        {{ t.code }} ({{ t.code_discount }}%)
                                                    </span>
                                                </p>
                                            </el-col>
                                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="has-text-right" v-if="t.number_of_access < 2">
                                                <el-checkbox class="bold" :class="{'w-100': gutterValue == 0}" v-model="t.checked" label="Autocompletar este boleto con los datos de la orden." size="large" @change="(val) => autoComplete(val, index)" />
                                            </el-col>
                                        </el-row>
                                    </el-col>
                                </div>
                            </template>
                            <el-row :gutter="gutterValue" v-for="(input, key_index) in t.inputs" :key="key_index">
                                <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8" class="mb-3">
                                    <label class="bold has-text-dark">Nombre completo <span class="has-text-danger">*</span></label>
                                    <el-input
                                        class="el-form-item mb-0 mt-1"
                                        v-model="input.name"
                                        name="name"
                                        autocomplete="name"
                                        placeholder="Nombre completo"
                                    />
                                </el-col>
                                <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8" class="mb-3">
                                    <label class="bold has-text-dark">Correo</label>
                                    <el-input
                                        class="el-form-item mb-0 mt-1"
                                        :class="{'is-error': false}"
                                        v-model="input.email"
                                        name="email"
                                        autocomplete="email"
                                        placeholder="Correo electrónico"
                                    />
                                </el-col>
                                <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8" class="mb-3">
                                    <label class="bold has-text-dark">Teléfono</label>
                                    <VueTelInput
                                        v-model="input.phone"
                                        :value="input.phone"
                                        mode="international"
                                        class="mt-1"
                                        style="color: #606266; height: 32px;"
                                        :auto-format="true"
                                        :input-options="{ placeholder: 'Número de teléfono' }"
                                        @input="(val) => onPhoneChangeTickets(val, index)"
                                        defaultCountry="MX"
                                    />
                                </el-col>
                                <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8" class="mb-3" v-for="(question, qt) in t.questions" :key="question.id">
                                    <label class="bold has-text-dark">{{ question.title }} <span class="has-text-danger" v-if="question.required">*</span></label>
                                    <el-input
                                        class="el-form-item mb-0 mt-1"
                                        
                                        v-model="input[`question${qt}`]"
                                        v-if="question.type === 'text'"
                                        :placeholder="question.information"
                                    />
                                    <el-input
                                        class="el-form-item mb-0 mt-1"
                                        
                                        v-model="input[`question${qt}`]"
                                        v-if="question.type === 'number'"
                                        :placeholder="question.information" @keypress="isNumber($event)"
                                    />
                                    <el-select
                                        class="el-form-item mb-0 mt-1"
                                        
                                        v-model="input[`question${qt}`]" 
                                        v-if="question.type === 'select'" 
                                        :placeholder="question.information || 'Elige una opción'"
                                        :clearable="!question.required"
                                    >
                                        <el-option v-for="(o, i) in question.options" :key="i" :value="o" :label="o" />
                                    </el-select>
                                    <el-mention
                                        class="el-form-item mb-0 mt-1"
                                        
                                        v-model="input[`question${qt}`]"
                                        v-if="question.type === 'textarea'"
                                        type="textarea"
                                        :rows="3"
                                        :placeholder="question.information"
                                    />
                                    <!-- <span class="text-error" v-if="errors.questions[index][iq]">Este campo es obligatorio.</span> -->
                                </el-col>
                                <el-divider v-if="t.inputs.length > 1 && key_index !== (t.inputs.length - 1)" />
                            </el-row>
                        </el-card>
                    </el-row>
                </el-col>
            </el-row>
            <!-- <el-row :gutter="gutterValue" class="mb-6">
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
                    <el-table class="w-100 mb-3" :data="filteredTickets" stripe header-cell-class-name="has-text-dark" empty-text="Ningún dato disponible en esta tabla">
                        <el-table-column label="Producto" width="180">
                            <template #default="scope">
                                <span>{{ scope.row.name }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="Cantidad" align="center">
                            <template #default="scope">
                                {{ scope.row.quantity }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Precio unitario">
                            <template #default="scope">
                                <span v-if="!scope.row.priceWithDiscount">
                                    <span v-if="!scope.row.promotion" class="has-text-success">{{ scope.row.priceUnit }}</span>
                                    <del v-if="scope.row.promotion && !data.discount" class="has-text-danger">{{ scope.row.priceUnit }}</del>
                                    <span v-if="scope.row.promotion && data.discount" class="has-text-success">{{ scope.row.priceUnit }}</span>
                                </span>
                                <span v-if="scope.row.priceWithDiscount" class="has-text-success">
                                    {{ formatCurrency(scope.row.priceWithDiscount) }} MXN
                                </span>
                            </template>
                        </el-table-column>
                        <el-table-column label="Precio c/descuento">
                            <template #default="scope">
                                <span v-if="scope.row.promotion && !data.discount" class="has-text-success">{{ formatCurrency(scope.row.priceDiscount) }} MXN</span>
                                <del v-if="scope.row.promotion && data.discount" class="has-text-danger">{{ formatCurrency(scope.row.priceDiscount) }} MXN</del>
                                <span v-if="!scope.row.promotion" class="has-text-danger"><del>N/A</del></span>
                            </template>
                        </el-table-column>
                        <el-table-column label="Subtotal">
                            <template #default="scope">
                                {{ scope.row.subtotal }}
                            </template>
                        </el-table-column>
                    </el-table>
                    <i class="has-text-link"><font-awesome-icon :icon="['fas', 'circle-info']" /> Si aplicas un cupón de descuento no se tomará en cuenta el precio con descuento del boleto.</i>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mt-6">
                    <label class="bold has-text-dark" for="payment_method">Método de pago <span class="has-text-danger">*</span></label>
                    <el-select
                        :class="{'is-error': errors.payment_method}"
                        class="el-form-item mb-0"
                        v-model="data.order.payment_method"
                        placeholder="Selecciona una opción"
                        id="payment_method"
                        clearable
                        @change="(val) => verifyPaymentMethod(val)"
                        >
                            <el-option v-for="pm in event.payment_methods" :key="pm.id" :label="pm.name" :value="pm.sku" />
                    </el-select>
                    <span class="text-error" v-if="errors.payment_method">El método de pago es obligatorio.</span>
                </el-col>
                <el-col :span="24" class="has-text-left mt-6">
                    <h6 class="subtitle is-5 has-text-black mb-2">
                        Subtotal: <b>{{ formatCurrency(data.subtotal) }} MXN</b>
                    </h6>
                    <h6 class="subtitle is-5 has-text-black mb-2" v-if="event.model_payment == 'separated'">
                        Cargo por servicio: <b>{{ formatCurrency(data.commission) }} MXN</b>
                    </h6>
                    <h6 class="subtitle is-5 has-text-black mb-2">
                        Total a pagar: <b class="has-text-success">{{ formatCurrency(data.subtotal + data.commission) }} MXN</b>
                    </h6>
                </el-col>
                <el-col :span="24" class="pt-5 pb-5">
                    <el-row :gutter="gutterValue">
                        <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" v-if="data.order.payment_method == 'card'" class="mb-3">
                            <label class="bold has-text-dark" for="cardName">Nombre en la tarjeta <span class="has-text-danger">*</span></label>
                            <el-input
                                :class="{'is-error': errors.cardName}"
                                class="el-form-item mb-0"
                                name="name"
                                id="cardName"
                                autocomplete="name"
                                v-model="data.paymentData.card.name"
                                placeholder="Nombre del propietario de la tarjeta"
                                type="text"
                            />
                            <span class="text-error" v-if="errors.cardName">El nombre del propietario es obligatorio.</span>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" v-if="data.order.payment_method == 'card'" class="mb-3">
                            <label class="bold has-text-dark" for="cardNumber">Número de tarjeta <span class="has-text-danger">*</span></label>
                            <el-input
                                :class="{'is-error': errors.cardNumber || errors.cardInvalid}"
                                class="el-form-item mb-0"
                                id="cardNumber"
                                v-mask="'#### #### #### ####'"
                                v-model="data.paymentData.card.number"
                                placeholder="1234 5678 9012 3456"
                                maxlength="19"
                                clearable
                            />
                            <span class="text-error" v-if="errors.cardNumber">El número de tarjeta es obligatorio.</span>
                            <span class="text-error" v-if="errors.cardInvalid">El número de tarjeta debe contener 16 dígitos.</span>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" v-if="data.order.payment_method == 'card'" class="mb-3">
                            <label class="bold has-text-dark" for="expiration">Fecha de expiración <span class="has-text-danger">*</span></label>
                            <el-input
                                :class="{'is-error': errors.expiration || errors.month_invalid || errors.year_invalid}"
                                class="el-form-item mb-0"
                                id="expiration"
                                v-mask="'##/##'"
                                v-model="data.cardExpiration"
                                placeholder="MM/AA"
                                maxlength="5"
                                clearable
                                @keyup="setExpiration"
                            />
                            <p class="text-error" v-if="errors.expiration">Completa el mes y el año.</p>
                            <p class="text-error" v-if="errors.month_invalid">Ingresa un mes válido.</p>
                            <p class="text-error" v-if="errors.year_invalid">El año debe ser mayor o igual que el actual.</p>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" v-if="data.order.payment_method == 'card'" class="mb-3">
                            <label class="bold has-text-dark" for="cvv">CVV <span class="has-text-danger">*</span></label>
                            <el-input
                                :class="{'is-error': errors.cvc}"
                                class="el-form-item mb-0"
                                id="cvv"
                                v-model="data.paymentData.card.cvc"
                                placeholder="CVV"
                                maxlength="3"
                                @keypress="isNumber($event)"
                            />
                            <span class="text-error" v-if="errors.cvc">El código de seguridad es obligatorio.</span>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="data.order.payment_method == 'paypal'" class="text-center justify-content-center">
                            <PaypalButton
                                ref="PaypalButton"
                                :amount="event.model_payment === 'separated' ? (data.subtotal + data.commission) : data.subtotal"
                                @update-orderId="data.order.paypal_order_id = $event"
                                :handleMakePayment="payment"
                            />
                        </el-col>
                    </el-row>
                </el-col>
                <el-col :span="24" class="has-text-centered mt-3">
                    <el-button type="primary" size="large" @click="payment" v-if="event.status == 1 && (data.order.payment_method === 'oxxo' || data.order.payment_method === 'card')">
                        <font-awesome-icon :icon="['fas', 'dollar-sign']" v-if="data.order.payment_method == 'card'" />
                        <font-awesome-icon :icon="['fas', 'check']" v-if="data.order.payment_method == 'oxxo'" />
                        &nbsp;&nbsp;{{ data.order.payment_method == 'card' ? 'Realizar pago' : 'Realizar pedido' }}
                    </el-button>
                </el-col>
            </el-row> -->
        </el-col>
    </el-row>
</template>

<style scoped>

</style>