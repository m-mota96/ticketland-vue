<template>
    <div>
        <div class="text-center justify-content-center" id="paypal-button-container"></div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    props: {
        amount: {
            type: String,
            required: true,
        },
        handleMakePayment: {
            type: Function,
            required: true
        }
    },
    mounted() {
        if (typeof window.paypal === "undefined") {
            const script  = document.createElement("script");
            script.src    ="https://www.paypal.com/sdk/js?client-id=ATPIxQhqpEEK7DhZ-QlaGbA6V7SfAr1dr7hbWyeJnAl5oNu5rOEv37SV-LP1EZLjQf55nRy5uff-LE54&currency=MXN&locale=es_MX";
            script.onload = this.renderPaypalButton;
            document.body.appendChild(script);
        } else {
            this.renderPaypalButton();
        }
    },
    methods: {
        renderPaypalButton() {
            window.paypal.Buttons({
                createOrder: async () => {
                    const res = await axios.post(`${window.location.origin}/createOrder`, {
                        amount: this.amount,
                    });
                    // console.log(res.data);
                    return res.data.order_id; // Devuelve el orderID desde Laravel
                },
                onApprove: async (data) => {
                    this.$emit('update-orderId', data.orderID);
                    this.handleMakePayment();
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