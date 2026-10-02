<script setup lang="js">
import { ref, defineExpose, nextTick } from 'vue';
import apiClient from '@/apiClient';
import apiClientPayments from '@/apiClientPayments.js';
import { showNotification } from '@/notification';
import { VueTelInput } from 'vue3-tel-input';
import 'vue3-tel-input/dist/vue3-tel-input.css';
import ConektaFrame from './ConektaFrame.vue';
import PaypalFrame from './PaypalFrame.vue';
import { ElMessageBox, ElLoading } from 'element-plus';

const { totalsParent, scrollToInfoParent, resetFormParent } = defineProps({
    totalsParent: {
        type: Function,
        required: true
    },
    scrollToInfoParent: {
        type: Function,
        required: true
    },
    resetFormParent: {
        type: Function,
        required: true
    }
});

const viewFormTickets = defineModel();

const totalsRef        = ref(null);
const dataOrder        = ref(null);
const gutterValue      = window.innerWidth <= 768 ? 0 : 20;
const loading          = ref(false);
const loadingTickets   = ref(false);
const txtLoading       = ref('compra');
const tickets          = ref([]);
const formTickets      = ref([]);
const payment_methods  = ref([]);
const disabledDiscount = ref(false);
const viewConektaFrame = ref(false);
const viewPaypalFrame  = ref(false);
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
    event_status: null,
    model_payment: '',
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
    device_session_id: '',
    subtotal: 0,
    commission: 0
});
const errors = ref({
    name: [],
    phone: [],
    email: [],
    confirm_email: [],
    payment_method: [],
});

const loadForm = (_event, _tickets) => {
    payment_methods.value     = _event.payment_methods;
    order.value.event_id      = _event.id;
    order.value.event_status  = _event.status;
    order.value.model_payment = _event.model_payment;
    tickets.value             = _tickets;
    formTickets.value         = [];

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
                    error_name: [],
                    email: '',
                    error_email: [],
                    phone: '',
                    error_phone: [],
                    question: [ // Agregamos estos campos por si el administrador añade campos adicionales para llenar el boleto.
                        {
                            id: t.questions[0]?.id || null,
                            required: t.questions[0]?.required || null,
                            error: false,
                            response: ''
                        },
                        {
                            id: t.questions[1]?.id || null,
                            required: t.questions[1]?.required || null,
                            error: false,
                            response: ''
                        },
                        {
                            id: t.questions[2]?.id || null,
                            required: t.questions[2]?.required || null,
                            error: false,
                            response: ''
                        },
                        {
                            id: t.questions[3]?.id || null,
                            required: t.questions[3]?.required || null,
                            error: false,
                            response: ''
                        },
                        {
                            id: t.questions[4]?.id || null,
                            required: t.questions[4]?.required || null,
                            error: false,
                            response: ''
                        },
                    ]
                });
            }
            pos++;
        }
    });

    // console.log(formTickets.value);
    if (order.value.code) {
        verifyCodes(null, false);
    }
    totals();
    viewFormTickets.value = true;
    scrollToTickets();
};

const verifyCodes = async (action = null, view_msg = true) => {
    formTickets.value.forEach(t => {
        t.code_id       = null;
        t.code          = '';
        t.code_discount = 0;
    });
    tickets.value.forEach(t => {
        t.code_id       = null;
        t.code          = '';
        t.code_discount = 0;
    });
    if (action === 'delete') {
        order.value.code_id       = null;
        order.value.code          = '';
        order.value.code_discount = 0;
        disabledDiscount.value    = false;
        totals();
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

        // Verifica si el cupón ingresado puede ser aplicado a alguno de los boletos que estan comprando.
        let isApplicable = false;
        formTickets.value.forEach(t => {
            if (idsSet.has(t.id)) {
                isApplicable    = true;
                t.code_id       = response.data.code_id;
                t.code          = response.data.code;
                t.code_discount = response.data.discount;
            }
        });

        tickets.value.forEach(t => {
            t.code_id       = null;
            t.code          = '';
            t.code_discount = 0;
            if (idsSet.has(t.id)) {
                t.code_id       = response.data.code_id;
                t.code          = response.data.code;
                t.code_discount = response.data.discount;
            }

            let price = t.promotion && !t.code_id
                ? t.priceDiscount // Si el boleto tiene una promoción y no aplican cupón de descuento, tomamos el precio con descuento.
                : t.price; // Si el boleto no tiene promoción o aplican cupón de descuento, tomamos el precio base.

            price = !t.code_id ? price : t.price - Math.round(t.price * (t.code_discount / 100));

            t.subtotal = price * t.quantity_to_purchase;
        });

        totals();

        if (isApplicable) {
            order.value.code_id       = response.data.code_id;
            order.value.code          = response.data.code;
            order.value.code_discount = response.data.discount;
            // Solo si aplican el cupón con el botón mostramos el mensaje, si van a elegir mas boletos y regresan al formulario ya no lo mostramos.
            if (view_msg) {
                showNotification('¡Correcto!', 'Cupón aplicado.', 'success', 5000);
            }
        } else {
            order.value.code_id       = null;
            order.value.code          = '';
            order.value.code_discount = 0;
            showNotification('¡Atención!', 'Tu cupón es válido.<br>Pero no aplica para ningún boleto seleccionado (no se aplicaron descuentos).', 'warning', 15000);
            verifyCodes('delete');
        }
    }
};

