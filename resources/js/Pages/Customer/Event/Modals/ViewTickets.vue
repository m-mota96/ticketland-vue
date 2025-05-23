<template>
    <el-dialog
        v-model="activeTickets"
        title="Boletos"
        width="1500"
        align-center
        style="margin-top: 5% !important;"
    >
        <el-table class="w-100 mb-6" :data="tickets" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="has-text-dark">
            <el-table-column prop="ticket.name" label="Tipo de boleto" />
            <el-table-column label="Precio">
                <template #default="scope">
                    {{ formatCurrency(scope.row.ticket.price) }}
                </template>
            </el-table-column>
            <el-table-column prop="name" label="Nombre" />
            <el-table-column prop="email" label="Correo electrónico" />
            <el-table-column prop="phone" label="Teléfono" />
            <!-- <el-table-column label="Acciones" width="90" align="center">
                <template #default="scope">
                    <el-tooltip
                        class="box-item"
                        effect="dark"
                        content="Desactivar acceso"
                        placement="left"
                    >
                        <el-button class="pl-2 pr-2" type="danger" @click="disableAccess(scope.row.ticket.event_id, scope.row.id)">
                            <font-awesome-icon :icon="['far', 'circle-xmark']" />
                        </el-button>
                    </el-tooltip>
                </template>
            </el-table-column> -->
        </el-table>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="activeTickets = false">Cerrar</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script>
    export default {
        data() {
            return {
                activeTickets: false,
                tickets: []
            }
        },
        methods: {
            showTickets(_tickets) {
                this.tickets       = _tickets;
                this.activeTickets = true;
            },
            async disableAccess(event_id, access_id) {

            },
            formatCurrency(value) {
                return new Intl.NumberFormat('es-MX', {
                    style: 'currency',
                    currency: 'MXN'
                }).format(value);
            },
        }
    }
</script>

<style scoped>

</style>