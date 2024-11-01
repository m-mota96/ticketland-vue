<template>
    <Menu></Menu>
    <div class="container is-max-desktop">
        <el-row :gutter="20" class="mt-10">
            <el-col :xs="24" :sm="24" :md="12" :lg="16" :xl="1">
                <el-input placeholder="Buscar por nombre de evento" size="large" v-model="search" />
            </el-col>
            <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="11">
                <el-card class="p-4">
                    <template #header>
                        <div class="card-header is-justify-content-center is-flex p-0">
                            <span>OPCIONES</span>
                        </div>
                    </template>
                    <el-button class="w-100 mt-1" type="warning" size="large" @click="$refs.CreateEvent.showHideModal()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'plus']" /> Crear nuevo evento
                    </el-button>
                </el-card>
            </el-col>
        </el-row>
    </div>

    <CreateEvent ref="CreateEvent" />
</template>
  
<script>
import Menu from './Menu.vue';
import CreateEvent from './Modals/CreateEvent.vue';
  
export default {
    components: {
        //Aqui se agregan los componentes, en dado caso que quiereas usar, para separar código, yo separo los modales y aqui los agrego
        Menu,
        CreateEvent
    },
    data() {
        return {
            //Aquí se declaran las variables
            search: '',
        }
    },
    beforeMount() {
        //Aqui se pueden mandar llamar metodos antes de que se monte el componente
    },
    mounted() {
        
    },
    created() {
        //Aqui se pueden mandar llamar métodos, cuando se crea el componente
    },
   
    methods: {
    // Aqui van los métodos
        
    }
}
</script>
  
<style>
    .w-100 {
        width: 100% !important;
    }
    .p-0 {
        padding: 0px !;
    }
    input[type='text']:focus,
    input[type='email']:focus,
    input[type='url']:focus,
    input[type='password']:focus,
    input[type='number']:focus,
    input[type='date']:focus,
    input[type='datetime-local']:focus,
    input[type='month']:focus,
    input[type='search']:focus,
    input[type='tel']:focus,
    input[type='time']:focus,
    input[type='week']:focus,
    input[multiple]:focus,
    /* textarea:focus, */
    select:focus {
        box-shadow: unset !important;
    }
</style>