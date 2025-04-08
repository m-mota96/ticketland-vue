<template>
    <el-dialog
        v-model="isActive"
        title="Crear evento"
        width="900"
        align-center
        style="margin-top: 5% !important;"
    >
        <el-row :gutter="20" class="mb-4">
            <el-col :span="12" class="mb-3">
                <label class="text-black" for="name">Nombre del evento:</label>
                <el-input size="large" id="name" v-model="event.name"/>
            </el-col>
            <el-col :span="12" class="mb-3">
                <label class="text-black" for="website">Sitio web:</label>
                <el-input
                size="large"
                id="website"
                v-model="event.website"
                >
                <template #prepend>ticketland.mx/</template>
                </el-input>
            </el-col>
            <el-col :span="12" class="mb-3">
                <label class="text-black" for="capacity">Asistencia estimada:</label>
                <el-input size="large" id="capacity" v-model="event.capacity"/>
            </el-col>
            <el-col :span="12" class="mb-3">
                <label class="text-black" for="category">Categoría:</label>
                <el-select size="large" id="category" v-model="event.category" placeholder="Seleccione una categoría">
                    <el-option
                    v-for="item in categories"
                    :key="item.id"
                    :label="item.name"
                    :value="item.id"
                    />
                </el-select>
            </el-col>
            <el-col :span="12" class="mb-6">
                <p class="text-black">Tipo de evento:</p>
                <el-radio-group v-model="event.type">
                    <el-radio value="paid">De consumo</el-radio>
                    <el-radio value="free">De registro</el-radio>
                </el-radio-group>
            </el-col>
            <el-col :span="12" class="mb-6">
                <label>Fechas del evento:</label>
                    <el-date-picker
                        size="large"
                        class="w-100"
                        v-model="event.dates"
                        type="daterange"
                        range-separator="A"
                        start-placeholder="Fecha inicial"
                        end-placeholder="Fecha final"
                        @change="createHorary"
                    />
            </el-col>
            <el-col :span="8" class="mb-3">
                <el-input
                size="large"
                class="mb-3"
                v-for="(item, index) in dates" :key="item.id" v-model="item.date" readonly
                >
                <template #prepend>Día {{ index + 1 }}</template>
                </el-input>
            </el-col>
            <el-col :span="8">
                <el-row :gutter="0" v-for="(item, index) in dates" :key="item.id">
                    <el-col :span="3" class="text-left pt-2">
                        <b>De</b>
                    </el-col>
                    <el-col :span="10">
                        <el-select size="large" class="mb-3" v-model="event.startHour[index]" placeholder="Hora">
                            <el-option
                            v-for="index in 23"
                            :key="index"
                            :label="index"
                            :value="index"
                            />
                        </el-select>
                    </el-col>
                    <el-col :span="1" class="text-center pt-2">
                        <b>:</b>
                    </el-col>
                    <el-col :span="10">
                        <el-select size="large" class="mb-3" v-model="event.startMinute[index]" placeholder="Minuto">
                            <el-option
                            v-for="m in minutes"
                            :key="m.id"
                            :label="m"
                            :value="m"
                            />
                        </el-select>
                    </el-col>
                </el-row>
            </el-col>
            <el-col :span="8">
                <el-row :gutter="0" v-for="(item, index) in dates" :key="item.id">
                    <el-col :span="3" class="text-left pt-2">
                        <b>A</b>
                    </el-col>
                    <el-col :span="10">
                        <el-select size="large" class="mb-3" v-model="event.endHour[index]" placeholder="Hora">
                            <el-option
                            v-for="index in 23"
                            :key="index"
                            :label="index"
                            :value="index"
                            />
                        </el-select>
                    </el-col>
                    <el-col :span="1" class="text-center pt-2">
                        <b>:</b>
                    </el-col>
                    <el-col :span="10">
                        <el-select size="large" class="mb-3" v-model="event.endMinute[index]" placeholder="Minuto">
                            <el-option
                            v-for="m in minutes"
                            :key="m.id"
                            :label="m"
                            :value="m"
                            />
                        </el-select>
                    </el-col>
                </el-row>
            </el-col>
            <el-col :span="24">
                <label class="text-black" for="description">Describa su evento:</label>
                <el-input
                    id="description"
                    v-model="event.description"
                    :rows="5"
                    type="textarea"
                    placeholder=""
                />
            </el-col>
            <el-col :span="24" v-if="errors.length">
                <b>Por favor, corrija los siguientes errores:</b>
                <ul>
                    <li v-for="error in errors" :key="error.id">- {{ error }}</li>
                </ul>
            </el-col>
        </el-row>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="showHideModal">Cancelar</el-button>
                <el-button type="primary" @click="saveEvent" :disabled="isDisabled">Crear evento</el-button>
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
                startMinute: [],
                endHour: [],
                endMinute: [],
                description: '',
            },
            errors: [],
            dates: [],
            minutes: ['00', '15', '30', '45'],
            isDisabled: false
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
                this.isDisabled = true;
                const response = await apiClient('customer/event', 'POST', this.event);
                if (response.error) {
                    this.isDisabled = false;
                    showNotification('¡Error!', response.msj, 'error');
                    return false;
                }
                this.isDisabled = false;
                this.showHideModal();
                showNotification('¡Correcto!', response.msj, 'success');
            }
        },
        validate() {
            this.errors = [];
            if (!this.event.name.trim()) this.errors.push('Ingrese el nombre del evento');
            if (!this.event.website.trim()) this.errors.push('Ingrese el sitio web del evento');
            if (!this.event.capacity) this.errors.push('Ingrese la asistencia del evento');
            if (!this.event.category) this.errors.push('Elija la categoría del evento');
            if (!this.event.dates.length) this.errors.push('Ingrese las fechas del evento');
            if (!this.event.startHour.length || !this.event.startMinute.length || !this.event.endHour.length || !this.event.endMinute.length) this.errors.push('Ingrese los horarios del evento');
            if (this.event.startHour.length != this.event.days || this.event.startMinute.length != this.event.days || this.event.endHour.length != this.event.days || this.event.endMinute.length != this.event.days) this.errors.push('Complete todos los horarios del evento');
            return (!this.errors.length) ? true : false;
        },
        showHideModal() {
            this.event.name = 'Petweekend';
            this.event.website = 'petweekend';
            this.event.capacity = 3000;
            this.event.category = '';
            this.event.type = 'paid';
            this.event.dates = [];
            this.event.allDates = [];
            this.dates = [];
            this.event.startHour = [];
            this.event.startMinute = [];
            this.event.endHour = [];
            this.event.endMinute = [];
            this.event.description = '';
            this.errors = [];
            this.isActive = !this.isActive;
        },
        createHorary() {
            // console.log(this.event.dates.length);
            if (!this.event.dates) {
                this.event.dates = [];
                this.event.allDates = [];
                this.dates = [];
                this.event.startHour = [];
                this.event.startMinute = [];
                this.event.endHour = [];
                this.event.endMinute = [];
                return false;
            }
            const timeStart = this.formatDate(this.event.dates[0], true);
            const timeEnd = this.formatDate(this.event.dates[1], true);
            this.dates = this.datesRange(timeStart, timeEnd);
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
        }
    }
}
</script>
  
<style>
    
</style>