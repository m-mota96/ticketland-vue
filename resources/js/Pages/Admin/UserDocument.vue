<script setup>
import Menu from './Menu.vue';
import Navbar from './Navbar.vue';
import { onMounted, ref } from 'vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';

const appUrl = ref(window.location.origin);
const documents = ref([]);
const search    = ref({
    customerName: '',
    customerPhone: '',
    customerEmail: '',
    type: '',
    status: 'pending',
});
const pagination = ref({
    totalRows: 0,
    currentPage: 1,
    pageSize: 25,
});

onMounted(() => {
    getDocuments();
});

const getDocuments = async () => {
    const response = await apiClient('admin/getDocuments', 'GET', {search: search.value, pagination: pagination.value});
    if (response.error) {
        showNotification('¡Error!', response.msj, 'error');
        return false;
    }
    documents.value            = response.data.documents;
    pagination.value.totalRows = response.data.count;
};
const typeDocument = (type) => {
    switch (type) {
        case 'constitutive':
            return 'Acta constitutiva';
        case 'address':
            return 'Comprobante de domicilio';
        case 'bank_receipt':
            return 'Comprobante bancario';
        case 'identification':
            return 'Identificación';
    }
};
const statusTxt = (status) => {
    switch (status) {
        case 'pending':
            return {txt: 'Pendiente', class: 'has-text-warning'};
        case 'accepted':
            return {txt: 'Aceptado', class: 'has-text-success'};
        case 'rejected':
            return {txt: 'Rechazado', class: 'has-text-danger'};
    }
}
const viewDocument = (_doc) => {
    window.open(appUrl.value + '/customers/' + _doc, '_blank')

}
const changeStatus = async(id, status) => {
    const response = await apiClient('admin/statusDocument', 'PUT', {id, status});
    if (response.error) {
        showNotification('¡Error!', response.msj, 'error');
        return false;
    }
    getDocuments();
    showNotification('¡Correcto!', response.msj, 'success');
};
const handleSizeChange = (val) => {
    // console.log(`${val} items per page`)
    getDocuments();
};
const handleCurrentChange = (val) => {
    // console.log(`current page: ${val}`)
    getDocuments();
};
</script>

<template>
    <div class="container-fluid">
        <Menu :index="'3'" :countDocs="$page.props.countDocs"></Menu>
        <div style="width: 85%;">
            <Navbar></Navbar>
            <el-row :gutter="20" class="p-6">
                <el-col :span="24">
                    <h4 class="title is-4 has-text-dark mb-2">Usuarios/<span class="has-text-link">Documentos</span></h4>
                    <el-card>
                        <el-table class="w-100" :data="documents" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="has-text-dark">
                            <el-table-column prop="id" label="#" width="70" align="center" />
                            <el-table-column prop="user.name">
                                <template #header>
                                    <el-input v-model="search.customerName" placeholder="Buscar Cliente" @input="getDocuments" clearable />
                                </template>
                            </el-table-column>
                            <el-table-column prop="user.email">
                                <template #header>
                                    <el-input v-model="search.customerEmail" placeholder="Buscar Correo electrónico" @input="getDocuments" clearable />
                                </template>
                            </el-table-column>
                            <el-table-column prop="user.phone">
                                <template #header>
                                    <el-input v-model="search.customerPhone" placeholder="Buscar Teléfono" @input="getDocuments" clearable />
                                </template>
                            </el-table-column>
                            <el-table-column>
                                <template #header>
                                    <el-select v-model="search.type" placeholder="Tipo de documento" @change="getDocuments">
                                        <el-option value="" label="Todos los tipos" />
                                        <el-option value="constitutive" label="Acta constitutiva" />
                                        <el-option value="bank_receipt" label="Comprobante bancario" />
                                        <el-option value="address" label="Comprobante de domicilio" />
                                        <el-option value="identification" label="Identificación" />
                                    </el-select>
                                </template>
                                <template #default="scope">
                                    {{ typeDocument(scope.row.type) }}
                                </template>
                            </el-table-column>
                            <el-table-column>
                                <template #header>
                                    <el-select v-model="search.status" placeholder="Estatus" @change="getDocuments">
                                        <el-option value="" label="Todos los estatus" />
                                        <el-option value="accepted" label="Aceptado" />
                                        <el-option value="pending" label="Pendiente" />
                                        <el-option value="rejected" label="Rechazado" />
                                    </el-select>
                                </template>
                                <template #default="scope">
                                    <span class="bold" :class="statusTxt(scope.row.status).class">{{ statusTxt(scope.row.status).txt }}</span>
                                </template>
                            </el-table-column>
                            <el-table-column label="Acciones" width="150" align="center">
                                <template #default="scope">
                                    <el-button-group>
                                        <el-tooltip
                                            class="box-item"
                                            effect="dark"
                                            content="Ver documento"
                                            placement="top"
                                        >
                                            <el-button class="pl-2 pr-2" type="primary" @click="viewDocument(scope.row.document)">
                                                <font-awesome-icon :icon="['fas', 'eye']" />
                                            </el-button>
                                        </el-tooltip>
                                        <el-tooltip
                                            class="box-item"
                                            effect="dark"
                                            content="Aceptar documento"
                                            placement="top"
                                            v-if="scope.row.status == 'pending'"
                                        >
                                            <el-button class="pl-2 pr-2" type="success" @click="changeStatus(scope.row.id, 'accepted')">
                                                <font-awesome-icon :icon="['fas', 'check']" />
                                            </el-button>
                                        </el-tooltip>
                                        <el-tooltip
                                            class="box-item"
                                            effect="dark"
                                            content="Rechazar documento"
                                            placement="top"
                                            v-if="scope.row.status == 'pending'"
                                        >
                                            <el-button class="pl-2 pr-2" type="danger" @click="changeStatus(scope.row.id, 'rejected')">
                                                <font-awesome-icon :icon="['fas', 'xmark']" />
                                            </el-button>
                                        </el-tooltip>
                                    </el-button-group>
                                </template>
                            </el-table-column>
                        </el-table>
                        <el-pagination
                            class="mt-4 w-100 has-text-center"
                            v-model:current-page="pagination.currentPage"
                            v-model:page-size="pagination.pageSize"
                            :page-sizes="[25, 50, 75, 100]"
                            layout="total, sizes, prev, pager, next"
                            :total="pagination.totalRows"
                            @size-change="handleSizeChange"
                            @current-change="handleCurrentChange"
                        />
                    </el-card>
                </el-col>
            </el-row>
        </div>
    </div>
</template>

<style>
.container-fluid {
    display: flex;
}
</style>