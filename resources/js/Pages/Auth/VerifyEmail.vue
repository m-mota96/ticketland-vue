<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import apiClient from '@/apiClient';

const props = defineProps({
    status: {
        type: String,
    },
});

const appUrl = ref(window.location.origin);
const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const logout = async () => {
    await apiClient('logout', 'POST');
    location.href = window.location.origin;
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <el-row>
        <el-col :xs="{span: 20, offset: 2}" :sm="{span: 20, offset: 2}" :md="{span: 12, offset: 6}" :lg="{span: 5, offset: 9}" :xl="{span: 6, offset: 9}" class="mt-6">
            <el-card class="p-5">
                <el-col :span="24">
                    <a :href="appUrl" class="is-justify-content-center is-flex">
                        <img src="../../../../public/general/ticketland.png" alt="Ticketland" class="w-25 mb-5">
                    </a>
                </el-col>
                <p class="text-justify has-text-black">
                    ¡Gracias por registrarte!
                    <br>Antes de empezar, ¿Podrías verificar tu dirección de correo electrónico 
                    haciendo clic en el enlace que te acabamos de enviar? Si no lo recibiste, con gusto te enviaremos otro.
                </p>
                <p class="text-justify has-text-success mt-4 mb-5" v-if="verificationLinkSent">
                    Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que proporcionaste durante el registro.
                </p>
                <el-row class="mt-3">
                    <el-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14" class="mb-3">
                        <el-button
                            type="primary"
                            class="w-100"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            @click="submit"
                        >
                            Reenviar correo
                        </el-button>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="12" :lg="10" :xl="14" class="has-text-right-desktop pt-1 mb-3">
                        <p @click="logout" class="has-text-link pointer">Cerrar sesión</p>
                    </el-col>
                </el-row>
            </el-card>
        </el-col>
    </el-row>
</template>

<style scoped>

</style>