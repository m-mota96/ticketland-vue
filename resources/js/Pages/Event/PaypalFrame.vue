<template>
    <div class="text-center justify-content-center" id="paypal-button-container" style="width: 100% !important;"></div>
</template>

<script>
import axios from "axios";

export default {
    props: {
        amount: {
            type: Number,
            required: true,
        },
        makePaymentParent: {
            type: Function,
            required: true
        }
    },
    data() {
        return {
            clientId: import.meta.env.VITE_PAYPAL_CLIENT_ID,
        }
    },
    mounted() {
        this.loadSdk();
    },
    methods: {
        loadSdk() {
            if (typeof window.paypal === "undefined") {
                const script  = document.createElement("script");
                script.id     = "paypal-sdk";
                script.src    = `https://www.paypal.com/sdk/js?client-id=${this.clientId}&currency=MXN&locale=es_MX`;
                script.onload = this.renderPaypalButton;
                document.body.appendChild(script);
            } else {
                this.renderPaypalButton();
            }
        },
        renderPaypalButton() {
            window.paypal.Buttons({
                createOrder: async () => {
                    const res = await axios.get(`${window.location.origin}/createOrder`, {
                        params: {
                            amount: this.amount,
                        },
                    });
                    // console.log(res.data);
                    return res.data.order_id; // Devuelve el orderID desde Laravel
                },
                onApprove: async (data) => {
                    // this.$emit('update-orderId', data.orderID);
                    this.makePaymentParent(data.orderID);
                    // const res = await axios.post(`${window.location.origin}/captureOrder`, {
                    //     order_id: data.orderID
                    // });
                    // alert("Pago realizado con éxito ✅");
                    // console.log("Detalles del pago:", res.data);
                },
                onError: (err) => {
                    console.error("Error en PayPal:", err);
                },
            }).render("#paypal-button-container");
        },
    },
};
</script>