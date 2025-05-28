<template>
    <Menu></Menu>
    <div class="container">
        <el-row>
            <el-col :span="20" :offset="2">
                <el-card class="p-5 pb-6">
                    <el-row :gutter="20">
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
                                id="legal_representative"
                                v-model="tax_nformation.legal_representative"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="company_name">Razón social <span class="has-text-danger">*</span></label>
                            <el-input 
                                id="company_name"
                                v-model="tax_nformation.company_name"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="rfc">RFC <span class="has-text-danger">*</span></label>
                            <el-input 
                                id="rfc"
                                v-model="tax_nformation.rfc"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="street">Calle <span class="has-text-danger">*</span></label>
                            <el-input 
                                id="street"
                                v-model="tax_nformation.street"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="outer_number">Número exterior <span class="has-text-danger">*</span></label>
                            <el-input 
                                id="outer_number"
                                v-model="tax_nformation.outer_number"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="inside_number">Número interior</label>
                            <el-input 
                                id="inside_number"
                                v-model="tax_nformation.inside_number"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="cologne">Colonia <span class="has-text-danger">*</span></label>
                            <el-input 
                                id="cologne"
                                v-model="tax_nformation.cologne"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="postal_code">Código postal <span class="has-text-danger">*</span></label>
                            <el-input 
                                id="postal_code"
                                v-model="tax_nformation.postal_code"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="state">Estado <span class="has-text-danger">*</span></label>
                            <el-input 
                                id="state"
                                v-model="tax_nformation.state"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="city">Ciudad <span class="has-text-danger">*</span></label>
                            <el-input 
                                id="city"
                                v-model="tax_nformation.city"
                            />
                        </el-col>
                        <el-col :span="24" class="text-center">
                            <p class="has-text-black mb-2"><b>Nota: </b>Verifique que la información sea correcta una vez guardada no podrá ser modificada.</p>
                            <el-button type="primary">Guardar</el-button>
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
                                id="bank"
                                v-model="bank_data.bank"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="bank_key">Clave <span class="has-text-danger">*</span></label>
                            <el-input 
                                id="bank_key"
                                v-model="bank_data.bank_key"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="acount">Número de cuenta <span class="has-text-danger">*</span></label>
                            <el-input 
                                id="acount"
                                v-model="bank_data.acount"
                            />
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="mb-4">
                            <label class="has-text-dark" for="name">Nombre del tarjetahabiente <span class="has-text-danger">*</span></label>
                            <el-input 
                            id="name"
                            v-model="bank_data.name"
                            />
                        </el-col>
                        <el-col :span="24" class="text-center">
                            <p class="has-text-black mb-2"><b>Nota: </b>Verifique que la información sea correcta una vez guardada no podrá ser modificada.</p>
                            <el-button type="primary">Guardar</el-button>
                        </el-col>
                        <el-col :span="24" class="mb-5">
                            <hr>
                        </el-col>
                        <el-col :span="24" class="mb-2">
                            <h5 class="title is-6 has-text-black">DOCUMENTACIÓN</h5>
                        </el-col>
                        <el-col :span="12">
                            <el-upload
                                class="upload-demo mb-5 mt-1"
                                ref="uploadRef"
                                drag
                                :data="{type: 'constitutive'}"
                                :action="appUrl+'/customer/uploadFiles'"
                                :headers="{'X-CSRF-TOKEN': token}"
                                accept=".jpg,.png,.pdf"
                                list-type="picture"
                                :limit="1"
                                :on-success="handleSuccess"
                                :on-error="handleError"
                            >
                                <el-icon class="el-icon--upload"><font-awesome-icon :icon="['fas', 'cloud-arrow-up']" /></el-icon>
                                <div class="el-upload__text">
                                    <h5 class="title is-5 has-text-dark mb-2">Acta constitutiva</h5>
                                    Arrastra tu archivo o <em>haz click en el recuadro</em>
                                </div>
                                <template #tip>
                                <div class="el-upload__tip">
                                    Máx 5 MB, .jpeg, .png, .pdf
                                    <a class="has-text-dark bold example">Ver ejemplo</a>
                                </div>
                                </template>
                            </el-upload>
                        </el-col>
                        <el-col :span="12">
                            <el-upload
                                class="upload-demo mb-5 mt-1"
                                ref="uploadRef"
                                drag
                                :data="{type: 'address'}"
                                :action="appUrl+'/customer/uploadFiles'"
                                :headers="{'X-CSRF-TOKEN': token}"
                                accept=".jpg,.png,.pdf"
                                list-type="picture"
                                :limit="1"
                                :on-success="handleSuccess"
                                :on-error="handleError"
                            >
                                <el-icon class="el-icon--upload"><font-awesome-icon :icon="['fas', 'cloud-arrow-up']" /></el-icon>
                                <div class="el-upload__text">
                                    <h5 class="title is-5 has-text-dark mb-2">Comprobante de domicilio</h5>
                                    Arrastra tu archivo o <em>haz click en el recuadro</em>
                                </div>
                                <template #tip>
                                <div class="el-upload__tip">
                                    Máx 5 MB, .jpeg, .png, .pdf
                                    <a class="has-text-dark bold example" :href="appUrl+'/general/cfe.jpg'" target="_blank">Ver ejemplo</a>
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
            tax_nformation: {
                id: null,
                legal_representative: '',
                company_name: '',
                rfc: '',
                street: '',
                outer_number: '',
                inside_number: '',
                cologne: '',
                postal_code: '',
                state: '',
                city: '',
            },
            bank_data: {
                id: null,
                bank: '',
                bank_key: '',
                acount: '',
                name: '',
            }
        }
    },
    methods: {
        handleSuccess(response, file, fileList) {
            // const refImage = this.typeUpload == 'profile' ? 'update-profile' : 'update-logo';
            // this.$emit(refImage, response.data);
            // this.activeUploadImages = false;
            // showNotification('¡Correcto!', response.msj, 'success');
            // this.clearFile();
        },
        handleError(response) {
            response = JSON.parse(response.message);
            showNotification('¡Error!', response.msj, 'error', 10000);
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