// Calcula el total a pagar por el cliente.
const totals = () => {
    order.value.subtotal = 0;
    tickets.value.forEach(t => {
        let price = t.promotion && !t.code_id
            ? t.priceDiscount // Si el boleto tiene una promoción y no aplican cupón de descuento, tomamos el precio con descuento.
            : t.price; // Si el boleto no tiene promoción o aplican cupón de descuento, tomamos el precio base.

        price = !t.code_id ? price : t.price - Math.round(t.price * (t.code_discount / 100));

        t.subtotal = price * t.quantity_to_purchase;
        order.value.subtotal = order.value.subtotal + t.subtotal;
    });

    if (order.value.model_payment === 'separated' && order.value.payment_method) {
        const payment_method   = payment_methods.value.find(pm => pm.sku === order.value.payment_method);
        order.value.commission = Math.round(order.value.subtotal * payment_method.commission);
    }
};

const confirmMakePayment = (token = null) => {
    order.value.token_id = token;

    if (order.value.payment_method === 'oxxo' && !validate()) {
        return
    }

    scrollToTickets();

    const txt = order.value.payment_method == 'card' || order.value.payment_method == 'paypal'
        ? `Tus boletos se enviarán al siguiente correo:<br><b>${order.value.email}</b><br>¿El correo esta correcto?<br>`
        : `Tu ficha de pago se enviará al siguiente correo:<br><b>${order.value.email}</b><br>¿El correo esta correcto?<br>Tendrás 48 horas para realizar tu pago.<br>`;
    const txtBtn = order.value.payment_method == 'card' || order.value.payment_method == 'paypal'
        ? 'Si, proceder al pago'
        : 'Si, realizar registro';

        ElMessageBox.confirm(
            txt,
            '¡Atención!',
            {
                dangerouslyUseHTMLString: true,
                confirmButtonText: txtBtn,
                cancelButtonText: 'Cancelar',
                type: 'warning',
                center: true,
                lockScroll: false
            }
        )
        .then(() => {
            makePayment();
        });
};

const makePayment = async () => {
    const loading = ElLoading.service({
        lock: true,
        text: `¡Procesando tu ${txtLoading.value}. No cierres ni actualices esta página, por favor espera!`,
        background: 'rgba(0, 0, 0, 0.9)',
        customClass: 'my-loading',
    });

    order.value.device_session_id = Math.random().toString(36).substring(2);

    const ti = tickets.value.map((t) => ({
        id: t.id,
        event_id: t.event_id,
        name: t.name,
        code_id: t.code_id,
        code: t.code,
        code_discount: t.code_discount,
        quantity_to_purchase: t.quantity_to_purchase
    }));

    const info = formTickets.value.map(({ questions, ...result }) => ({
        ...result
    }));

    const response = await apiClientPayments('makePayment', 'POST', {
        order: order.value,
        tickets: ti,
        informationTickets: info
    });
    loading.close();
    if (response.error) {
        showNotification('¡Error!', response.msj, 'error', 15000);
        return false;
    }
    resetFormParent();
    resetForm();
    viewFormTickets.value = false;
    showNotification('¡Correcto!', response.msj, 'success', 30000);
};

