<script setup>
import { onMounted, ref } from 'vue';

const { index } = defineProps({
    index: {
        type: String,
        required: false,
        default: ''
    },
    countDocs: {
        type: Number,
        required: false,
        default: 0
    }
});

onMounted(() => {
    
});

const appUrl = ref(window.location.origin);
const isCollapse = ref(false);

const redirectTo = (route) => {
    location.href = appUrl.value+'/administrador/'+route;
};
</script>

<template>
    <el-menu
        default-active="2"
        class="el-menu-vertical-demo"
        :collapse="isCollapse"
        style="width: 15%; height: 100vh;"
        :default-openeds="[index]"
    >
        <el-menu-item index="1" class="mt-5">
            <img src="../../../../public/general/ticketland.png" alt="Ticketland" class="w-10 mr-2">
            <h5 class="title is-4 has-text-dark">Ticketland</h5>
        </el-menu-item>
        <el-menu-item index="2" class="mt-5" @click="redirectTo('inicio')">
            <template #title>
                <div class="my-title w-100 pl-2 pr-2 pt-3 pb-3" :class="{'active' : index == '2'}">
                    <font-awesome-icon :icon="['fas', 'house-chimney']" class="mr-2" />
                    Inicio
                </div>
            </template>
        </el-menu-item>
        <el-sub-menu index="3">
            <template #title>
                <div class="my-title w-100 pl-2 pr-2 pt-3 pb-3" :class="{'active' : index == '3'}">
                    <font-awesome-icon :icon="['fas', 'users']" class="mr-2" />
                    Usuarios
                </div>
            </template>
            <el-menu-item index="3-1" class="my-title-submenu" @click="redirectTo('usuarios-informacion')">Información</el-menu-item>
            <el-menu-item index="3-2" class="my-title-submenu" @click="redirectTo('usuarios-documentos')">
                <el-badge class="item" :value="countDocs" :offset="[10, 5]" v-if="countDocs">
                    Documentos
                </el-badge>
                <span v-if="!countDocs">Documentos</span>
            </el-menu-item>
            <el-menu-item index="3-3" class="my-title-submenu" @click="redirectTo('usuarios-contratos')">Contratos</el-menu-item>
        </el-sub-menu>
        <!-- <el-sub-menu index="3" class="mt-1" @click="redirectTo('usuarios')">
            <template #title>
                <div class="my-title w-100 pl-2 pr-2 pt-3 pb-3">
                    <font-awesome-icon :icon="['fas', 'users']" class="mr-2" />
                    Usuarios
                </div>
            </template>
            <el-sub-menu index="1-4">
                <template #title><span>item four</span></template>
                <el-menu-item index="1-4-1">item one</el-menu-item>
            </el-sub-menu>
        </el-sub-menu> -->
        <el-menu-item index="4" class="mt-1" @click="redirectTo('eventos')">
            <template #title>
                <div class="my-title w-100 pl-2 pr-2 pt-3 pb-3">
                    <font-awesome-icon :icon="['fas', 'calendar-days']" class="mr-2" />
                    Eventos
                </div>
            </template>
        </el-menu-item>
        <el-menu-item index="5" class="mt-1" @click="redirectTo('documentos')">
            <template #title>
                <div class="my-title w-100 pl-2 pr-2 pt-3 pb-3">
                    <font-awesome-icon :icon="['fas', 'file']" class="mr-2" />
                    Documentos
                </div>
            </template>
        </el-menu-item>
    </el-menu>
</template>

<style>
.el-menu-item {
    background: unset !important;
    line-height: unset !important;
}
.el-menu-item:hover {
    background: unset !important;
    line-height: unset !important;
}
.el-sub-menu__title {
    line-height: unset !important;
}
.el-sub-menu__title:hover {
    background: unset !important;
}
.my-title {
    /* padding-top: 1px; */
    font-weight: bold;
    color: #4d5564;
}
.my-title-submenu {
    /* padding-top: 1px; */
    font-weight: bold;
    color: #4d5564;
}
.my-title:hover {
    border-radius: 5px;
    background: #f2f4f7 !important;
}
.active {
    border-radius: 5px;
    background-color: #ecf3ff;
    color: #465fff !important;
}
.active:hover {
    border-radius: 5px;
    background-color: #ecf3ff !important;
    color: #465fff !important;
}
</style>