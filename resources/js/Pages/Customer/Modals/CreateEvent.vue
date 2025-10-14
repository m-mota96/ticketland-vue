<template>
    <el-dialog
        v-model="isActive"
        title="Crear evento"
        width="900"
        align-center
        style="margin-top: 2% !important;"
    >
        <el-row :gutter="20" class="mb-4">
            <el-col :span="12" class="mb-3">
                <label class="bold" for="name">Nombre del evento <span class="has-text-danger">*</span></label>
                <el-input
                    class="el-form-item mb-0 mt-1"
                    :class="{'is-error': errors.name}"
                    size="large"
                    id="name"
                    v-model="event.name"
                    @input="setWebsite"
                />
                <span class="text-error" v-if="errors.name">El nombre del evento es obligatorio.</span>
            </el-col>
            <el-col :span="12" class="mb-3">
                <label class="bold" for="website">Sitio web <span class="has-text-danger">*</span></label>
                <el-input
                    class="el-form-item mb-0 mt-1"
                    :class="{'is-error': errors.website}"
                    size="large"
                    id="website"
                    v-model="event.website"
                    @input="setWebsite"
                >
                <template #prepend>{{ appUrl }}/evento/</template>
                </el-input>
                <span class="text-error" v-if="errors.website">El sitio web es obligatorio.</span>
            </el-col>
            <el-col :span="12" class="mb-3">
                <label class="bold" for="capacity">Asistencia estimada <span class="has-text-danger">*</span></label>
                <el-input
                    class="el-form-item mb-0 mt-1"
                    :class="{'is-error': errors.capacity}"
                    size="large"
                    id="capacity"
                    v-model="event.capacity"
                    @keypress="isNumber($event)"
                />
                <span class="text-error" v-if="errors.capacity">La asistencia estimada es obligatoria.</span>
            </el-col>
            <el-col :span="12" class="mb-3">
                <label class="bold" for="category">Categoría <span class="has-text-danger">*</span></label>
                <el-select
                    class="el-form-item mb-0 mt-1"
                    :class="{'is-error': errors.category}"
                    size="large"
                    id="category"
                    v-model="event.category"
                    placeholder="Seleccione una categoría"
                >
                    <el-option
                    v-for="item in categories"
                    :key="item.id"
                    :label="item.name"
                    :value="item.id"
                    />
                </el-select>
                <span class="text-error" v-if="errors.category">La categoría es obligatoria.</span>
            </el-col>
            <el-col :span="12" class="mb-6">
                <p class="bold">Tipo de evento</p>
                <el-radio-group v-model="event.type">
                    <el-radio value="paid">De consumo</el-radio>
                    <el-radio value="free">De registro</el-radio>
                </el-radio-group>
            </el-col>
            <el-col :span="12" class="mb-6">
                <label class="bold">Fechas del evento <span class="has-text-danger">*</span></label>
                <el-date-picker
                    class="el-form-item mb-0 mt-1 w-100"
                    :class="{'is-error': errors.dates}"
                    size="large"
                    v-model="event.dates"
                    type="daterange"
                    range-separator="A"
                    start-placeholder="Fecha inicial"
                    end-placeholder="Fecha final"
                    @change="createHorary"
                />
                <span class="text-error" v-if="errors.dates">Las fechas son obligatorias.</span>
            </el-col>
            <el-col :span="8" class="mb-3">
                <div v-for="(item, index) in dates" :key="item.id">
                    <el-input
                    size="large"
                    class="mb-3"
                    v-model="item.date" readonly
                    >
                    <template #prepend>Día {{ index + 1 }}</template>
                </el-input>
                <span style="font-size: 13px; color: white;" v-if="errors.startHour || errors.endHour">0</span>
                </div>
            </el-col>
            <el-col :span="8">
                <el-row class="mb-3" :gutter="0" v-for="(item, index) in dates" :key="item.id">
                    <el-time-picker
                        class="el-form-item mb-0 w-100"
                        :class="{'is-error': errors.startHour}"
                        v-model="event.startHour[index]"
                        placeholder="Hora inicial"
                        size="large"
                        :format="'HH:mm'"
                        :value-format="'HH:mm'"
                    />
                    <span class="text-error" v-if="errors.startHour">Es necesario completar todos los horarios.</span>
                </el-row>
            </el-col>
            <el-col :span="8">
                <el-row class="mb-3" :gutter="0" v-for="(item, index) in dates" :key="item.id">
                    <el-time-picker
                        class="el-form-item mb-0 w-100"
                        :class="{'is-error': errors.endHour}"
                        v-model="event.endHour[index]"
                        placeholder="Hora final"
                        size="large"
                        :format="'HH:mm'"
                        :value-format="'HH:mm'"
                    />
                    <span class="text-error" v-if="errors.endHour">Es necesario completar todos los horarios.</span>
                </el-row>
            </el-col>
            <el-col :span="24">
                <label class="text-black" for="description">Describe tu evento</label>
                <el-input
                    id="description"
                    v-model="event.description"
                    :rows="5"
                    type="textarea"
                    placeholder=""
                />
            </el-col>
        </el-row>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="showHideModal">Cancelar</el-button>
                <el-button type="primary" @click="saveEvent" :loading="loading">Crear evento</el-button>
            </div>
        </template>
    </el-dialog>
</template>
  
<script>
import { showNotification } from '@/notification';
import apiClient from '@/apiClient';
  
