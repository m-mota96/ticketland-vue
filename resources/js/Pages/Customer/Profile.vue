<template>
    <Menu></Menu>
    <div class="container">
        <el-row>
            <el-col :span="20" :offset="2">
                <el-card class="p-5 pb-6">
                    <el-row :gutter="30">
                        <el-col :span="24" class="mb-2">
                            <h5 class="title is-6 has-text-black">INFORMACIÓN DE LA CUENTA</h5>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="name">Nombre <span class="has-text-danger">*</span></label>
                            <el-input 
                                id="name"
                                v-model="user.name"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="email">Correo electrónico <span class="has-text-danger">*</span></label>
                            <el-input 
                                id="email"
                                v-model="user.email"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="phone">Teléfono <span class="has-text-danger">*</span></label>
                            <el-input 
                                id="phone"
                                v-model="user.phone"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="phone">Estatus de la cuenta</label>
                            <p class="has-text-success pt-2 bold" v-if="user.status"><font-awesome-icon :icon="['fas', 'file']" /> Con contrato</p>
                            <p class="has-text-danger pt-2 bold" v-if="!user.status"><font-awesome-icon :icon="['fas', 'file']" /> Sin contrato</p>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="mb-4">
                            <p class="text-justify">
                                <b>Nota: </b>Para que tu cuenta tenga contrato debes completar toda la información solicitada y subir tus documentos.<br>
                                Una ves hecho esto, se revisará todo por el equipo legal de Ticketland y si todo es correcto se activará tu cuenta.<br>
                                De lo contrario se te informará si hay algún problema con los datos o documentos proporcionados.
                            </p>
                        </el-col>
                        <el-col :span="24" class="text-center">
                            <el-button type="primary">Guardar</el-button>
                        </el-col>
                        <el-col :span="24" class="mb-5">
                            <hr>
                        </el-col>
                        <el-col :span="24" class="mb-2">
                            <h5 class="title is-6 has-text-black">INFORMACIÓN FISCAL</h5>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="legal_representative">Representante legal <span class="has-text-danger">*</span></label>
                            <el-input 
                                class="el-form-item mb-0"
                                :class="{'is-error': errors.legal_representative}"
                                id="legal_representative"
                                v-model="tax_information.legal_representative"
                                :disabled="disabledTaxInfo"
                            />
                            <span class="text-error" v-if="errors.legal_representative">El representante legal es obligatorio.</span>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="business_name">Razón social <span class="has-text-danger">*</span></label>
                            <el-input 
                                class="el-form-item mb-0"
                                :class="{'is-error': errors.business_name}"
                                id="business_name"
                                v-model="tax_information.business_name"
                                :disabled="disabledTaxInfo"
                            />
                            <span class="text-error" v-if="errors.business_name">La razón social es obligatoria.</span>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="rfc">RFC <span class="has-text-danger">*</span></label>
                            <el-input 
                                class="el-form-item mb-0"
                                :class="{'is-error': errors.rfc}"
                                id="rfc"
                                v-model="tax_information.rfc"
                                :disabled="disabledTaxInfo"
                            />
                            <span class="text-error" v-if="errors.rfc">El RFC es obligatorio.</span>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="address">Calle <span class="has-text-danger">*</span></label>
                            <el-input 
                                class="el-form-item mb-0"
                                :class="{'is-error': errors.address}"
                                id="address"
                                v-model="tax_information.address"
                                :disabled="disabledTaxInfo"
                            />
                            <span class="text-error" v-if="errors.address">La calle es obligatoria.</span>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="external_number">Número exterior <span class="has-text-danger">*</span></label>
                            <el-input 
                                class="el-form-item mb-0"
                                :class="{'is-error': errors.external_number}"
                                id="external_number"
                                v-model="tax_information.external_number"
                                :disabled="disabledTaxInfo"
                            />
                            <span class="text-error" v-if="errors.external_number">El número exterior es obligatorio.</span>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="internal_number">Número interior</label>
                            <el-input 
                                id="internal_number"
                                v-model="tax_information.internal_number"
                                :disabled="disabledTaxInfo"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="colony">Colonia <span class="has-text-danger">*</span></label>
                            <el-input 
                                class="el-form-item mb-0"
                                :class="{'is-error': errors.colony}"
                                id="colony"
                                v-model="tax_information.colony"
                                :disabled="disabledTaxInfo"
                            />
                            <span class="text-error" v-if="errors.colony">La colonia es obligatoria.</span>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="postal_code">Código postal <span class="has-text-danger">*</span></label>
                            <el-input 
                                class="el-form-item mb-0"
                                :class="{'is-error': errors.postal_code}"
                                id="postal_code"
                                v-model="tax_information.postal_code"
                                @keypress="isNumber($event)"
                                :disabled="disabledTaxInfo"
                            />
                            <span class="text-error" v-if="errors.postal_code">El código postal es obligatorio.</span>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="state">Estado <span class="has-text-danger">*</span></label>
                            <el-input 
                                class="el-form-item mb-0"
                                :class="{'is-error': errors.state}"
                                id="state"
                                v-model="tax_information.state"
                                :disabled="disabledTaxInfo"
                            />
                            <span class="text-error" v-if="errors.state">El estado es obligatorio.</span>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="city">Ciudad <span class="has-text-danger">*</span></label>
                            <el-input 
                                class="el-form-item mb-0"
                                :class="{'is-error': errors.city}"
                                id="city"
                                v-model="tax_information.city"
                                :disabled="disabledTaxInfo"
                            />
                            <span class="text-error" v-if="errors.city">La ciudad es obligatoria.</span>
                        </el-col>
                        <el-col :span="24" class="text-center" v-if="!tax_information.id">
                            <p class="has-text-black mb-2"><b>Nota: </b>Verifica que la información sea correcta una vez guardada no podrá ser modificada.</p>
                            <el-button type="primary" @click="saveTaxInformation">Guardar</el-button>
                        </el-col>
                        <el-col :span="24" class="mb-5">
                            <hr>
                        </el-col>
                        <el-col :span="24" class="mb-2">
                            <h5 class="title is-6 has-text-black">DATOS BANCARIOS</h5>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="bank">Banco <span class="has-text-danger">*</span></label>
                            <el-input 
                                class="el-form-item mb-0"
                                :class="{'is-error': errors.bank}"
                                id="bank"
                                v-model="bank_data.bank"
                                :disabled="disabledBankData"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="key">Clave <span class="has-text-danger">*</span></label>
                            <el-input 
                                class="el-form-item mb-0"
                                :class="{'is-error': errors.key}"
                                id="key"
                                v-model="bank_data.key"
                                :disabled="disabledBankData"
                                @keypress="isNumber($event)"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="number_account">Número de cuenta <span class="has-text-danger">*</span></label>
                            <el-input 
                                class="el-form-item mb-0"
                                :class="{'is-error': errors.number_account}"
                                id="number_account"
                                v-model="bank_data.number_account"
                                :disabled="disabledBankData"
                                @keypress="isNumber($event)"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="name_propietary">Nombre del tarjetahabiente <span class="has-text-danger">*</span></label>
                            <el-input 
                                class="el-form-item mb-0"
                                :class="{'is-error': errors.name_propietary}"
                                id="name_propietary"
                                v-model="bank_data.name_propietary"
                                :disabled="disabledBankData"
                            />
                        </el-col>
                        <el-col :span="24" class="text-center" v-if="!bank_data.id">
                            <p class="has-text-black mb-2"><b>Nota: </b>Verifica que la información sea correcta una vez guardada no podrá ser modificada.</p>
                            <el-button type="primary" @click="saveBankData">Guardar</el-button>
                        </el-col>
                        <el-col :span="24" class="mb-5">
                            <hr>
                        </el-col>
                        <el-col :span="24" class="mb-2">
                            <h5 class="title is-6 has-text-black">DOCUMENTACIÓN</h5>
                        </el-col>
                        <el-col :span="12">
                            <el-upload
                                class="upload-demo mb-6 mt-1"
                                ref="uploadConstitutive"
                                drag
                                :data="{type: 'constitutive'}"
                                :action="appUrl+'/customer/uploadFiles'"
                                :headers="{'X-CSRF-TOKEN': token}"
                                accept=".jpg,.png,.pdf"
                                list-type="picture"
                                :limit="1"
                                :on-success="handleSuccess"
                                :on-error="handleError"
                                :disabled="disabledConstitutive"
                            >
                                <el-icon class="el-icon--upload">
                                    <font-awesome-icon :icon="['fas', 'cloud-arrow-up']" v-if="!documents.constitutive.status" />
                                    <font-awesome-icon class="has-text-warning" :icon="['fas', 'circle-exclamation']" v-if="documents.constitutive.status == 'pending'" />
                                    <font-awesome-icon class="has-text-danger" :icon="['fas', 'circle-xmark']" v-if="documents.constitutive.status == 'rejected'" />
                                    <font-awesome-icon class="has-text-success" :icon="['fas', 'circle-check']" v-if="documents.constitutive.status == 'accepted'" />
                                </el-icon>
                                <div class="el-upload__text">
                                    <h5 class="title is-5 has-text-dark mb-2">Acta constitutiva</h5>
                                    <span v-if="!documents.constitutive.status">Arrastra tu archivo o <em>haz click en el recuadro</em></span>
                                    <span v-if="documents.constitutive.status == 'pending'" class="has-text-warning">Tu documento se encuentra en revisión.</span>
                                    <span v-if="documents.constitutive.status == 'rejected'">
                                        <span class="has-text-danger">Documento incorrecto </span>
                                        <em>haz click en el recuadro </em>
                                        para subir uno uevo
                                    </span>
                                    <span v-if="documents.constitutive.status == 'accepted'" class="has-text-success">Tu documento es correcto.</span>
                                </div>
                                <template #tip>
                                <div class="el-upload__tip">
                                    Máx 5 MB, .jpeg, .png, .pdf
                                    <a v-if="!documents.constitutive.status || documents.constitutive.status == 'rejected'" class="has-text-dark bold example" :href="appUrl+'/general/acta_constitutiva.jpg'" target="_blank">Ver ejemplo</a>
                                    <a v-if="documents.constitutive.status == 'pending' || documents.constitutive.status == 'accepted'" class="has-text-dark bold example" :href="appUrl+'/customers/'+documents.constitutive.document" target="_blank">Ver documento</a>
                                </div>
                                </template>
                            </el-upload>
                        </el-col>
                        <el-col :span="12">
                            <el-upload
                                class="upload-demo mb-6 mt-1"
                                ref="uploadAddress"
                                drag
                                :data="{type: 'address'}"
                                :action="appUrl+'/customer/uploadFiles'"
                                :headers="{'X-CSRF-TOKEN': token}"
                                accept=".jpg,.png,.pdf"
                                list-type="picture"
                                :limit="1"
                                :on-success="handleSuccess"
                                :on-error="handleError"
                                :disabled="disabledAddress"
                            >
                                <el-icon class="el-icon--upload">
                                    <font-awesome-icon :icon="['fas', 'cloud-arrow-up']" v-if="!documents.address.status" />
                                    <font-awesome-icon class="has-text-warning" :icon="['fas', 'circle-exclamation']" v-if="documents.address.status == 'pending'" />
                                    <font-awesome-icon class="has-text-danger" :icon="['fas', 'circle-xmark']" v-if="documents.address.status == 'rejected'" />
                                    <font-awesome-icon class="has-text-success" :icon="['fas', 'circle-check']" v-if="documents.address.status == 'accepted'" />
                                </el-icon>
                                <div class="el-upload__text">
                                    <h5 class="title is-5 has-text-dark mb-2">Comprobante de domicilio</h5>
                                    <span v-if="!documents.address.status">Arrastra tu archivo o <em>haz click en el recuadro</em></span>
                                    <span v-if="documents.address.status == 'pending'" class="has-text-warning">Tu documento se encuentra en revisión.</span>
                                    <span v-if="documents.address.status == 'rejected'">
                                        <span class="has-text-danger">Documento incorrecto </span>
                                        <em>haz click en el recuadro </em>
                                        para subir uno uevo
                                    </span>
                                    <span v-if="documents.address.status == 'accepted'" class="has-text-success">Tu documento es correcto.</span>
                                </div>
                                <template #tip>
                                <div class="el-upload__tip">
                                    Máx 5 MB, .jpeg, .png, .pdf
                                    <a v-if="!documents.address.status || documents.address.status == 'rejected'" class="has-text-dark bold example" :href="appUrl+'/general/acta_constitutiva.jpg'" target="_blank">Ver ejemplo</a>
                                    <a v-if="documents.address.status == 'pending' || documents.address.status == 'accepted'" class="has-text-dark bold example" :href="appUrl+'/customers/'+documents.address.document" target="_blank">Ver documento</a>
                                </div>
                                </template>
                            </el-upload>
                        </el-col>
                        <el-col :span="12">
                            <el-upload
                                class="upload-demo mb-5 mt-1"
                                ref="uploadBankReceipt"
                                drag
                                :data="{type: 'bank_receipt'}"
                                :action="appUrl+'/customer/uploadFiles'"
                                :headers="{'X-CSRF-TOKEN': token}"
                                accept=".jpg,.png,.pdf"
                                list-type="picture"
                                :limit="1"
                                :on-success="handleSuccess"
                                :on-error="handleError"
                                :disabled="disabledBankReceipt"
                            >
                                <el-icon class="el-icon--upload">
                                    <font-awesome-icon :icon="['fas', 'cloud-arrow-up']" v-if="!documents.bank_receipt.status" />
                                    <font-awesome-icon class="has-text-warning" :icon="['fas', 'circle-exclamation']" v-if="documents.bank_receipt.status == 'pending'" />
                                    <font-awesome-icon class="has-text-danger" :icon="['fas', 'circle-xmark']" v-if="documents.bank_receipt.status == 'rejected'" />
                                    <font-awesome-icon class="has-text-success" :icon="['fas', 'circle-check']" v-if="documents.bank_receipt.status == 'accepted'" />
                                </el-icon>
                                <div class="el-upload__text">
                                    <h5 class="title is-5 has-text-dark mb-2">Comprobante bancario</h5>
                                    <span v-if="!documents.bank_receipt.status">Arrastra tu archivo o <em>haz click en el recuadro</em></span>
                                    <span v-if="documents.bank_receipt.status == 'pending'" class="has-text-warning">Tu documento se encuentra en revisión.</span>
                                    <span v-if="documents.bank_receipt.status == 'rejected'">
                                        <span class="has-text-danger">Documento incorrecto </span>
                                        <em>haz click en el recuadro </em>
                                        para subir uno uevo
                                    </span>
                                    <span v-if="documents.bank_receipt.status == 'accepted'" class="has-text-success">Tu documento es correcto.</span>
                                </div>
                                <template #tip>
                                <div class="el-upload__tip">
                                    Máx 5 MB, .jpeg, .png, .pdf
                                    <a v-if="!documents.bank_receipt.status || documents.bank_receipt.status == 'rejected'" class="has-text-dark bold example" :href="appUrl+'/general/acta_constitutiva.jpg'" target="_blank">Ver ejemplo</a>
                                    <a v-if="documents.bank_receipt.status == 'pending' || documents.bank_receipt.status == 'accepted'" class="has-text-dark bold example" :href="appUrl+'/customers/'+documents.bank_receipt.document" target="_blank">Ver documento</a>
                                </div>
                                </template>
                            </el-upload>
                        </el-col>
                        <el-col :span="12">
                            <el-upload
                                class="upload-demo mb-5 mt-1"
                                ref="uploadIdentification"
                                drag
                                :data="{type: 'identification'}"
                                :action="appUrl+'/customer/uploadFiles'"
                                :headers="{'X-CSRF-TOKEN': token}"
                                accept=".jpg,.png,.pdf"
                                list-type="picture"
                                :limit="1"
                                :on-success="handleSuccess"
                                :on-error="handleError"
                                :disabled="disabledIdentification"
                            >
                                <el-icon class="el-icon--upload">
                                    <font-awesome-icon :icon="['fas', 'cloud-arrow-up']" v-if="!documents.identification.status" />
                                    <font-awesome-icon class="has-text-warning" :icon="['fas', 'circle-exclamation']" v-if="documents.identification.status == 'pending'" />
                                    <font-awesome-icon class="has-text-danger" :icon="['fas', 'circle-xmark']" v-if="documents.identification.status == 'rejected'" />
                                    <font-awesome-icon class="has-text-success" :icon="['fas', 'circle-check']" v-if="documents.identification.status == 'accepted'" />
                                </el-icon>
                                <div class="el-upload__text">
                                    <h5 class="title is-5 has-text-dark mb-2">Identificación del representante legal</h5>
                                    <span v-if="!documents.identification.status">Arrastra tu archivo o <em>haz click en el recuadro</em></span>
                                    <span v-if="documents.identification.status == 'pending'" class="has-text-warning">Tu documento se encuentra en revisión.</span>
                                    <span v-if="documents.identification.status == 'rejected'">
                                        <span class="has-text-danger">Documento incorrecto </span>
                                        <em>haz click en el recuadro </em>
                                        para subir uno uevo
                                    </span>
                                    <span v-if="documents.identification.status == 'accepted'" class="has-text-success">Tu documento es correcto.</span>
                                </div>
                                <template #tip>
                                <div class="el-upload__tip">
                                    Máx 5 MB, .jpeg, .png, .pdf
                                    <a v-if="!documents.identification.status || documents.identification.status == 'rejected'" class="has-text-dark bold example" :href="appUrl+'/general/acta_constitutiva.jpg'" target="_blank">Ver ejemplo</a>
                                    <a v-if="documents.identification.status == 'pending' || documents.identification.status == 'accepted'" class="has-text-dark bold example" :href="appUrl+'/customers/'+documents.identification.document" target="_blank">Ver documento</a>
                                </div>
                                </template>
                            </el-upload>
                        </el-col>
                    </el-row>
                </el-card>
            </el-col>
        </el-row>
    </div>
    <Footer></Footer>
