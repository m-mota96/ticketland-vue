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
                    <el-col :span="24" class="mb-3">
                        <label for="name">Nombre <span class="has-text-danger">*</span></label>
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
                    <el-col :span="24" class="mb-3">
                        <label for="description">Descripción</label>
                        <el-mention
                            class="w-100 mt-1"
                            id="description"
                            v-model="ticket.description"
                            type="textarea"
                            :rows="5"
                        />
                    </el-col>
                    <el-col>
                        <label for="days">¿Cuántas veces se puede escanear este boleto? <span class="has-text-danger">*</span></label>
                        <el-select class="mt-1" size="large" id="days" v-model="ticket.days" placeholder="Seleccione una opción">
                            <el-option
                            v-for="n in daysEvent"
                            :key="n"
                            :label="n"
                            :value="n"
                            />
                        </el-select>
                        <span class="text-error" v-if="errors.quantity">Indique la cantidad de veces que pueden utilizar el boleto.</span>
                    </el-col>
                </el-row>
            </el-col>
            <el-col :span="12">
                <el-row :gutter="10">

                </el-row>
            </el-col>
        </el-row>
    </el-dialog>
</template>

<script>
export default {
    props: {
        // dadTicket: Object
        eventId: Number,
        daysEvent: Number
    },
    data() {
        return {
            activeEditTicket: false,
            ticket: {
                event_id: this.eventId,
                name: '',
                description: '',
                price: '',
                auqntity: 0
            },
            errors: {
                name: false,
                quantity: 0
            }
        }
    },
    methods: {
        showModal(_ticket = null) {
            this.activeEditTicket = true;
        }
    }
}
</script>

<style scoped>

</style>