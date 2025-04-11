<template>
    <el-dialog
        v-model="activeEditEvent"
        title="Edita el nombre y sitio de ventas de tu evento"
        width="500"
        align-center
        style="margin-top: 5% !important;"
    >
        <el-row class="mb-4" :gutter="20">
            <el-col class="mb-3" :span="24">
                <label for="name">Nombre del evento</label>
                <el-input size="large" id="name" v-model="event.name"/>
            </el-col>
            <el-col class="mb-3" :span="24">
                <label for="url">Sitio de ventas</label>
                <div>
                    <el-input
                    size="large"
                    id="url"
                    v-model="event.url"
                    >
                    <template #prepend>{{appUrl}}/</template>
                    </el-input>
                </div>
            </el-col>
            <el-col class="mb-3" :span="24">
                <label for="quantity">Asistencia estimada</label>
                <el-input size="large" id="quantity" v-model="event.quantity"/>
            </el-col>
            <el-col class="mb-3" :span="24">
                <label for="category_id">Categoría</label>
                <el-select size="large" id="category_id" v-model="event.category_id" placeholder="Seleccione una categoría">
                    <el-option
                    v-for="item in categories"
                    :key="item.id"
                    :label="item.name"
                    :value="item.id"
                    />
                </el-select>
            </el-col>
        </el-row>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="activeEditEvent = false">Cancelar</el-button>
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
            categories: this.$page.props.categories,
            activeEditEvent: false,
            isDisabled: false,
            event: {
                id: this.dadEvent.id,
                name: this.dadEvent.name,
                url: this.dadEvent.url,
                quantity: this.dadEvent.quantity,
                category_id: this.dadEvent.category_id,
                category: {
                    name: this.dadEvent.category.name
                }
            }
        }
    },
    methods: {
        async saveInfo() {
            const response = await apiClient('customer/editEvent', 'PUT', this.event);
            this.isDisabled = false;
            if (response.error) {
                showNotification('¡Error!', response.msj, 'error');
                return false;
            }
            const option = this.categories.find(opt => opt.id === this.event.category_id);
            this.dadEvent.name = this.event.name;
            this.dadEvent.url = this.event.url;
            this.dadEvent.quantity = this.event.quantity;
            this.dadEvent.category_id = this.event.category_id;
            this.dadEvent.category.name = option.name;
            showNotification('¡Correcto!', response.msj, 'success');
            this.activeEditEvent = false;
        }
    }
}
</script>

<style scope>

</style>