const viewTickets = () => {
    totalsParent(formTickets.value);
    viewFormTickets.value = false;
    scrollToInfoParent();
};

const verifyPaymentMethod = (value) => {
    viewConektaFrame.value = false;
    viewPaypalFrame.value  = false;
    switch (value) {
        case 'card':
            txtLoading.value = 'compra';
            if (!validate()) {
                order.value.payment_method = '';
                return
            }
            viewConektaFrame.value = true;
            scrollToTotalsRef();
            break;
        case 'paypal':
            txtLoading.value = 'compra';
            if (!validate()) {
                order.value.payment_method = '';
                return
            }
            viewPaypalFrame.value = true;
            scrollToTotalsRef();
            break;
        case 'oxxo':
            txtLoading.value = 'registro';
            break;
    }
    if (order.value.model_payment === 'separated') {
        const payment_method          = payment_methods.value.find(pm => pm.sku === value);
        order.value.commission        = Math.round(order.value.subtotal * payment_method.commission);
        order.value.payment_method_id = payment_method.id;
    }
};

const validate = () => {
    resetErrors();
    let valid       = true;
    const mailRegex = /^\w+([.-_+]?\w+)*@\w+([.-]?\w+)*(\.\w{2,10})+$/;

    // Validaciones de los datos de la orden.
    if (!order.value.name) {
        errors.value.name.push('El nombre es obligatorio.');
        valid = false;
    }
    if (!order.value.phone) {
        errors.value.phone.push('El teléfono es obligatorio.');
        valid = false;
    }
    if (!order.value.email) {
        errors.value.email.push('El correo es obligatorio.');
        valid = false;
    } else if (!mailRegex.test(order.value.email)) {
        errors.value.email.push('Formato del correo inválido.');
        valid = false;
    }
    if (!order.value.confirm_email) {
        errors.value.confirm_email.push('Confirma el correo.');
        valid = false;
    } else if (!mailRegex.test(order.value.confirm_email)) {
        errors.value.confirm_email.push('Formato del correo inválido.');
        valid = false;
    }
    if (order.value.email && order.value.confirm_email && mailRegex.test(order.value.email) && mailRegex.test(order.value.confirm_email)) {
        if (order.value.email !== order.value.confirm_email) {
            errors.value.email.push('Los correos no coinciden.');
            errors.value.confirm_email.push('Los correos no coinciden.');
            valid = false;
        }
    }

    // Validaciones de los datos de los boletos.
    formTickets.value.forEach(t => {
        t.inputs.forEach(i => {
            i.error_name  = [];
            i.error_email = [];
            i.error_phone = [];
            i.question[0].error = false;
            i.question[1].error = false;
            i.question[2].error = false;
            i.question[3].error = false;
            i.question[4].error = false;
            if (!i.name) {
                i.error_name.push('El nombre es obligatorio.');
                valid = false;
            }
            if (!i.email) {
                i.error_email.push('El correo es obligatorio.');
                valid = false;
            } else if (i.email && !mailRegex.test(i.email)) {
                i.error_email.push('Correo inválido.');
                valid = false;
            }
            if (!i.phone) {
                i.error_phone.push('El teléfono es obligatorio.');
                valid = false;
            }
            if (i.question[0].id && i.question[0].required && !i.question[0].response) {
                i.question[0].error = true;
                valid = false;
            }
            if (i.question[1].id && i.question[1].required && !i.question[1].response) {
                i.question[1].error = true;
                valid = false;
            }
            if (i.question[2].id && i.question[2].required && !i.question[2].response) {
                i.question[2].error = true;
                valid = false;
            }
            if (i.question[3].id && i.question[3].required && !i.question[3].response) {
                i.question[3].error = true;
                valid = false;
            }
            if (i.question[4].id && i.question[4].required && !i.question[4].response) {
                i.question[4].error = true;
                valid = false;
            }
        });
    });

    if (!valid) {
        scrollToTickets();
    }

    return valid;
};

const resetErrors = () => {
    errors.value.name           = [];
    errors.value.phone          = [];
    errors.value.email          = [];
    errors.value.confirm_email  = [];
    errors.value.payment_method = [];
};

