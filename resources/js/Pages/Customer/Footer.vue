<template>
    <el-row :gutter="20" class="mb-6 mt-6" :class="{'pl-3 pr-3': dataExpand}">
        <el-col :span="span" :offset="offset">
            <el-row :gutter="20" class="border">
                <el-col :span="8" class="pt-2">
                    <p class="has-text-dark">
                        <span>Términos y condiciones</span>
                        <span class="ml-4">Políticas de privacidad</span>
                    </p>
                </el-col>
                <el-col :span="8" class="text-center pt-6">
                    <p>
                        © Derechos reservados {{ currentYear }} - Hecho en México
                    </p>
                </el-col>
                <el-col :span="8" class="pt-2 text-right">
                    <p class="has-text-dark">
                        <span><font-awesome-icon :icon="['fas', 'phone-flip']" /> Llámanos al 33 1149 4592</span>
                        <span class="ml-4">
                            <a class="has-text-dark" href="https://www.facebook.com/ticketland.mx" target="_blank"><font-awesome-icon class="bold" :icon="['fab', 'facebook-f']" /> Facebook</a>
                        </span>
                    </p>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
</template>

<script>
export default {
    props: {
        dataExpand: {
            type: Boolean,
            required: false,
            default: false
        }
    },
    data() {
        return {
            currentYear: new Date().getFullYear(),
            span: 14,
            offset: 5
        }
    },
    mounted() {
        if (this.dataExpand) {
            this.span = 24;
            this.offset = 0;
        }
    }
}
</script>

<style scoped>
    p {
        font-size: 0.8rem;
    }
    .border {
        margin-top: 1rem;
        margin-bottom: 1rem;
        border: 0;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
    }
</style>