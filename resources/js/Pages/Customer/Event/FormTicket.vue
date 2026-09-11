<template>
    <MenuEvent></MenuEvent>
    <Submenu :dadEvent="event"></Submenu>
    <el-row class="wrapper">
        <el-col :span="14" :offset="5" class="pt-6">
            <el-row :gutter="50">
                <el-col :span="16">
                    <el-card v-if="questions.length || newFormTicket">
                        <el-row :gutter="20" class="pt-5">
                            <!-- <el-col :span="24" v-if="!questions.length && !newFormTicket">
                                <p class="text-xl text-center font-bold mt-5 mb-5">
                                    Crea 
                                    <el-button type="primary" link class="!text-xl !font-bold" @click="newFormTicket = true">aquí</el-button> 
                                    tu primer forma por boleto.
                                </p>
                            </el-col> -->
                            <el-col :span="12" class="mb-3" v-if="newFormTicket">
                                <label class="text-black" for="tickets">¿Para que boletos aplica? <span class="text-red-500">*</span></label>
                                <el-select-v2
                                    v-model="formTicket.tickets"
                                    class="el-form-item mb-0"
                                    :class="{'is-error': errors.tickets.length}"
                                    :options="tickets"
                                    multiple
                                    clearable
                                    collapse-tags
                                    placeholder="Elige el/los boletos"
                                    popper-class="custom-header"
                                    :max-collapse-tags="3"
                                    id="tickets"
                                >
                                    <template #header>
                                    <el-checkbox
                                        v-model="checkAll"
                                        :indeterminate="indeterminate"
                                        @change="handleCheckAll"
                                    >
                                        Seleccionar todo
                                    </el-checkbox>
                                    </template>
                                </el-select-v2>
                                <span class="text-red-500 text-sm">{{ errors.tickets[0] }}</span>
                            </el-col>
                            <el-col :span="12" class="mb-3" v-if="newFormTicket">
                                <label class="text-black" for="typeInput">Tipo de campo <span class="text-red-500">*</span></label>
                                <el-select
                                    class="el-form-item mb-0"
                                    :class="{'is-error': errors.typeInput.length}"
                                    v-model="formTicket.typeInput"
                                    placeholder="Elige una opción"
                                    @change="resetForm(false)"
                                    clearable
                                >
                                    <el-option
                                        value="text"
                                        label="Campo de texto"
                                    />
                                    <el-option
                                        value="number"
                                        label="Campo numérico"
                                    />
                                    <el-option
                                        value="select"
                                        label="Lista desplegable"
                                    />
                                    <el-option
                                        value="textarea"
                                        label="Área de texto"
                                    />
                                </el-select>
                                <span class="text-red-500 text-sm">{{ errors.typeInput[0] }}</span>
                            </el-col>
                            <el-col :span="12" class="mb-3" v-if="newFormTicket">
                                <label class="text-black" for="typeInput">Título del campo <span class="text-red-500">*</span></label>
                                <el-input
                                    class="el-form-item mb-0"
                                    :class="{'is-error': errors.title.length}"
                                    v-model="formTicket.title"
                                    placeholder="Escribe el título"
                                />
                                <span class="text-red-500 text-sm">{{ errors.title[0] }}</span>
                            </el-col>
                            <el-col :span="12" class="mb-3" v-if="newFormTicket">
                                <label class="text-black" for="typeInput">Ayuda del campo</label>
                                <el-input v-model="formTicket.placeholder" placeholder="Escribe la ayuda" />
                            </el-col>
                            <el-col :span="12" class="mb-3" v-if="newFormTicket">
                                <label class="text-black" for="typeInput">¿El campo será obligatorio?</label><br>
                                <el-switch
                                    v-model="formTicket.required"
                                    inline-prompt
                                    style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949"
                                    active-text="SI"
                                    inactive-text="NO"
                                />
                            </el-col>
                            <el-col :span="12" class="mb-3" v-if="newFormTicket && formTicket.typeInput === 'select'">
                                <label class="text-black" for="typeInput">Opciones de la lista <span class="text-red-500">*</span></label>
                                <el-select
                                    class="el-form-item mb-0"
                                    :class="{'is-error': errors.options.length}"
                                    placeholder="Agrega las opciones"
                                >
                                    <el-option
                                        v-for="item in formTicket.options"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value"
                                    >
                                        <span style="float: left">{{ item.label }}</span>
                                        <span
                                            style="
                                            float: right;
                                            font-size: 13px;
                                            "
                                        >
                                            <el-button type="danger" link @click.stop="deleteItem(item.value)">
                                                <font-awesome-icon :icon="['fas', 'trash-can']" />
                                            </el-button>
                                        </span>
                                    </el-option>
                                    <template #footer>
                                    <el-button v-if="!isAdding" text bg size="small" @click="onAddOption">
                                        Añadir opción
                                    </el-button>
                                    <template v-else>
                                        <el-input
                                            v-model="optionName"
                                            class="option-input mb-2"
                                            placeholder="Escribe la opción"
                                            size="small"
                                        />
                                        <el-button type="primary" size="small" @click="onConfirm">
                                            Confirmar
                                        </el-button>
                                        <el-button size="small" @click="clear">Cancelar</el-button>
                                    </template>
                                    </template>
                                </el-select>
                                <span class="text-red-500 text-sm">{{ errors.options[0] }}</span>
                            </el-col>
                            <el-col :span="24" v-if="newFormTicket" class="text-center mt-4">
                                <el-button type="warning" @click="resetForm" :disabled="disabled">Cancelar</el-button>
                                <el-button type="primary" @click="saveQuestion" :disabled="disabled">Guardar</el-button>
                            </el-col>
                            <el-divider v-if="newFormTicket" />
                            <el-col :span="18" :offset="3" v-if="newFormTicket">
                                <p class="text-black font-bold text-lg text-center">Vista previa</p>
                                <el-card class="p-3 mt-2">
                                    <label>{{ formTicket.title }} <span class="text-red-500" v-if="formTicket.required">*</span></label>
                                    <el-input v-model="value" v-if="formTicket.typeInput === 'text'" :placeholder="formTicket.placeholder" />
                                    <el-input v-model="value" v-if="formTicket.typeInput === 'number'" :placeholder="formTicket.placeholder" @keypress="isNumber($event)" />
                                    <el-mention
                                        v-model="value"
                                        v-if="formTicket.typeInput === 'textarea'"
                                        type="textarea"
                                        :rows="5"
                                        :placeholder="formTicket.placeholder"
                                    />
                                    <el-select v-model="value" v-if="formTicket.typeInput === 'select'" :placeholder="formTicket.placeholder || 'Elige una opción'" clearable>
                                        <el-option v-for="item in formTicket.options" :key="item.value" :value="item.value" :label="item.label" />
                                    </el-select>
                                </el-card>
                            </el-col>
                            <el-col :span="24" v-for="item in questions">
                                <el-row :gutter="20"
                                    v-if="!newFormTicket"
                                    class="border-2 border-dashed ps-1 pe-1 pt-4 pb-4 mb-5"
                                    :class="item.active ? 'border-green-300' : 'border-red-300'"
                                >
                                    <el-col :span="24">
                                        <el-tag type="primary" class="mb-2 mr-2" v-for="t in item.tickets" :key="t.id">{{ t.name }}</el-tag>
                                    </el-col>
                                    <el-col :span="10">
                                        <label class="text-black">{{ item.title }} <span class="text-red-500" v-if="item.required">*</span></label>
                                        <el-input v-if="item.type === 'text'" :placeholder="item.information" />
                                        <el-input v-if="item.type === 'number'" :placeholder="item.information" @keypress="isNumber($event)" />
                                        <el-select v-if="item.type === 'select'" :placeholder="item.information || 'Elige una opción'">
                                            <el-option v-for="(o, i) in item.options" :key="i" :value="o" :label="o" />
                                        </el-select>
                                        <el-mention
                                            v-if="item.type === 'textarea'"
                                            type="textarea"
                                            :rows="5"
                                            :placeholder="item.information"
                                        />
                                        <p class="mt-1 text-sm">Estatus: 
                                            <span class="font-bold" :class="item.active ? 'text-green-500' : 'text-red-500'">
                                                {{ item.active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </p>
                                    </el-col>
                                    <el-col :span="14">
                                        <br>
                                        <el-button type="" link class="!text-black !text-base" @click="editQuestion(item)" :disabled="disabled">
                                            <font-awesome-icon class="mr-2" :icon="['fas', 'pencil']" /> Editar
                                        </el-button>
                                        <el-button type="" link class="!text-black !text-base ml-4" @click="changeStatus(item.id)" :disabled="disabled">
                                            <font-awesome-icon class="mr-2" :icon="['far', item.active ? 'circle-xmark' : 'circle-check']" /> 
                                            {{ item.active ? 'Desactivar' : 'Activar' }}
                                        </el-button>
                                        <el-button type="" link class="!text-black !text-base ml-4" :disabled="disabled">
                                            <font-awesome-icon class="mr-2" :icon="['fas', 'trash-can']" /> Eliminar
                                        </el-button>
                                    </el-col>
                                </el-row>
                                <!-- <el-divider/> -->
                            </el-col>
                        </el-row>
                    </el-card>
                    <el-col :span="24" v-if="!questions.length && !newFormTicket" class="text-center pt-5">
                        <h3 class="subtitle is-3 has-text-grey w-100">Ninguna forma por boleto disponible.</h3>
                    </el-col>
                </el-col>
                <el-col :span="8">
                    <el-card class="text-center pb-4">
                        <h5 class="subtitle is-5 has-text-dark mb-5">OPCIONES</h5>
                        <el-button class="bold w-100" type="warning" size="large" @click="newFormTicket = true" :disabled="newFormTicket || questions.length === 5">
                            <font-awesome-icon class="mr-1 bold" :icon="['fas', 'plus']" /> Nueva forma por boleto
                        </el-button>
                        <p class="text-center text-blue-500 mt-3"><font-awesome-icon :icon="['fas', 'info-circle']" /> Máximo 5 registros.</p>
                    </el-card>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
    <Footer></Footer>
</template>

<script>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import MenuEvent from '../MenuEvent.vue';
import Submenu from '../Submenu.vue';
import Footer from '../Footer.vue';

export default {
    components: {
        MenuEvent,
        Submenu,
        Footer
    },
    data() {
        return {
            appUrl: window.location.origin,
            event: this.$page.props.event,
            tickets: [],
            questions: [],
            loading: false,
            newFormTicket: false,
            disabled: false,
            formTicket: {
                id: null,
                event_id: this.$page.props.event.id,
                tickets: [],
                typeInput: '',
                title: '',
                placeholder: '',
                options: [],
                required:  false
            },
            value: '',
            checkAll: false,
            indeterminate: false,
            optionName: '',
            isAdding: false,
            errors: {
                tickets: [],
                typeInput: [],
                title: [],
                options: []
            }
        }
    },
    beforeMount() {
        this.getQuestions();
        this.getTickets();
    },
    mounted() {
        
    },
    created() {
        
    },
    watch: {
        'formTicket.tickets': function (val) {
            if (val.length === 0) {
                this.checkAll = false;
                this.indeterminate = false;
            } else if (val.length === this.tickets.length) {
                this.checkAll = true;
                this.indeterminate = false;
            } else {
                this.indeterminate = true;
            }
        }
    },
    methods: {
        async getTickets() {
            this.loading   = true;
            const response = await apiClient('customer/allTickets', 'GET', {event_id: this.event.id});
            this.loading   = false;
            this.tickets   = response.data.map(item => ({
                value: item.id,
                label: item.name
            }));
        },
        async getQuestions() {
            const response = await apiClient('customer/questions', 'GET', {event_id: this.event.id});
            this.questions = response.data;
            this.questions.forEach(q => {
                q.options = q.options ? q.options.split(',') : '';
            });
        },
        async saveQuestion() {
            if (this.validate()) {
                this.disabled  = true;
                const method = !this.formTicket.id ? 'POST' : 'PUT';
                const response = await apiClient('customer/question', method, this.formTicket);
                this.disabled  = false;
                if (response.error) {
                    showNotification('¡Error!', response.msj, 'error', 7000);
                    return false;
                }
                this.getQuestions();
                this.resetForm();
                showNotification('¡Correcto!', response.msj, 'success');
            }
        },
        validate() {
            this.resetErrors();
            let valid = true;
            if (!this.formTicket.tickets.length) {
                this.errors.tickets.push('Elige al menos un boleto.');
                valid = false;
            }
            if (!this.formTicket.typeInput) {
                this.errors.typeInput.push('Elige una opción.');
                valid = false;
            }
            if (!this.formTicket.title) {
                this.errors.title.push('Elige una opción.');
                valid = false;
            }
            if (this.formTicket.typeInput === 'select' && this.formTicket.options.length < 2) {
                this.errors.options.push('Debes agregar al menos 2 opciones.');
                valid = false;
            }
            return valid;
        },
        resetErrors() {
            this.errors.tickets   = [];
            this.errors.typeInput = [];
            this.errors.title     = [];
            this.errors.options   = [];
        },
        editQuestion(question) {
            this.resetForm();
            this.formTicket.id          = question.id;
            this.formTicket.title       = question.title;
            this.formTicket.typeInput   = question.type;
            this.formTicket.placeholder = question.information;
            this.formTicket.required    = question.required === 1 ? true : false;
            if (question.type === 'select') {
                question.options.forEach(opt => {
                    this.formTicket.options.push({
                        label: opt,
                        value: opt
                    });
                });
            }
            question.tickets.forEach(t => {
                this.formTicket.tickets.push(t.id);
            });
            this.newFormTicket = true;
        },
        async changeStatus(id) {
            this.disabled  = true;
            const response = await apiClient(`customer/question/${id}`, 'PATCH', {event_id: this.event.id});
            this.disabled  = false;
            if (response.error) {
                showNotification('¡Error!', response.msj, 'error', 7000);
                return false;
            }
            this.getQuestions();
            showNotification('¡Correcto!', response.msj, 'success');
        },
        handleCheckAll(val) {
            this.indeterminate = false
            if (val) {
                this.formTicket.tickets = this.tickets.map((_) => _.value);
            } else {
                this.formTicket.tickets = [];
            }
        },
        onAddOption() {
            this.isAdding = true;
        },
        onConfirm() {
            if (this.optionName) {
                this.formTicket.options.push({
                    label: this.optionName,
                    value: this.optionName,
                });
                this.clear();
            }
        },
        clear() {
            this.optionName = '';
            this.isAdding = false;
        },
        resetForm(clearAll = true) {
            this.resetErrors();
            this.value                  = '';
            this.formTicket.title       = '';
            this.formTicket.placeholder = '';
            this.formTicket.required    = false;
            this.formTicket.options     = [];
            if (clearAll) {
                this.formTicket.id        = null;
                this.formTicket.typeInput = '';
                this.formTicket.tickets   = [];
                this.newFormTicket        = false;
            }
        },
        deleteItem(item) {
            this.formTicket.options = this.formTicket.options.filter(obj => obj.value !== item);
        },
        isNumber(evt) {
            const charCode = evt.which ? evt.which : evt.keyCode;
            if (charCode < 48 || charCode > 57) {
                evt.preventDefault();
            }
        }
    }
}
</script>

<style scoped>

</style>