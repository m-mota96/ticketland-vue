<template>
    <el-dialog
        v-model="activeTickets"
        title="Información de los boletos"
        width="95%"
        align-center
        style="margin-top: 3% !important;"
        :lock-scroll="false"
    >
        <el-table
            class="w-100 mb-6"
            :data="tickets"
            :span-method="objectSpanMethod"
            stripe
            :row-class-name="rowClassName"
            empty-text="Ningún dato disponible en esta tabla"
            header-cell-class-name="has-text-dark"
        >
            <el-table-column label="#" width="70" align="center">
                <template #default="scope">
                    {{ scope.$index + 1 }}
                </template>
            </el-table-column>
            <el-table-column prop="ticket.name" label="Tipo de boleto" />
            <el-table-column label="Precio" align="center">
                <template #default="scope">
                    {{ formatCurrency(scope.row.price) }}
                </template>
            </el-table-column>
            <el-table-column align="center">
                <template #header>
                    Descuento<br>del boleto
                </template>
                <template #default="scope">
                    {{ scope.row.promotion ? scope.row.promotion+'%' : 'N/A' }}
                </template>
            </el-table-column>
            <el-table-column align="center">
                <template #header>
                    Cupón de<br>descuento
                </template>
                <template #default="scope">
                    {{ scope.row.code_name ? scope.row.code_name : 'N/A' }}
                </template>
            </el-table-column>
            <el-table-column align="center">
                <template #header>
                    Descuento<br>del cupón
                </template>
                <template #default="scope">
                    {{ scope.row.code_discount ? scope.row.code_discount+'%' : '0%' }}
                </template>
            </el-table-column>
            <el-table-column label="Total pagado" align="center">
                <template #default="{row}">
                    <span v-if="row.promotion && !row.code_name">
                        {{ formatCurrency(row.price - Math.round(row.price * (row.promotion / 100))) }}
                    </span>
                    <span v-if="row.code_id">
                        {{ formatCurrency(row.price - Math.round(row.price * (row.code_discount / 100))) }}
                    </span>
                    <span v-if="!row.promotion && !row.code_id">
                        {{ formatCurrency(row.price) }}
                    </span>
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
                // console.log(this.tickets);
                this.tickets       = _tickets;
                this.activeTickets = true;
            },
            objectSpanMethod({ row, columnIndex, rowIndex }) {
                const columns = [0, 1, 2, 3, 4, 5, 6];

                if (!columns.includes(columnIndex)) {
                    return;
                }

                if (!row.ticket.package) {
                    return;
                }

                const previousRow = this.tickets[rowIndex - 1];

                // Si la fila anterior tiene el mismo ticket,
                // esta celda queda absorbida por el rowspan anterior.
                if (
                    previousRow &&
                    previousRow.ticket_id === row.ticket_id
                ) {
                    return {
                        rowspan: 0,
                        colspan: 0,
                    };
                }

                return {
                    rowspan: row.ticket.number_of_access,
                    colspan: 1,
                };
            },
            async disableAccess(event_id, access_id) {

            },
            formatCurrency(value) {
                return new Intl.NumberFormat('es-MX', {
                    style: 'currency',
                    currency: 'MXN'
                }).format(value);
            },
            rowClassName({ row, rowIndex }) {
                const previousRow = rowIndex > 0
                    ? this.tickets[rowIndex - 1]
                    : null;

                // Si pertenece al mismo grupo que la fila anterior
                if (
                    previousRow &&
                    previousRow.ticket_id === row.ticket_id
                ) {
                    return `group-${previousRow.ticket_id}`;
                }

                return `group-${row.ticket_id}`;
            }
        }
    }
</script>

<style scoped>

</style>