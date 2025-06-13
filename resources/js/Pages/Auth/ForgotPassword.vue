<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    status: {
        type: String,
    },
});

const appUrl = ref(window.location.origin);

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
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
                <p class="text-justify has-text-black mb-4">
                    ¿Olvidaste tu contraseña? No hay problema. 
                    Simplemente indícanos tu correo electrónico 
                    y te enviaremos un enlace para restablecer 
                    tu contraseña. Podrás elegir una nueva.
                </p>
                <p class="text-justify has-text-success mt-4 mb-5" v-if="status">
                    {{ status }}
                </p>
                <el-col :span="24">
                    <label for="email">Correo electrónico</label>
                    <el-input 
                        class="el-form-item mb-0"
                        :class="{'is-error': form.errors.email }"
                        name="email"
                        id="email"
                        autocomplete="email"
                        required
                        type="email"
                        v-model="form.email"
                        clearable
                    />
                    <span class="text-error" v-if="form.errors.email">{{ form.errors.email }}</span>
                </el-col>
                <el-col :span="24" class="text-right mt-4">
                    <el-button
                    type="primary"
                        :disabled="form.processing"
                        :class="{'is-error': form.errors.email, 'opacity-25': form.processing}"
                        @click="submit"
                    >
                        Restablecer contraseña
                    </el-button>
                </el-col>
            </el-card>
        </el-col>
    </el-row>
</template>

<style scoped>
    :global(input:-webkit-autofill) {
        box-shadow: 0 0 0px 1000px white inset !important;
        -webkit-text-fill-color: #000 !important;
    }
</style>