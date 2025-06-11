<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordVisible = ref(false);
const appUrl = ref(window.location.origin);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const togglePassword = () => {
    passwordVisible.value = !passwordVisible.value;
};
</script>
<template>
    <el-row>
        <el-col :xs="{span: 20, offset: 2}" :sm="{span: 20, offset: 2}" :md="{span: 12, offset: 6}" :lg="{span: 5, offset: 9}" :xl="{span: 6, offset: 9}" class="mt-6 text-center">
            <el-card class="p-5">
                <el-col :span="24">
                    <a :href="appUrl" class="is-justify-content-center is-flex">
                        <img src="../../../../public/general/ticketland.png" alt="Ticketland" class="w-25 mb-3">
                    </a>
                </el-col>
                <h3 class="title is-3 has-text-dark mb-5">Iniciar sesión</h3>
                <form class="text-left mb-5">
                    <label for="email" class="has-text-grey">Correo electrónico</label>
                    <el-input class="mb-0" name="email" id="email" autocomplete="email" v-model="form.email" />
                    <span class="text-error" v-if="form.errors.email">{{ form.errors.email }}</span>
                    <el-col :span="24" class="mt-5 mb-5">
                        <label for="password" class="has-text-grey">Contraseña</label>
                        <el-input
                            id="password"
                            class="el-form-input mb-0"
                            :type="passwordVisible ? 'text' : 'password'"
                            v-model="form.password"
                            placeholder="Introduce tu contraseña"
                            suffix-icon="Eye"
                            @suffix-icon-click="togglePassword"
                        >
                            <template #suffix>
                            <el-icon @click="togglePassword" class="password-eye">
                                <component :is="passwordVisible ? 'View' : 'Hide'" />
                            </el-icon>
                            </template>
                        </el-input>
                        <span class="text-error" v-if="form.errors.password">{{ form.errors.password }}</span>
                    </el-col>
                    <el-checkbox v-model="form.remember" label="Recuérdame" size="large" class="mb-5" />
                    <el-button
                        type="primary"
                        class="w-100 mt-1"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        Iniciar sesión
                    </el-button>
                </form>
                <a class="has-text-link" :href="route('password.request')">¿Olvidaste tu contraseña?</a>
            </el-card>
        </el-col>
    </el-row>
</template>

<style scoped>
    .password-eye {
        color: black;
        cursor: pointer;
    }
    :global(input:-webkit-autofill) {
        box-shadow: 0 0 0px 1000px white inset !important;
        -webkit-text-fill-color: #000 !important;
    }
</style>