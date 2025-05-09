<template>
    <el-dialog
        v-model="activeEditContact"
        title="Editar información de contacto"
        width="500"
        align-center
        style="margin-top: 2% !important;"
    >
        <el-row class="mb-4" :gutter="20">
            <el-col class="mb-3" :span="24">
                <label for="email">Correo electrónico</label>
                <el-input class="el-form-item mb-0" size="large" id="email" v-model="data.email"/>
            </el-col>
            <el-col class="mb-3" :span="24">
                <label for="phone">Teléfono</label>
                <el-input class="el-form-item mb-0" size="large" id="phone" v-model="data.phone"/>
            </el-col>
            <el-col class="mb-3" :span="24">
                <label for="twitter">Twitter</label>
                <el-input class="el-form-item mb-0" size="large" id="twitter" v-model="data.twitter" placeholder="p.ej: ticketland sin el @"/>
            </el-col>
            <el-col class="mb-3" :span="24">
                <label for="facebook">Facebook</label>
                <el-input class="el-form-item mb-0" size="large" id="facebook" v-model="data.facebook" placeholder="p.ej: https://www.facebook.com/ticketland"/>
            </el-col>
            <el-col class="mb-3" :span="24">
                <label for="instagram">Instagram</label>
                <el-input class="el-form-item mb-0" size="large" id="instagram" v-model="data.instagram" placeholder="p.ej: https://www.instagram.com/ticketland"/>
            </el-col>
            <el-col class="mb-3" :span="24">
                <label for="website">Página web</label>
                <el-input class="el-form-item mb-0" size="large" id="website" v-model="data.website" placeholder="p.ej: https://ticketland.mx"/>
            </el-col>
        </el-row>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="activeEditContact = false">Cancelar</el-button>
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
            activeEditContact: false,
            isDisabled: false,
            data: {
                event_id: this.dadEvent.id,
                email: this.dadEvent.email,
                phone: this.dadEvent.phone,
                twitter: this.dadEvent.twitter,
                facebook: this.dadEvent.facebook,
                instagram: this.dadEvent.instagram,
                website: this.dadEvent.website
            }
        }
    },
    methods: {
        async saveInfo() {
            if (!this.validateForm()) {
                showNotification('¡Atención!', 'No hay información que guardar', 'warning');
                return false;
            }
            this.isDisabled = true;
            const response = await apiClient('customer/editContact', 'PUT', this.data);
            this.isDisabled = false;
            if (response.error) {
                showNotification('¡Error!', response.msj, 'error');
                return false;
            }
            this.dadEvent.email     = this.data.email;
            this.dadEvent.phone     = this.data.phone;
            this.dadEvent.twitter   = this.data.twitter;
            this.dadEvent.facebook  = this.data.facebook;
            this.dadEvent.instagram = this.data.instagram;
            this.dadEvent.website   = this.data.website;
            showNotification('¡Correcto!', response.msj, 'success');
            this.activeEditContact = false;
        },
        validateForm() {
            let valid = false;
            if (this.data.email) {
                valid = true;
            }
            if (this.data.phone) {
                valid = true;
            }
            if (this.data.twitter) {
                valid = true;
            }
            if (this.data.facebook) {
                valid = true;
            }
            if (this.data.instagram) {
                valid = true;
            }
            if (this.data.website) {
                valid = true;
            }
            return valid;
        }
    }
}
</script>

<style scoped>

</style>