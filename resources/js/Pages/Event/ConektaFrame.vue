<script setup lang="js">
import { onMounted, defineProps } from 'vue';
import { showNotification } from '@/notification';

const { makePaymentParent } = defineProps({
    makePaymentParent: {
        type: Function,
        required: true
    }
});

const publicKey = import.meta.env.VITE_CONEKTA_PUBLIC_KEY;

onMounted(async () => {
    const script = document.createElement('script')
    script.src   = 'https://pay.conekta.com/v1.0/js/conekta-checkout.min.js';

    script.onload = () => {
        window.ConektaCheckoutComponents.Card({
            config: {
                targetIFrame: '#conektaIframeContainer',
                publicKey: publicKey,
                locale: 'es'
            },
            callbacks: {
                onCreateTokenSucceeded(token) {
                    makePaymentParent(token.id);
                },
                onCreateTokenError(error) {
                    let msg = 'Por favor verifica que los datos de la tarjeta sean correctos.';
                    if (error.data?.details.length) {
                        msg = '';
                        error.data.details.forEach(d => {
                            msg = msg + d.message + '<br>';
                        });
                    }
                    showNotification('¡Error!', msg, 'error', 8000);
                },
                onGetInfoSuccess(loadingTime) {
                    console.log(loadingTime)
                }
            },
            options: {
                backgroundMode: 'lightMode',
                colorPrimary: '#081133',
                colorText: '#585987',
                colorLabel: '#585987',
                hideLogo: true,
                inputType: 'minimalMode',
                excludeCardNetworks: []
            }
        });
    };

    document.head.appendChild(script);
});
</script>

<template>
    <div
        ref="conektaIframeContainer"
        id="conektaIframeContainer"
        style="height: 500px; width: 100%;"
    />
</template>