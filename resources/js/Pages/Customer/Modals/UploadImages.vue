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
            v-model="file.image"
            class="upload-demo mb-5 mt-1"
            drag
            :data="{event_id: dadEvent.id}"
            :action="appUrl+'/customer/uploadImages'"
            multiple
            :auto-upload="false"
            accept=".jpg,.png"
            list-type="picture"
            :limit="1"
            :on-success="handleSuccess"
        >
            <el-icon class="el-icon--upload"><font-awesome-icon :icon="['fas', 'cloud-arrow-up']" /></el-icon>
            <div class="el-upload__text">
                Arrastre la imagen o haga <em>click para cargar</em>
            </div>
            <template #tip>
                <div class="el-upload__tip mt-4">
                    <!-- jpg/png files with a size less than 500kb -->
                    <h5 class="subtitle is-6 has-text-dark"><b>NOTA:</b> La imagen debe ser en formato jpg, png no mayor a 1MB. Recomendado 1530px x 630px</h5>
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
import apiClient from '@/apiClient';
export default {
    props: {
        dadEvent: Object
    },
    data() {
        return {
            titleDialog: '',
            textLabel: '',
            appUrl: window.location.origin,
            activeUploadImages: false,
            isDisabled: false,
            file: {
                id: this.dadEvent.id,
                image: {
                    name: '',
                    url: ''
                }
            }
        }
    },
    methods: {
        showUploadImages(type) {
            switch (type) {
                case 'profile':
                    this.titleDialog = 'Cambiar la imagen de fondo';
                    this.textLabel   = 'Imagen de fondo';
                    break;
                case 'logo':
                    
                    break;
            }
            this.activeUploadImages = true;
        },
        async saveInfo() {
            this.$refs.uploadRef.submit();
            // const response = apiClient('customer/uploadImages', 'POST', {event_id: this.dadEvent.id});
            // console.log(response);
        },
        handleSuccess(response, file, fileList) {
            console.log('Respuesta del servidor:', response);
        }
    }
}
</script>

<style>

</style>