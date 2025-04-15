<template>
    <el-dialog
        v-model="activeUploadImages"
        :title="titleDialog"
        width="500"
        align-center
        style="margin-top: 5% !important;"
    >
        <label>{{ textLabel }}</label>
        <el-upload
            ref="uploadRef"
            class="upload-demo mb-5 mt-1"
            drag
            :data="{event_id: dadEvent.id, type: typeUpload}"
            :action="appUrl+'/customer/uploadImages'"
            :headers="{'X-CSRF-TOKEN': token}"
            multiple
            :auto-upload="false"
            accept=".jpg,.png"
            list-type="picture"
            :limit="1"
            :on-success="handleSuccess"
            :on-error="handleError"
        >
            <el-icon class="el-icon--upload"><font-awesome-icon :icon="['fas', 'cloud-arrow-up']" /></el-icon>
            <div class="el-upload__text">
                Arrastre la imagen o haga <em>click para cargar</em>
            </div>
            <template #tip>
                <div class="el-upload__tip mt-4">
                    <!-- jpg/png files with a size less than 500kb -->
                    <h5 class="subtitle is-6 has-text-dark"><b>NOTA:</b> La imagen debe ser en formato jpg, png no mayor a 1MB. Recomendado {{ textSize }}</h5>
                </div>
            </template>
        </el-upload>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="activeUploadImages = false">Cancelar</el-button>
                <el-button type="primary" @click="saveInfo" :disabled="isDisabled">Guardar cambios</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script>
import { showNotification } from '@/notification';
export default {
    props: {
        dadEvent: Object,
        dadImageProfile: String,
        dadImageLogo: String
    },
    data() {
        return {
            token: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            titleDialog: '',
            textLabel: '',
            textSize: '',
            appUrl: window.location.origin,
            activeUploadImages: false,
            isDisabled: false,
            typeUpload: ''
        }
    },
    mounted() {
        
    },
    methods: {
        showUploadImages(type) {
            this.clearFile();
            this.typeUpload  = type;
            switch (type) {
                case 'profile':
                    this.titleDialog = 'Cambia la imagen de fondo';
                    this.textLabel   = 'Imagen de fondo';
                    this.textSize    = '1530px x 630px';
                    break;
                case 'logo':
                    this.titleDialog = 'Edita el logo del evento';
                    this.textLabel   = 'Logo';
                    this.textSize    = '250px x 250px';
                    break;
            }
            this.isDisabled = false;
            this.activeUploadImages = true;
        },
        async saveInfo() {
            this.isDisabled = true;
            this.$refs.uploadRef.submit();
        },
        handleSuccess(response, file, fileList) {
            const refImage = this.typeUpload == 'profile' ? 'update-profile' : 'update-logo';
            this.$emit(refImage, response.data);
            this.activeUploadImages = false;
            showNotification('¡Correcto!', response.msj, 'success');
            this.clearFile();
            this.isDisabled = false;
        },
        handleError(response) {
            this.isDisabled = false;
            response = JSON.parse(response.message);
            showNotification('¡Error!', response.data, 'error', 10000);
        },
        clearFile() {
            if (this.$refs.uploadRef) {
                this.$refs.uploadRef.clearFiles();
            }
        }
    }
}
</script>

<style>

</style>