</template>

<script>
import Menu from './Menu.vue';
import Footer from './Footer.vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';

export default {
    components: {
        Menu,
        Footer
    },
    data() {
        return {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            appUrl: window.location.origin,
            user: this.$page.props.user,
            documents: this.$page.props.documents,
            disabledTaxInfo: this.$page.props.tax_information ? true : false,
            tax_information: {
                id: null,
                legal_representative: '',
                business_name: '',
                rfc: '',
                address: '',
                external_number: '',
                internal_number: '',
                colony: '',
                postal_code: '',
                state: '',
                city: '',
            },
            disabledBankData: this.$page.props.bank_data ? true : false,
            bank_data: {
                id: null,
                name_propietary: '',
                bank: '',
                key: '',
                number_account: '',
            },
            disabledConstitutive: false,
            disabledAddress: false,
            disabledBankReceipt: false,
            disabledIdentification: false,
            errors: {
                legal_representative: false,
                business_name: false,
                rfc: false,
                address: false,
                external_number: false,
                colony: false,
                postal_code: false,
                state: false,
                city: false,
                name_propietary: false,
                bank: false,
                key: false,
                number_account: false
            }
        }
    },
    beforeMount() {
        this.verifyStatus();
        if (this.$page.props.tax_information) {
            this.tax_information.id                   = this.$page.props.tax_information.id;
            this.tax_information.legal_representative = this.$page.props.tax_information.legal_representative;
            this.tax_information.business_name        = this.$page.props.tax_information.business_name;
            this.tax_information.rfc                  = this.$page.props.tax_information.rfc;
            this.tax_information.address              = this.$page.props.tax_information.address;
            this.tax_information.external_number      = this.$page.props.tax_information.external_number;
            this.tax_information.internal_number      = this.$page.props.tax_information.internal_number;
            this.tax_information.colony               = this.$page.props.tax_information.colony;
            this.tax_information.postal_code          = this.$page.props.tax_information.postal_code;
            this.tax_information.state                = this.$page.props.tax_information.state;
            this.tax_information.city                 = this.$page.props.tax_information.city;
        }
        if (this.$page.props.bank_data) {
            this.bank_data.id              = this.$page.props.bank_data.id;
            this.bank_data.name_propietary = this.$page.props.bank_data.name_propietary;
            this.bank_data.bank            = this.$page.props.bank_data.bank;
            this.bank_data.key             = this.$page.props.bank_data.key;
            this.bank_data.number_account  = this.$page.props.bank_data.number_account;
        }
    },
    mounted() {
        
    },
    methods: {
        async saveTaxInformation() {
            if (this.validateTaxInformation()) {
                const response = await apiClient('customer/taxInformation', 'POST', this.tax_information);
                if (response.error) {
                    showNotification('¡Error!', response.msj, 'error');
                    return false;
                }
                this.disabledTaxInfo    = true;
                this.tax_information.id = response.data.id;
                showNotification('¡Correcto!', response.msj, 'success');
            }
        },
        async saveBankData() {
            if (this.validateBankData()) {
                const response = await apiClient('customer/bankData', 'POST', this.bank_data);
                if (response.error) {
                    showNotification('¡Error!', response.msj, 'error');
                    return false;
                }
                this.disabledBankData = true;
                this.bank_data.id     = response.data.id;
                showNotification('¡Correcto!', response.msj, 'success');
            }
        },
        validateTaxInformation() {
            let valid = true;
            if (!this.tax_information.legal_representative) {
                this.errors.legal_representative = true;
                valid                            = false;
            }
            if (!this.tax_information.business_name) {
                this.errors.business_name = true;
                valid                     = false;
            }
            if (!this.tax_information.rfc) {
                this.errors.rfc = true;
                valid           = false;
            }
            if (!this.tax_information.address) {
                this.errors.address = true;
                valid               = false;
            }
            if (!this.tax_information.external_number) {
                this.errors.external_number = true;
                valid                       = false;
            }
            if (!this.tax_information.colony) {
                this.errors.colony = true;
                valid              = false;
            }
            if (!this.tax_information.postal_code) {
                this.errors.postal_code = true;
                valid                   = false;
            }
            if (!this.tax_information.state) {
                this.errors.state = true;
                valid             = false;
            }
            if (!this.tax_information.city) {
                this.errors.city = true;
                valid            = false;
            }
            return valid;
        },
        validateBankData() {
            let valid = true;
            if (this.bank_data.name_propietary) {
                this.errors.name_propietary = true;
                valid                       = false;
            }
            if (this.bank_data.bank) {
                this.errors.bank = true;
                valid            = false;
            }
            if (this.bank_data.key) {
                this.errors.key = true;
                valid           = false;
            }
            if (this.bank_data.number_account) {
                this.errors.number_account = true;
                valid                      = false;
            }
            return valid;
        },
        handleSuccess(response, file, fileList) {
            // const refImage = this.typeUpload == 'profile' ? 'update-profile' : 'update-logo';
            // this.$emit(refImage, response.data);
            // this.activeUploadImages = false;
            this.documents = response.data.documents;
            this.verifyStatus();
            showNotification('¡Correcto!', response.msj, 'success');
            this.clearFile(response.data.type);
        },
        handleError(response) {
            response = JSON.parse(response.message);
            showNotification('¡Error!', response.msj, 'error', 10000);
        },
        clearFile(type) {
            switch (type) {
                case 'constitutive':
                    if (this.$refs.uploadConstitutive) {
                        this.$refs.uploadConstitutive.clearFiles();
                    }
                    break;
                case 'address':
                    if (this.$refs.uploadAddress) {
                        this.$refs.uploadAddress.clearFiles();
                    }
                    break;
                case 'bank_receipt':
                    if (this.$refs.uploadBankReceipt) {
                        this.$refs.uploadBankReceipt.clearFiles();
                    }
                    break;
                case 'identification':
                    if (this.$refs.uploadIdentification) {
                        this.$refs.uploadIdentification.clearFiles();
                    }
                    break;
            }
        },
        verifyStatus() {
            if (['pending', 'accepted'].includes(this.documents.constitutive.status)) {
                this.disabledConstitutive = true;
            }
            if (['pending', 'accepted'].includes(this.documents.address.status)) {
                this.disabledAddress = true;
            }
            if (['pending', 'accepted'].includes(this.documents.bank_receipt.status)) {
                this.disabledBankReceipt = true;
            }
            if (['pending', 'accepted'].includes(this.documents.identification.status)) {
                this.disabledIdentification = true;
            }
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
    hr {
        border: 0.01rem solid #eeeceb;
    }
    .text-justify {
        text-align: justify !important;
    }
    .example {
        position: absolute;
        right: 0;
    }
    .el-upload__tip {
        position: relative;
    }
</style>