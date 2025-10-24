<template>
    <el-dialog
        v-model="activeUploadImages"
        :title="titleDialog"
        width="500"
        align-center
        style="margin-top: 5% !important;"
        :lock-scroll="false"
    >
        <label class="bold">{{ textLabel }} <span class="has-text-danger">*</span></label>
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
            :on-change="handleFileChange"
        >
            <el-icon class="el-icon--upload"><font-awesome-icon :icon="['fas', 'cloud-arrow-up']" /></el-icon>
            <div class="el-upload__text">
                Arrastra la imagen o haz <em>clic para cargar</em>
            </div>
            <template #tip>
                <div class="el-upload__tip">
                    <span class="text-error" v-if="error">Elige una imagen para cargar.</span>
                    <!-- jpg/png files with a size less than 500kb -->
                    <h5 class="subtitle is-6 has-text-dark mt-4"><b>NOTA:</b> La imagen debe ser en formato jpg o png no mayor a 1MB. Recomendado {{ textSize }}</h5>
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
            typeUpload: '',
            error: false,
            selectedFiles: []
        }
    },
    mounted() {
        
    },
    methods: {
        showUploadImages(type) {
            this.error = false;
            this.clearFile();
            this.typeUpload  = type;
            switch (type) {
                case 'profile':
                    this.titleDialog = 'Cambia la imagen de fondo';
                    this.textLabel   = 'Imagen de fondo';
                    this.textSize    = '1530px x 630px';
                    break;
                case 'logo':
                    this.titleDialog = 'Edita el logo de tu evento';
                    this.textLabel   = 'Logo';
                    this.textSize    = '250px x 250px';
                    break;
            }
            this.isDisabled = false;
            this.activeUploadImages = true;
        },
        async saveInfo() {
            this.error = false;
            if (this.selectedFiles.length === 0) {
                this.error = true;
                return
            }
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
        handleFileChange(file, fileList) {
            this.selectedFiles = fileList;
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