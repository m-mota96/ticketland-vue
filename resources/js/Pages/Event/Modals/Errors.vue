<template>
    <el-dialog
        v-model="activeErrors"
        width="600"
        align-center
        style="margin-top: 2% !important;"
    >
        <el-row class="pb-3 pt-5 pl-5 pr-5" :gutter="20">
            <el-col class="mb-5" :span="24">
                <h5 class="title is-5 has-text-dark">Lo sentimos, hubo problemas al realizar tu compra.</h5>
            </el-col>
            <el-col class="mb-3 card-error p-3" :span="24" v-for="(e, index) in errors" :key="index">
                <h6 class="subtitle is-6">
                    <div v-html="e"></div>
                </h6>
            </el-col>
            <el-col :span="24">
                <p class="has-text-black mb-0 mt-5 bold">No se realizaron cargos a tu tarjeta</p>
            </el-col>
        </el-row>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="activeErrors = false">Cerrar</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
export default {
    props: {
        
    },
    data() {
        return {
            activeErrors: false,
            errors: []
        }
    },
    methods: {
        showErrors(_errors) {
            this.errors       = _errors;
            this.activeErrors = true;
        }
    }
}
</script>

<style scoped>
    .card-error {
        color: #721c24 !important;
        background-color: #f8d7da;
        border-color: #f5c6cb;
        border-radius: 5px;
    }
    .subtitle {
        color: #721c24 !important;
    }
</style>