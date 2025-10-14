<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const appUrl = ref(window.location.origin);
const passwordVisible = ref(false);
const passwordConfirmVisible = ref(false);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const errors = ref({
    email: false
});

const submit = () => {
    if (validate()) {
        form.post(route('register'), {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    }
};

const togglePassword = () => {
    passwordVisible.value = !passwordVisible.value;
};

const togglePasswordConfirm = () => {
    passwordConfirmVisible.value = !passwordConfirmVisible.value;
};

const validate = () => {
    let valid       = true;
    const mailRegex =  /^\w+([.-_+]?\w+)*@\w+([.-]?\w+)*(\.\w{2,10})+$/;
    if (!mailRegex.test(form.email)) {
        errors.value.email = true;
        valid              = false;
    }
    return valid;
};

const isNumber = (evt) => {
    const charCode = evt.which ? evt.which : evt.keyCode;
    if (charCode < 48 || charCode > 57) {
        evt.preventDefault();
    }
};
</script>

<template>
    <el-row>
        <el-col :xs="{span: 20, offset: 2}" :sm="{span: 20, offset: 2}" :md="{span: 12, offset: 6}" :lg="{span: 6, offset: 9}" :xl="{span: 6, offset: 9}" class="mt-6 text-center">
            <el-card class="p-5">
                <el-col :span="24">
                    <a :href="appUrl" class="is-justify-content-center is-flex">
                        <img src="../../../../public/general/ticketland.png" alt="Ticketland" class="w-25 mb-3">
                    </a>
                </el-col>
                <h3 class="title is-3 has-text-dark mb-5">Registro</h3>
                <form class="text-left mb-5">
                    <el-col :span="24" class="mb-3">
                        <label for="name" class="has-text-grey">Nombre <span class="has-text-danger">*</span></label>
                        <el-input
                            class="el-form-item mb-0"
                            :class="{'is-error': form.errors.name}"
                            name="name"
                            id="name"
                            autocomplete="name"
                            v-model="form.name"
                            clearable
                        />
                        <span class="text-error" v-if="form.errors.name">{{ form.errors.name }}</span>
                    </el-col>
                    <el-col :span="24" class="mb-3">
                        <label for="email" class="has-text-grey">Correo electrónico <span class="has-text-danger">*</span></label>
                        <el-input
                            class="el-form-item mb-0"
                            :class="{'is-error': form.errors.email || errors.email}"
                            name="email"
                            id="email"
                            autocomplete="email"
                            v-model="form.email"
                            clearable
                        />
                        <span class="text-error" v-if="form.errors.email">{{ form.errors.email }}</span>
                        <span class="text-error" v-if="errors.email">Por favor ingresa un correo válido.</span>
                    </el-col>
                    <el-col :span="24" class="mb-3">
                        <label for="phone" class="has-text-grey">Teléfono <span class="has-text-danger">*</span></label>
                        <el-input
                            class="el-form-item mb-0"
                            :class="{'is-error': form.errors.phone}"
                            name="phone"
                            id="phone"
                            autocomplete="phone"
                            v-model="form.phone"
                            @keypress="isNumber($event)"
                            clearable
                        />
                        <span class="text-error" v-if="form.errors.phone">{{ form.errors.phone }}</span>
                    </el-col>
                    <el-col :span="24" class="mb-3">
                        <label for="password" class="has-text-grey">Contraseña <span class="has-text-danger">*</span></label>
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
                    <el-col :span="24" class="mb-3">
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
                    <el-col :span="24" class="mb-5 mt-5 text-right">
                        <el-button type="primary" class="w-100" @click="submit">Registrarme</el-button>
                    </el-col>
                    <el-col :span="24" class="mb-5 text-center">
                        <a :href="route('login')" class="has-text-link mr-3">¿Ya estas registrado?</a>
                    </el-col>
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