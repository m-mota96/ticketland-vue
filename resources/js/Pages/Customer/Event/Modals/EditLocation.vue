<template>
    <el-dialog
        v-model="activeEditLocation"
        title="Edita la descripción de tu evento"
        width="800"
        align-center
        style="margin-top: 5% !important;"
    >
        <el-row class="mb-4" :gutter="20">
            <el-col class="mb-3" :span="24">
                <label for="name">Lugar</label>
                <el-input size="large" id="name" v-model="location.name"/>
            </el-col>
            <el-col class="mb-3" :span="24">
                <label for="iframe">Iframe</label>
                <el-input size="large" id="iframe" v-model="location.iframe"/>
            </el-col>
        </el-row>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="activeEditLocation = false">Cancelar</el-button>
                <el-button type="primary" @click="saveInfo" :disabled="isDisabled">Guardar cambios</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script>
import apiClient from '@/apiClient';

export default {
    props: {
        dadEvent: Object
    },
    data() {
        return {
            activeEditLocation: false,
            isDisabled: false,
            location: {
                event_id: this.dadEvent.id,
                name: this.dadEvent.location.name,
                iframe: this.dadEvent.iframe
            }
        }
    },
    methods: {
        async saveInfo() {
            const response = await apiClient('customer/editLocation', 'PUT', this.location);
            
        }
    }
}
</script>

<style>

</style>