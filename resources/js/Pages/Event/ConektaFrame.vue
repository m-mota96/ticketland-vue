<script setup lang="js">
import { onMounted, defineProps } from 'vue';

// const { addPaymentMethodParent } = defineProps({
//     addPaymentMethodParent: {
//         type: Function,
//         required: true
//     }
// });

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
                    console.log(token);
                    // addPaymentMethodParent(token);
                },
                onCreateTokenError(error) {
                    console.log('ERROR', error)
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