const resetForm = () => {
    order.value.name              = '';
    order.value.phone             = '';
    order.value.email             = '';
    order.value.confirm_email     = '';
    order.value.token_id          = '';
    order.value.payment_method_id = '';
    order.value.payment_method    = '';
    order.value.card              = '';
    order.value.code_id           = null;
    order.value.code              = '';
    order.value.code_discount     = 0;
    order.value.device_session_id = '';
    order.value.subtotal          = 0;
    order.value.commission        = 0;
    viewConektaFrame.value        = false;
    viewPaypalFrame.value         = false;
};

const scrollToTickets = async () => {
    await nextTick();

    if (dataOrder.value?.$el) {
        dataOrder.value.$el.scrollIntoView({
            behavior: 'smooth'
        });
    }
};

const scrollToTotalsRef = async () => {
    await nextTick();

    if (totalsRef.value?.$el) {
        totalsRef.value.$el.scrollIntoView({
            behavior: 'smooth'
        });
    }
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
    errors.value.phone = [];
    if (typeof val === 'string') {
        order.value.phone = val.replaceAll(' ', '');
    } else if (val && val.number) {
        order.value.phone = val.number.replaceAll(' ', '');
    }
};

const onPhoneChangeTickets = (val, index, index_input) => {
    formTickets.value[index].inputs[index_input].error_phone = [];
    if (val) {
        if (typeof val === 'string') {
            formTickets.value[index].inputs[index_input].phone = val.replaceAll(' ', '');
        } else if (val && val.number) {
            formTickets.value[index].inputs[index_input].phone = val.number.replaceAll(' ', '');
        }
    }
};

