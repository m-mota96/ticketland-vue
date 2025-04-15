<template>
    <el-dialog
        v-model="activeEditDescription"
        title="Edita la descripción de tu evento"
        width="800"
        align-center
        style="margin-top: 5% !important;"
    >
        <el-row class="mb-4" :gutter="20">
            <el-col class="mb-3" :span="24">
                <label for="description">Descripción</label>
                <el-mention
                    class="w-100"
                    id="description"
                    v-model="data.description"
                    type="textarea"
                    :rows="15"
                />
            </el-col>
        </el-row>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="activeEditDescription = false">Cancelar</el-button>
                <el-button type="primary" @click="saveInfo" :disabled="isDisabled">Guardar cambios</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
export default {
    props: {
        dadEvent: Object
    },
    data() {
        return {
            activeEditDescription: false,
            isDisabled: false,
            data: {
                event_id: this.dadEvent.id,
                description: this.dadEvent.description
            }
        }
    },
    methods: {
        async saveInfo() {
            this.isDisabled = true;
            const response = await apiClient('customer/editDescription', 'PUT', this.data);
            this.isDisabled = false;
            if (response.error) {
                showNotification('¡Error!', response.msj, 'error');
                return false;
            }
            this.dadEvent.description = this.data.description;
            showNotification('¡Correcto!', response.msj, 'success');
            this.activeEditDescription = false;
        }
    }
}
</script>

<style scoped>

</style>