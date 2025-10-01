<template>
    <el-row class="container-fluid has-background-white-ter" ref="dataPrincipal">
        <el-col :xs="0" :sm="0" :md="0" :lg="24" :xl="24" class="row content-head p-r">
            <div class="p-a opacy w-100">
                <img v-if="event.profile" class="h-100 w-100 img-transparent" :src="appUrl+'/events/images/'+event.profile.name" :alt="event.name">
                <img v-if="!event.profile" class="h-100 w-100 img-transparent" :src="appUrl+'/general/slide_ticketland.png'" :alt="event.name">
            </div>
        </el-col>
        <el-col :xs="0" :sm="0" :md="0" :lg="{span: 12, offset: 6}" :xl="{span: 12, offset: 6}">
            <img  v-if="event.profile" class="p-a img-event p-0" :src="appUrl+'/events/images/'+event.profile.name" :alt="event.name">
            <img  v-if="!event.profile" class="p-a img-event p-0" :src="appUrl+'/general/slide_ticketland.png'" :alt="event.name">
        </el-col>
        <el-col :xs="24" :sm="24" :md="24" :lg="0" :xl="0">
            <img  v-if="event.profile" class="shadow" :src="appUrl+'/events/images/'+event.profile.name" :alt="event.name">
            <img  v-if="!event.profile" class="shadow" :src="appUrl+'/general/slide_ticketland.png'" :alt="event.name">
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
            <el-row :gutter="gutterValue">
                <el-col :span="24" class="mb-6">
                    <h2 class="title is-2 mb-0 bold has-text-black mb-2">Selecciona tus boletos</h2>
                    <h3 class="subtitle is-4 mb-0 has-text-grey">Máximo 10 boletos por orden</h3>
                </el-col>
                <el-col :span="24">
                    <el-row class="mb-6" :gutter="gutterValue2" v-for="(t, index) in data.tickets" :key="index">
                        <el-col class="mb-3" :sm="24" :md="16" :lg="18" :xl="18">
                                <h4 class="subtitle is-4 has-text-dark mb-0" v-if="!t.promotion">{{ t.name }}</h4>
                                <el-badge :value="`-${t.promotion}% Descuento`" class="item" :offset="[10, 5]" v-if="t.promotion">
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
                                v-model="t.quantity"
                                size="large"
                                :min="0"
                                :max="t.available"
                                @change="calculate"
                                :controls="true"
                            />
                        </el-col>
                    </el-row>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    <el-row class="has-background-white pb-5 b-t b-b pt-6 pb-5 padding" v-if="!viewInfoCustomer">
        <el-col :xs="24" :sm="24" :md="24" :lg="{span: 12, offset: 6}" :xl="{span: 12, offset: 6}">
            <el-row :gutter="gutterValue2">
                <el-col :xs="24" :sm="24" :md="16" :lg="18" :xl="18" class="mb-3">
                    <h4 class="subtitle is-4 has-text-dark mb-2" v-if="data.selected != 1">Tienes <b>{{ data.selected }}</b> boletos seleccionados</h4>
                    <h4 class="subtitle is-4 has-text-dark mb-2" v-if="data.selected == 1">Tienes <b>{{ data.selected }}</b> boleto seleccionado</h4>
                    <h4 class="title is-4 has-text-link">{{ formatCurrency(data.subtotal) }} MXN <span class="subtitle is-6 has-text-grey" v-if="event.model_payment == 'separated'"> + CARGOS</span></h4>
                </el-col>
                <el-col :xs="24" :sm="24" :md="8" :lg="6" :xl="6">
                    <el-button class="bold w-100" type="success" size="large" @click="loadInfo">
                        <font-awesome-icon :icon="['fas', 'money-check-dollar']" />&nbsp;&nbsp;Comprar boletos
                    </el-button>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    <el-row
        ref="dataOrder"
        class="custom-loading-svg container-fluid has-background-white pt-6 pb-6 padding b-b"
        v-if="viewInfoCustomer"
        :element-loading-text="`¡Procesando tu ${txtLoading}. No cierres ni actualices esta página, por favor espera!`"
        v-loading="loading"
        :element-loading-svg="svg"
        element-loading-svg-view-box="-10, -10, 50, 50"
        element-loading-background="rgba(0, 0, 0, 0.9)"
    >
        <el-col :xs="24" :sm="24" :md="24" :lg="{span: 12, offset: 6}" :xl="{span: 12, offset: 6}">
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
                        @keypress="isNumber($event)"
                        maxlength="10"
                    />
                    <span class="text-error" v-if="errors.phone">El teléfono es obligatorio.</span>
                    <span class="text-error" v-if="errors.phone_invalid">Teléfono inválido.</span>
                </el-col>
                <el-col :span="24">
                    <i class="has-text-dark"><font-awesome-icon :icon="['fas', 'circle-info']" /> Debes de tener acceso al correo ya que a esta dirección se enviarán los boletos.</i>
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
                <el-col :span="24">
                    <el-row>
                        <el-card class="w-100 mb-5" v-for="(t, index) in data.ticketsReserved" :key="index">
                            <template #header>
                                <div class="card-header">
                                    <el-col :span="24">
                                        <el-row :gutter="gutterValue">
                                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                                <span>Boleto {{ (index + 1) }} - <b class="has-text-primary">{{ t.name }}</b></span>
                                            </el-col>
                                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="has-text-right">
                                                <el-checkbox :class="{'w-100': gutterValue == 0}" v-model="t.checked" label="Autocompletar este boleto con datos de la orden." size="large" @change="(val) => autoComplete(val, index)" />
                                            </el-col>
                                        </el-row>
                                    </el-col>
                                </div>
                            </template>
                            <el-row :gutter="gutterValue">
                                <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8" class="mb-3">
                                    <label class="bold has-text-dark">Nombre completo <span class="has-text-danger">*</span></label>
                                    <el-input
                                        class="el-form-item mb-0 mt-1"
                                        :class="{'is-error': errors.names[index]}"
                                        v-model="t.customer_name"
                                        placeholder="Nombre completo"
                                    />
                                    <span class="text-error" v-if="errors.names[index]">El nombre es obligatorio.</span>
                                </el-col>
                                <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8" class="mb-3">
                                    <label class="bold has-text-dark">Correo</label>
                                    <el-input
                                        class="el-form-item mb-0 mt-1"
                                        :class="{'is-error': false}"
                                        v-model="t.email"
                                        placeholder="Correo"
                                    />
                                </el-col>
                                <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8" class="mb-3">
                                    <label class="bold has-text-dark">Teléfono</label>
                                    <el-input
                                        class="el-form-item mb-0 mt-1"
                                        :class="{'is-error': false}"
                                        v-model="t.phone"
                                        placeholder="Teléfono"
                                        @keypress="isNumber($event)"
                                        maxlength="10"
                                    />
                                </el-col>
                                <!-- <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mb-3">
                                    <label class="bold has-text-dark">¿Tienes un código de descuento?</label>
                                    <el-input
                                        class="el-form-item mb-0 mt-1"
                                        :class="{'is-error': false}"
                                        v-model="t.code"
                                        placeholder="Ingresa tu código"
                                        @input="val => formatInput(val, index)"
                                        @blur="val => verifyCodes(val, index)"
                                    />
                                </el-col> -->
                            </el-row>
                        </el-card>
                    </el-row>
                </el-col>
            </el-row>
            <el-row :gutter="gutterValue" class="mb-6">
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
                        <el-table-column prop="name" label="Producto" />
                        <el-table-column label="Cantidad" align="center">
                            <template #default="scope">
                                {{ scope.row.quantity }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Precio unitario">
                            <template #default="scope">
                                <span v-if="!scope.row.promotion" class="has-text-success">{{ scope.row.priceUnit }}</span>
                                <del v-if="scope.row.promotion && !data.discount" class="has-text-danger">{{ scope.row.priceUnit }}</del>
                                <span v-if="scope.row.promotion && data.discount" class="has-text-success">{{ scope.row.priceUnit }}</span>
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
                        <!-- <el-table-column label="Cupones">
                            <template #default="scope">
                                {{ formatCurrency(scope.row.discount)+' MXN' }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Total">
                            <template #default="scope">
                                {{ formatCurrency((scope.row.quantity * scope.row.price) - scope.row.discount)+' MXN' }}
                            </template>
                        </el-table-column> -->
                    </el-table>
                    <i class="has-text-danger"><font-awesome-icon :icon="['fas', 'circle-info']" /> Si aplicas un código de descuento no se tomará en cuenta el precio con descuento.</i>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="mt-6" :inline="true">
                    <el-row :gutter="5">
                        <el-col :xs="17" :sm="17" :md="18" :lg="18" :xl="18">
                            <label class="bold has-text-dark">¿Tienes un código de descuento?</label>
                            <el-input
                                class="el-form-item mb-0"
                                :class="{'is-error': false}"
                                v-model="data.order.code"
                                placeholder="Ingresa tu código"
                                @input="formatInput"
                                :disabled="disabledDiscount"
                            />
                        </el-col>
                        <el-col :xs="7" :sm="7" :md="6" :lg="6" :xl="6">
                            <br>
                            <el-button class="w-100" type="success" @click="verifyCodes" v-if="!data.discount">Validar cupón</el-button>
                            <el-button class="w-100" type="danger" @click="verifyCodes('delete')" v-if="data.discount">Borrar cupón</el-button>
                        </el-col>
                    </el-row>
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
                        @change="verifyPaymentMethod"
                        :disabled="disabledPaymentMethod"
                        >
                        <el-option label="Pago en Oxxo" value="oxxo" />
                        <el-option label="Tarjeta de Débito/Crédito" value="card" />
                    </el-select>
                    <span class="text-error" v-if="errors.payment_method">El método de pago es obligatorio.</span>
                </el-col>
                <el-col :span="24" class="has-text-left mt-6">
                    <h6 class="subtitle is-5 has-text-black mb-2">
                        Subtotal: <b>{{ formatCurrency(data.total) }} MXN</b>
                    </h6>
                    <h6 class="subtitle is-5 has-text-black mb-2">
                        Descuento: <b>{{ data.discount == 0 ? 'N/A' : formatCurrency(data.discount)+' MXN' }}</b>
                    </h6>
                    <h6 class="subtitle is-5 has-text-black mb-2">
                        Total: <b>{{ formatCurrency(data.subtotal) }} MXN</b>
                    </h6>
                    <h6 class="subtitle is-5 has-text-black mb-2" v-if="event.model_payment == 'separated'">
                        Cargo por servicio: <b>{{ formatCurrency(Math.round(data.subtotal * .12)) }} MXN</b>
                    </h6>
                    <h6 class="subtitle is-5 has-text-black mb-2" v-if="event.model_payment == 'separated'">
                        Total a pagar: <b class="has-text-success">{{ formatCurrency(data.subtotal + (Math.round(data.subtotal * .12))) }} MXN</b>
                    </h6>
                </el-col>
                <el-col :span="24" class="pt-5 pb-5">
                    <el-row :gutter="gutterValue">
                        <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" v-if="data.order.payment_method == 'card'" class="mb-3">
                            <label class="bold has-text-dark" for="cardName">Nombre en la tarjeta <span class="has-text-danger">*</span></label>
                            <el-input
                                :class="{'is-error': errors.cardName}"
                                class="el-form-item mb-0"
                                id="cardName"
                                v-model="data.paymentData.card.name"
                                placeholder="Nombre del propietario de la tarjeta"
                                type="text"
                            />
                            <span class="text-error" v-if="errors.cardName">El nombre del propietario es obligatorio.</span>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" v-if="data.order.payment_method == 'card'" class="mb-3">
                            <label class="bold has-text-dark" for="cardNumber">Número de tarjeta <span class="has-text-danger">*</span></label>
                            <el-input
                                :class="{'is-error': errors.cardNumber}"
                                class="el-form-item mb-0"
                                id="cardNumber"
                                v-mask="'#### #### #### ####'"
                                v-model="data.paymentData.card.number"
                                placeholder="1234 5678 9012 3456"
                                maxlength="19"
                                clearable
                            />
                            <span class="text-error" v-if="errors.cardNumber">El número de tarjeta es obligatorio.</span>
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
                    </el-row>
                </el-col>
                <el-col :span="24" class="has-text-centered mt-3">
                    <el-button type="primary" size="large" @click="payment" v-if="event.status == 1 && data.order.payment_method">
                        <font-awesome-icon :icon="['fas', 'dollar-sign']" v-if="data.order.payment_method == 'card'" />
                        <font-awesome-icon :icon="['fas', 'check']" v-if="data.order.payment_method == 'oxxo'" />
                        &nbsp;&nbsp;{{ data.order.payment_method == 'card' ? 'Realizar pago' : 'Realizar pedido' }}
                    </el-button>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    <el-row class="container-fluid has-background-white pb-6 pt-6 padding" ref="moreInfo">
        <el-col :xs="24" :sm="24" :md="24" :lg="{span: 12, offset: 6}" :xl="{span: 12, offset: 6}">
            <el-row :gutter="gutterValue2">
                <el-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14" class="mb-3">
                    <h3 class="subtitle is-3 has-text-grey mb-2">Sobre el evento</h3>
                    <p class="justify has-text-black multiline-text" v-if="event.description">{{ event.description }}</p>
                    <div v-if="event.location">
                        <h3 class="subtitle is-3 has-text-grey mb-2 mt-5">Lugar del evento</h3>
                        <h5 class="title is-5 has-text-dark mb-1">{{ event.location.name }}</h5>
                        <p class="has-text-dark mb-4">{{ event.location.address }}</p>
                        <div class="w-100" v-html="event.location.iframe">

                        </div>
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
    <Errors ref="Errors"></Errors>
    <div></div>
</template>

<script>
import apiClient from '@/apiClient';
import { dateEs, time } from '@/dateEs';
import { showNotification } from '@/notification';
import Errors from './Modals/Errors.vue';
import { ElMessageBox } from 'element-plus';

export default {
    components: {
        Errors
    },
    data() {
        return {
            appUrl: window.location.origin,
            event: this.$page.props.event,
            loading: false,
            gutterValue: window.innerWidth <= 768 ? 0 : 20,
            gutterValue2: window.innerWidth < 768 ? 0 : 80,
            txtLoading: 'compra',
            data: {
                tickets: [],
                ticketsReserved: [],
                selected: 0,
                subtotal: 0,
                discount: 0,
                total: 0,
                order: {
                    event_id: this.$page.props.event.id,
                    name: '',
                    email: '',
                    confirm_email: '',
                    phone: '',
                    payment_method: '',
                    token_id: '',
                    card: '',
                    code: '',
                },
                paymentData: {
                    card: {
                        name: '',
                        number: '4242424242424242',
                        exp_month: '06',
                        exp_year: '26',
                        cvc: '123'
                    }
                },
                cardExpiration: '06/26',
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
                names: [],
                payment_method: false,
                cardName: false,
                cardNumber: false,
                expiration: false,
                month_invalid: false,
                year_invalid: false,
                cvc: false
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
            currentYear: new Date().getFullYear(),
            disabledPaymentMethod: false,
            disabledDiscount: false,
        }
    },
    beforeMount() {
        // console.log(this.event.event_dates);
        this.loadPaymentMethod();
        this.setTickets();
    },
    mounted() {
        document.title = `${this.event.name}`;
        window.addEventListener('resize', this.handleResize);
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.handleResize);
    },
    methods: {
        payment() {
            if (this.validate()) {
                this.scrollCenterY();
                const txt    = this.data.order.payment_method == 'card' ? 
                `Tus boletos se enviarán al siguiente correo:<br><b>${this.data.order.email}</b><br>¿El correo esta correcto?<br>` : 
                `Tu ficha de pago se enviará al siguiente correo:<br><b>${this.data.order.email}</b><br>¿El correo esta correcto?<br>Tendrás 48 horas para realizar tu pago.<br>`;
                const txtBtn = this.data.order.payment_method == 'card' ? 
                'Si, proceder al pago' :
                'Si, realizar registro';
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
                    this.txtLoading = this.data.order.payment_method == 'card' ? 'compra' : 'registro';
                    this.scrollCenterY();
                    this.loading = true;
                    Conekta.setPublicKey("key_DV7ryzTwLNxT2Ye66xpm6uA");
                    Conekta.setLanguage('es');
                    Conekta.Token.create(this.data.paymentData,
                        (token) => this.conektaSuccessHandler(token),
                        (error) => {
                            this.loading = false;
                            console.log('Error al crear token:', error);
                        }
                    );
                });
            } else {
                this.$refs.dataOrder.$el.scrollIntoView({ behavior: 'smooth' });
            }
        },
        async conektaSuccessHandler(token) {
            this.data.order.token_id = token.id;
            this.data.order.card     = this.data.paymentData.card.number.slice(-4);
            const response = await apiClient('makePayment', 'POST', {
                selected: this.data.selected,
                order: this.data.order,
                tickets: this.filterTickets(),
                informationTickets: this.data.ticketsReserved,
            });
            this.loading = false;
            // console.log(response);
            if (response.error) {
                if (!response.data.type) {
                    showNotification('¡Error!', response.msj, 'error', 6000);
                    return false;
                }
                switch (response.data.type) {
                    case 'stock':
                        this.$refs.Errors.showErrors(response.data.error);
                        break;
                    case 'codes':
                        this.verifyCodes('delete');
                        this.totals();
                        showNotification('¡Error!', response.msj, 'error', 7000);
                        break;
                    case 'event':
                    case 'createCustomer':
                    case 'payment':
                        showNotification('¡Error!', response.msj, 'error', 7000);
                        break;
                }
                return false;
            }
            this.$nextTick(() => {
                // Espera a que el DOM se actualice
                if (this.$refs.dataPrincipal) {
                    this.$refs.dataPrincipal.$el.scrollIntoView({ behavior: 'smooth' });
                }
            });
            this.viewInfoCustomer = false;
            this.resetInfo();
            this.resetErrors();
            this.setTickets();
            showNotification('¡Correcto!', response.msj, 'success', 20000);
            return false;
        },
        async verifyCodes(action = null) {
            if (action == 'delete') {
                this.data.order.code  = '';
                this.disabledDiscount = false;
            }
            this.data.discount = 0;
            this.totals();
            this.data.subtotal = this.data.total;
            if (this.data.order.code) {
                const response = await apiClient('verifyCodes', 'POST', {event_id: this.event.id, code: this.data.order.code});
                if (response.error) {
                    showNotification('¡Error!', response.msj, 'error', 7000);
                    return false;
                }
                this.disabledDiscount = true;
                showNotification('¡Correcto!', 'Cupón aplicado.', 'success', 5000);
                this.totals(true);
                this.data.discount = this.data.total * (response.data.discount / 100);
                this.data.discount = Math.round(this.data.discount);
                this.data.subtotal = this.data.total;
                this.data.subtotal = this.data.total - this.data.discount;
            }
        },
        loadInfo() {
            if (!this.data.selected) {
                showNotification('¡Atención!', 'Debes tener seleccionado al menos 1 boleto', 'warning', 6500);
                return;
            }
            this.verifyCodes();
            this.viewInfoCustomer = true;
            this.$nextTick(() => {
                // Espera a que el DOM se actualice
                if (this.$refs.dataOrder) {
                    this.$refs.dataOrder.$el.scrollIntoView({ behavior: 'smooth' });
                }
            });
        },
        calculate(val, oldVal) {
            if ((this.data.selected + (val - oldVal)) > 10) {
                return false;
            }
            this.data.selected = this.data.selected + (val - oldVal);
            this.totals(false, true);
        },
        totals(code = false, save = false) {
            this.data.subtotal        = 0;
            this.data.total           = 0;
            if (save) {
                this.data.ticketsReserved = [];
                this.errors.names         = [];
            }
            this.data.tickets.forEach((t, i) => {
                let price          = t.promotion && !code ? t.priceDiscount : t.price;
                this.data.subtotal = this.data.subtotal + (t.quantity * price);
                this.data.total    = this.data.total + (t.quantity * price);
                t.subtotal         = this.formatCurrency(t.quantity * price) + ' MXN';
                if (t.quantity > 0 && save) {
                    for (let j = 0; j < t.quantity; j++) {
                        this.errors.names.push(false);
                        this.data.ticketsReserved.push({
                            id: t.id, name: t.name, customer_name: '', email: '', phone: '', code: '', checked: false
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
            this.totals();
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
        isNumber(evt) {
            const charCode = evt.which ? evt.which : evt.keyCode;
            if (charCode < 48 || charCode > 57) {
                evt.preventDefault();
            }
        },
        setExpiration() {
            this.data.paymentData.card.exp_month = '';
            this.data.paymentData.card.exp_year  = '';
            if (this.data.cardExpiration.length == 5) {
                let expiration = this.data.cardExpiration.split('/');
                this.data.paymentData.card.exp_month = expiration[0];
                this.data.paymentData.card.exp_year  = expiration[1];
            }
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

            if (!this.data.order.payment_method) {
                this.errors.payment_method = true;
                valid                      = false;
            }

            if (this.data.order.payment_method == 'card') {
                if (!this.data.paymentData.card.name) {
                    this.errors.cardName = true;
                    valid                = false;
                }
                if (!this.data.paymentData.card.number) {
                    this.errors.cardNumber = true;
                    valid                  = false;
                }
                if (!this.data.cardExpiration) {
                    this.errors.expiration = true;
                    valid                  = false;
                }
                if (this.data.cardExpiration) {
                    if (this.data.cardExpiration.length == 5) {
                        if (parseInt(this.data.paymentData.card.exp_month) < 1 || parseInt(this.data.paymentData.card.exp_month) > 12) {
                            this.errors.month_invalid = true;
                            valid                     = false;
                        }
                        let year = this.currentYear.toString();
                        if (parseInt(this.data.paymentData.card.exp_year) < year.slice(-2)) {
                            this.errors.year_invalid = true;
                            valid                    = false;
                        }
                    }
                }
                if (!this.data.paymentData.card.cvc) {
                    this.errors.cvc = true;
                    valid           = false;
                }
            }

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
            this.errors.payment_method         = false;
            this.errors.cardName               = false;
            this.errors.cardNumber             = false;
            this.errors.expiration             = false;
            this.errors.month_invalid          = false;
            this.errors.year_invalid           = false;
            this.errors.cvc                    = false;
        },
        verifyPaymentMethod(value) {
            if(value !== 'card') {
                this.errors.cardName               = false;
                this.errors.cardNumber             = false;
                this.errors.expiration             = false;
                this.errors.month_invalid          = false;
                this.errors.year_invalid           = false;
                this.errors.cvc                    = false;
            }
        },
        filterTickets() {
            return this.data.tickets.filter(t => t.quantity > 0);
        },
        setTickets() {
            this.event.tickets.forEach(t => {
                this.data.tickets.push({
                    id: t.id,
                    name: t.name,
                    description: t.description,
                    price: t.price,
                    priceDiscount: !t.promotion ? 0 : (t.price - Math.round(t.price * (t.promotion / 100))),
                    priceUnit: this.formatCurrency(t.price) + ' MXN',
                    subtotal: '',
                    quantity: 0,
                    discount: 0,
                    available: t.available,
                    promotion: t.promotion
                })
            });
        },
        resetInfo() {
            this.data.tickets                    = [];
            this.data.ticketsReserved            = [];
            this.data.selected                   = 0;
            this.data.subtotal                   = 0;
            this.data.discount                   = 0;
            this.data.total                      = 0;
            this.data.order.name                 = 'Miguel Angel Mota Murillo';
            this.data.order.email                = 'miguel.mota.murillo@gmail.com';
            this.data.order.confirm_email        = 'miguel.mota.murillo@gmail.com';
            this.data.order.phone                = '4371041976';
            this.data.order.token_id             = '';
            this.data.order.card                 = '';
            this.data.order.code                 = '';
            this.data.paymentData.card.name      = 'Miguel Angel Mota Murillo';
            this.data.paymentData.card.number    = '4242424242424242';
            this.data.paymentData.card.exp_month = '06';
            this.data.paymentData.card.exp_year  = '26';
            this.data.paymentData.card.cvc       = '123';
            this.data.cardExpiration             = '06/26';
            this.disabledDiscount                = false;
            this.loadPaymentMethod();
        },
        loadPaymentMethod() {
            this.data.order.payment_method = '';
            this.disabledPaymentMethod     = false;
            let currentDate = new Date();
            let date        = new Date(this.event.event_dates[0].date); // formato YYYY-MM-DD
            date.setDate(date.getDate() - 3);
            date        = date.toISOString().split('T')[0];
            currentDate = currentDate.toISOString().split('T')[0];
            if (currentDate >= date) {
                this.disabledPaymentMethod     = true;
                this.data.order.payment_method = 'card';
            }
        },
        scrollCenterY() {
            const elComponent = this.$refs.dataOrder;
            if (elComponent && elComponent.$el && typeof elComponent.$el.getBoundingClientRect === 'function') {
                const rect         = elComponent.$el.getBoundingClientRect();
                const scrollTop    = window.scrollY || window.pageYOffset;
                const windowHeight = window.innerHeight;

                const targetY = rect.top + scrollTop - (windowHeight / 2) + (rect.height / 2);
                window.scrollTo({
                    top: targetY,
                    behavior: 'smooth' // animado
                });
            }
        },
        formatInput(value) {
            // const formatted                       = value.toUpperCase().replace(/[^A-Z0-9]/gi, '').trim();
            // this.data.ticketsReserved[index].code = formatted;
            const formatted = value.toUpperCase().replace(/[^A-Z0-9]/gi, '').trim();
            this.data.order.code  = formatted;
        },
        handleResize() {
            this.gutterValue  = window.innerWidth <= 768 ? 0 : 20;
            this.gutterValue2 = window.innerWidth < 768 ? 0 : 80;
        },
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
.multiline-text {
    white-space: pre-line;
}
</style>