const autoComplete = (checked, index) => {
    formTickets.value[index].inputs[0].name  = '';
    formTickets.value[index].inputs[0].email = '';
    formTickets.value[index].inputs[0].phone = '';
    if (checked) {
        formTickets.value[index].inputs[0].name  = order.value.name;
        formTickets.value[index].inputs[0].email = order.value.email;
        formTickets.value[index].inputs[0].phone = order.value.phone ? order.value.phone : '';
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
                        :class="{'is-error': errors.name.length}"
                        name="name"
                        id="name"
                        autocomplete="name"
                        v-model="order.name"
                        placeholder="Nombre completo"
                        @input="errors.name = []"
                        clearable
                    />
                    <span class="text-error" v-if="errors.name.length">{{ errors.name[0] }}</span>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mb-3">
                    <label class="bold has-text-dark" for="phone">Teléfono <span class="has-text-danger">*</span></label>
                    <VueTelInput
                        v-model="order.phone"
                        :value="order.phone"
                        mode="international"
                        class="mt-1"
                        :class="{'error-phone': errors.phone.length}"
                        style="color: #606266; height: 32px;"
                        :auto-format="true"
                        :input-options="{ placeholder: 'Ingresa tu número de teléfono', name: 'phone', id: 'phone', autocomplete: 'phone', maxlength: 15 }"
                        @input="(val) => onPhoneChange(val)"
                        defaultCountry="MX"
                    />
                    <span class="text-error" v-if="errors.phone.length">{{ errors.phone[0] }}</span>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mb-3">
                    <label class="bold has-text-dark" for="email">Correo <span class="has-text-danger">*</span></label>
                    <el-input
                        class="el-form-item mb-0 mt-1"
                        :class="{'is-error': errors.email.length}"
                        name="email"
                        id="email"
                        autocomplete="email"
                        v-model="order.email"
                        placeholder="Correo electrónico"
                        @input="errors.email = []"
                        clearable
                    />
                    <span class="text-error" v-if="errors.email.length">{{ errors.email[0] }}</span>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mb-3">
                    <label class="bold has-text-dark" for="confirm_email">Confirmar correo <span class="has-text-danger">*</span></label>
                    <el-input
                        class="el-form-item mb-0 mt-1"
                        :class="{'is-error': errors.confirm_email.length}"
                        name="email"
                        id="confirm_email"
                        autocomplete="email"
                        v-model="order.confirm_email"
                        placeholder="Confirmar correo electrónico"
                        @input="errors.confirm_email = []"
                        clearable
                    />
                    <span class="text-error" v-if="errors.confirm_email.length">{{ errors.confirm_email[0] }}</span>
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
                                                <span>Producto {{ (index + 1) }} - <b class="has-text-primary">{{ t.name }}</b></span>
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
                                        :class="{'is-error': input.error_name.length}"
                                        v-model="input.name"
                                        name="name"
                                        autocomplete="name"
                                        placeholder="Nombre completo"
                                        @input="input.error_name = []"
                                    />
                                    <span class="text-error" v-if="input.error_name.length">{{ input.error_name[0] }}</span>
                                </el-col>
                                <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8" class="mb-3">
                                    <label class="bold has-text-dark">Correo <span class="has-text-danger">*</span></label>
                                    <el-input
                                        class="el-form-item mb-0 mt-1"
                                        :class="{'is-error': input.error_email.length}"
                                        v-model="input.email"
                                        name="email"
                                        autocomplete="email"
                                        placeholder="Correo electrónico"
                                        @input="input.error_email = []"
                                    />
                                    <span class="text-error" v-if="input.error_email.length">{{ input.error_email[0] }}</span>
                                </el-col>
                                <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8" class="mb-3">
                                    <label class="bold has-text-dark">Teléfono <span class="has-text-danger">*</span></label>
                                    <VueTelInput
                                        v-model="input.phone"
                                        :value="input.phone"
                                        mode="international"
                                        class="mt-1"
                                        :class="{'error-phone': input.error_phone.length}"
                                        style="color: #606266; height: 32px;"
                                        :auto-format="true"
                                        :input-options="{ placeholder: 'Número de teléfono', name: 'phone', id: 'phone', autocomplete: 'phone' }"
                                        @input="(val) => onPhoneChangeTickets(val, index, key_index)"
                                        defaultCountry="MX"
                                    />
                                    <span class="text-error" v-if="input.error_phone.length">{{ input.error_phone[0] }}</span>
                                </el-col>
                                <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8" class="mb-3" v-for="(question, qt) in t.questions" :key="question.id">
                                    <label class="bold has-text-dark">{{ question.title }} <span class="has-text-danger" v-if="question.required">*</span></label>
                                    <el-input
                                        class="el-form-item mb-0 mt-1"
                                        :class="{'is-error': input.question[qt].error}"
                                        v-model="input.question[qt].response"
                                        v-if="question.type === 'text'"
                                        :placeholder="question.information"
                                        @input="input.question[qt].error = false"
                                    />
                                    <el-input
                                        class="el-form-item mb-0 mt-1"
                                        :class="{'is-error': input.question[qt].error}"
                                        v-model="input.question[qt].response"
                                        v-if="question.type === 'number'"
                                        :placeholder="question.information" @keypress="isNumber($event)"
                                        @input="input.question[qt].error = false"
                                    />
                                    <el-select
                                        class="el-form-item mb-0 mt-1"
                                        :class="{'is-error': input.question[qt].error}"
                                        v-model="input.question[qt].response" 
                                        v-if="question.type === 'select'" 
                                        :placeholder="question.information || 'Elige una opción'"
                                        :clearable="!question.required"
                                        @change="input.question[qt].error = false"
                                    >
                                        <el-option v-for="(o, i) in question.options" :key="i" :value="o" :label="o" />
                                    </el-select>
                                    <el-mention
                                        class="el-form-item mb-0 mt-1"
                                        :class="{'is-error': input.question[qt].error}"
                                        v-model="input.question[qt].response"
                                        v-if="question.type === 'textarea'"
                                        type="textarea"
                                        :rows="3"
                                        :placeholder="question.information"
                                        @input="input.question[qt].error = false"
                                    />
                                    <span class="text-error" v-if="input.question[qt].error">Campo requerido.</span>
                                </el-col>
                                <el-divider v-if="t.inputs.length > 1 && key_index !== (t.inputs.length - 1)" />
                            </el-row>
                        </el-card>
                    </el-row>
                </el-col>
            </el-row>
            <el-row :gutter="gutterValue" class="mb-6">
                <el-col :span="24" class="mb-3">
                    <el-row>
                        <el-col :xs="24" :sm="24" :md="12" :lg="18" :xl="18">
                            <h3 class="title is-3 has-text-dark mb-2">Resúmen de compra</h3>
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
                    <el-table class="w-100 mb-3" :data="tickets" stripe header-cell-class-name="has-text-dark" empty-text="Ningún dato disponible en esta tabla">
                        <el-table-column label="Producto" width="180">
                            <template #default="scope">
                                <span>{{ scope.row.name }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="Cant." align="center">
                            <template #default="scope">
                                {{ scope.row.quantity_to_purchase }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Precio unitario">
                            <template #default="{row}">
                                <span v-if="!row.promotion && !row.code" class="has-text-success">
                                    {{ formatCurrency(row.price) }} MXN
                                </span>
                                <del v-if="row.promotion || row.code" class="has-text-danger">{{ formatCurrency(row.price) }} MXN</del>
                            </template>
                        </el-table-column>
                        <el-table-column label="Dto." align="center">
                            <template #default="{row}">
                                <span v-if="!row.promotion && !row.code" class="has-text-danger">N/A</span>
                                <span v-if="row.promotion || row.code" class="has-text-success">{{ row.code ? row.code_discount : row.promotion }}%</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="Precio c/descuento">
                            <template #default="{row}">
                                <span v-if="!row.promotion && !row.code" class="has-text-danger">N/A</span>
                                <span v-if="row.promotion && !row.code" class="has-text-success">
                                    {{ formatCurrency(row.priceDiscount) }} MXN
                                </span>
                                <span v-if="!row.promotion && row.code" class="has-text-success">
                                    {{ formatCurrency(row.price - Math.round(row.price * (row.code_discount / 100))) }} MXN
                                </span>
                                <span v-if="row.promotion && row.code" class="has-text-success">
                                    {{ formatCurrency(row.price - Math.round(row.price * (row.code_discount / 100))) }} MXN
                                </span>
                            </template>
                        </el-table-column>
                        <el-table-column label="Subtotal">
                            <template #default="{row}">
                                <span class="!text-orange-500 !font-bold">{{ formatCurrency(row.subtotal) }} MXN</span>
                            </template>
                        </el-table-column>
                    </el-table>
                    <i class="has-text-link">
                        <font-awesome-icon :icon="['fas', 'circle-info']" /> 
                        Si utilizas un cupón de descuento no se tomará en cuenta el descuento del boleto.
                    </i>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mt-6" v-if="order.event_status === 1">
                    <label class="bold has-text-dark" for="payment_method">Método de pago <span class="has-text-danger">*</span></label>
                    <el-select
                        class="el-form-item mb-0"
                        v-model="order.payment_method"
                        placeholder="Selecciona una opción"
                        id="payment_method"
                        clearable
                        @change="(val) => verifyPaymentMethod(val)"
                        >
                            <el-option v-for="pm in payment_methods" :key="pm.id" :label="pm.name" :value="pm.sku" />
                    </el-select>
                </el-col>
                <el-col :span="24" class="has-text-left mt-6" ref="totalsRef">
                    <h6 class="subtitle is-5 has-text-black mb-2">
                        Subtotal: <b>{{ formatCurrency(order.subtotal) }} MXN</b>
                    </h6>
                    <h6 class="subtitle is-5 has-text-black mb-2" v-if="order.model_payment == 'separated'">
                        Cargo por servicio: <b>{{ formatCurrency(order.commission) }} MXN</b>
                    </h6>
                    <h6 class="subtitle is-5 has-text-black mb-2">
                        Total a pagar: <b class="has-text-success">{{ formatCurrency(order.subtotal + order.commission) }} MXN</b>
                    </h6>
                </el-col>
                <el-col :span="24" class="pt-5 pb-5">
                    <el-row :gutter="gutterValue">
                        <ConektaFrame
                            ref="conektaFrameRef"
                            v-if="viewConektaFrame"
                            :make-payment-parent="confirmMakePayment"
                        />
                        <PaypalFrame
                            ref="paypalFrameRef"
                            v-if="viewPaypalFrame"
                            :make-payment-parent="confirmMakePayment"
                            :amount="order.model_payment === 'separated' ? (order.subtotal + order.commission) : order.subtotal"
                        />
                    </el-row>
                </el-col>
                <el-col :span="24" class="has-text-centered mt-3">
                    <el-button type="primary" size="large" @click="confirmMakePayment" v-if="order.event_status == 1 && order.payment_method === 'oxxo'">
                        <font-awesome-icon :icon="['fas', 'check']" />
                        &nbsp;&nbsp;Realizar pedido
                    </el-button>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
</template>

<style scoped>
.error-phone {
    border: 1px solid red !important;
}
</style>