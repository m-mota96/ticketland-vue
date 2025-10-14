<template>
    <el-dialog
        v-model="activeEditLocation"
        title="Edita la dirección de tu evento"
        width="600"
        align-center
        style="margin-top: 2% !important;"
    >
        <el-row class="mb-4" :gutter="20">
            <el-col class="mb-3" :span="24">
                <label for="name">Lugar <span class="has-text-danger">*</span></label>
                <el-input class="el-form-item mb-0" :class="{'is-error': errors.name}" size="large" id="name" v-model="location.name"/>
                <span class="text-error" v-if="errors.name">El lugar es obligatorio.</span>
            </el-col>
            <el-col class="mb-3" :span="24">
                <label for="address">Dirección <span class="has-text-danger">*</span></label>
                <el-input class="el-form-item mb-0" :class="{'is-error': errors.address}" size="large" id="address" v-model="location.address"/>
                <span class="text-error" v-if="errors.address">La dirección es obligatoria.</span>
            </el-col>
            <el-col class="mb-3" :span="24">
                <label for="iframe">Iframe <a :href="appUrl+'/../../general/Llenar campo iframe.pdf'" target="_blank"><font-awesome-icon class="pointer bold has-text-black" title="¿Como lleno este campo?" :icon="['fas', 'question-circle']" /></a></label>
                <el-mention
                    class="w-100"
                    id="address"
                    v-model="location.iframe"
                    type="textarea"
                    :rows="10"
                />
            </el-col>
            <span class="text-error" v-if="errors.iframe">El iframe es obligatorio.</span>
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
import { showNotification } from '@/notification';
export default {
    props: {
        dadEvent: Object
    },
    data() {
        return {
            appUrl: window.location.origin,
            activeEditLocation: false,
            isDisabled: false,
            location: {
                event_id: this.dadEvent.id,
                name: this.dadEvent.location ? this.dadEvent.location.name : '',
                address: this.dadEvent.location ? this.dadEvent.location.address : '',
                iframe: this.dadEvent.location ? this.dadEvent.location.iframe : ''
            },
            errors: {
                name: false,
                address: false,
                iframe: false
            }
        }
    },
    methods: {
        showModal() {
            this.errors.name    = false;
            this.errors.address = false;
            this.errors.iframe  = false;
            this.activeEditLocation = true;
        },
        async saveInfo() {
            if (this.validateForm()) {
                this.isDisabled = true;
                const response = await apiClient('customer/editLocation', 'PUT', this.location);
                this.isDisabled = false;
                if (response.error) {
                    showNotification('¡Error!', response.msj, 'error');
                    return false;
                }
                this.dadEvent.location = response.data;
                showNotification('¡Correcto!', response.msj, 'success');
                this.activeEditLocation = false;
            }
        },
        validateForm() {
            let valid = true;
            if (!this.location.name) {
                this.errors.name = true;
                valid = false;
            }
            if (!this.location.address) {
                this.errors.address = true;
                valid = false;
            }
            // if (!this.location.iframe) {
            //     this.errors.iframe = true;
            //     valid = false;
            // }
            return valid;
        }
    }
}
</script>

<style scoped>

</style>