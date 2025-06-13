<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const appUrl = ref(window.location.origin);
const passwordVisible = ref(false);
const passwordConfirmVisible = ref(false);

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const togglePassword = () => {
    passwordVisible.value = !passwordVisible.value;
};

const togglePasswordConfirm = () => {
    passwordConfirmVisible.value = !passwordConfirmVisible.value;
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
                <form class="text-left">
                    <el-row>
                        <el-col :span="24" class="mb-4 mt-5">
                            <label for="email" class="has-text-grey">Correo electrónico</label>
                            <el-input v-model="form.email" class="mb-0" disabled readonly />
                            <span class="text-error" v-if="form.errors.email">{{ form.errors.email }}</span>
                        </el-col>
                        <el-col :span="24" class="mb-4">
                            <label for="password" class="has-text-grey">Contraseña</label>
                            <el-input
                                class="el-form-item mb-0"
                                :class="{ 'is-error': form.errors.password }"
                                id="password"
                                v-model="form.password"
                                :type="passwordVisible ? 'text' : 'password'"
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
                        <el-col :span="24" class="mb-4">
                            <label for="password_confirmation" class="has-text-grey">Confirmar contraseña</label>
                            <el-input
                                class="el-form-item mb-0"
                                :class="{ 'is-error': form.errors.password_confirmation }"
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="passwordConfirmVisible ? 'text' : 'password'"
                                suffix-icon="Eye"
                                @suffix-icon-click="togglePasswordConfirm"
                            >
                                <template #suffix>
                                    <el-icon @click="togglePasswordConfirm" class="password-eye">
                                        <component :is="passwordConfirmVisible ? 'View' : 'Hide'" />
                                    </el-icon>
                                </template>
                            </el-input>
                            <span class="text-error" v-if="form.errors.password_confirmation">{{ form.errors.password_confirmation }}</span>
                        </el-col>
                        <el-col :span="24" class="mb-4 text-right">
                            <el-button
                                type="primary"
                                @click="submit"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Restablecer contraseña
                            </el-button>
                        </el-col>
                    </el-row>
                </form>
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