export default {
    components: {
        //Aqui se agregan los componentes, en dado caso que quiereas usar, para separar código, yo separo los modales y aqui los agrego
        
    },
    data() {
        return {
            //Aquí se declaran las variables
            appUrl: window.location.origin,
            isActive: false,
            categories: this.$page.props.categories,
            event: {
                name: '',
                website: '',
                capacity: 0,
                category: '',
                type: 'paid',
                dates: [],
                allDates: [],
                days: 0,
                startHour: [],
                endHour: [],
                description: '',
            },
            errors: {
                name: false,
                website: false,
                capacity: false,
                category: false,
                dates: false,
                startHour: false,
                endHour: false
            },
            dates: [],
            minutes: ['00', '15', '30', '45'],
            loading: false
        }
    },
    beforeMount() {
        //Aqui se pueden mandar llamar metodos antes de que se monte el componente
    },
    mounted() {
        
    },
    created() {
        //Aqui se pueden mandar llamar métodos, cuando se crea el componente
    },
   
    methods: {
    // Aqui van los métodos
        async saveEvent() {
            if (this.validate()) {
                this.loading   = true;
                const response = await apiClient('customer/event', 'POST', this.event);
                if (response.error) {
                    this.showHideModal();
                    this.$emit('activate-animation');
                    this.loading = false;
                    showNotification('¡Error!', response.msj, 'error', 8000);
                    return false;
                }
                location.href = this.appUrl+'/cliente/evento/'+response.data.id;
                showNotification('¡Correcto!', response.msj, 'success');
            }
        },
        validate() {
            this.resetErrors();
            let valid = true;
            if (!this.event.name.trim()) {
                this.errors.name = true;
                valid            = false;
            }
            if (!this.event.website.trim()) {
                this.errors.website = true;
                valid               = false;
            }
            if (!this.event.capacity) {
                this.errors.capacity = true;
                valid                = false;
            }
            if (!this.event.category) {
                this.errors.category = true;
                valid                = false;
            }
            if (!this.event.dates.length) {
                this.errors.dates = true;
                valid             = false;
            }
            this.event.allDates.forEach((d, i) => {
                if (!this.event.startHour[i]) {
                    this.errors.startHour = true;
                    valid                 = false;
                }
                if (!this.event.endHour[i]) {
                    this.errors.endHour = true;
                    valid               = false;
                }
            });
            return valid;
        },
        setWebsite(value) {
            value              = value.toLowerCase();
            this.event.website = this.filterNonAphaNumeric(value);
        },
        showHideModal() {
            this.event.name        = '';
            this.event.website     = '';
            this.event.capacity    = '';
            this.event.category    = '';
            this.event.type        = 'paid';
            this.event.dates       = [];
            this.event.allDates    = [];
            this.dates             = [];
            this.event.startHour   = [];
            this.event.endHour     = [];
            this.event.description = '';
            this.resetErrors();
            this.isActive = !this.isActive;
        },
        createHorary() {
            // console.log(this.event.dates.length);
            if (!this.event.dates) {
                this.event.dates     = [];
                this.event.allDates  = [];
                this.dates           = [];
                this.event.startHour = [];
                this.event.endHour   = [];
                return false;
            }
            const timeStart = this.formatDate(this.event.dates[0], true);
            const timeEnd   = this.formatDate(this.event.dates[1], true);
            this.dates      = this.datesRange(timeStart, timeEnd);
        },
        formatDate(date) {
            let d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) 
                month = '0' + month;
            if (day.length < 2) 
                day = '0' + day;

            return [year, month, day].join('-');
        },
        datesRange(initialDate, finalDate) {
            this.event.days = 0;
            const dates = [];
            let currentDate = new Date(initialDate);
            const endDate = new Date(finalDate);

            // Bucle para recorrer las fechas
            while (currentDate <= endDate) {
                // Agregar la fecha actual al arreglo (en formato ISO)
                dates.push({date: this.dateEs(new Date(currentDate).toISOString().split('T')[0], '', 1)});
                this.event.allDates.push(new Date(currentDate).toISOString().split('T')[0]);
                // Incrementar un día
                currentDate.setDate(currentDate.getDate() + 1);
                this.event.days += 1;
            }

            return dates;
        },
        dateEs(date, separator = '', corto = 0) {
            separator = separator == '' ? '/' : ` ${separator} `;
            date = date.substring(0, 10);
            var meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            var mesesCortos = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            var nombreMes = corto = 0 ? meses[parseInt(date.substring(5, 7) - 1)] : mesesCortos[parseInt(date.substring(5, 7) - 1)]
            return `${date.substring(8, 10)}${separator}${nombreMes}${separator}${date.substring(0, 4)}`;
        },
        resetErrors() {
            this.errors.name      = false;
            this.errors.website   = false;
            this.errors.capacity  = false;
            this.errors.category  = false;
            this.errors.dates     = false;
            this.errors.startHour = false;
            this.errors.endHour   = false;
        },
        isNumber(evt) {
            const charCode = evt.which ? evt.which : evt.keyCode;
            if (charCode < 48 || charCode > 57) {
                evt.preventDefault();
            }
        },
        filterNonAphaNumeric(str) {
            let code, i, len, result='';
            for (i = 0, len = str.length; i < len; i++) {
                code = str.charCodeAt(i);
                if ((code > 47 && code < 58) || // numeric (0-9)
                    (code > 64 && code < 91) || // upper alpha (A-Z)
                    (code > 96 && code < 123)) { // lower alpha (a-z)
                        result += str.charAt(i);
                }
            }
            return result;
        }
    }
}
</script>
  
<style>
    
</style>