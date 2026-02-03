const apiClient = async (url, method = 'GET', data = null) => {
    try {
        let options = {};
        if (method != 'GET') {
            options = {
                method,
                headers: {
                    'Content-type': 'application/json; charset=utf-8'
                },
                data
            };
        } else {
            options = {
                method,
                headers: {
                    'Content-type': 'application/json; charset=utf-8'
                },
                params: data
            };
        }

        const response = await axios(`${window.location.origin}/${url}`, options);
        return (response.data) ? response.data : response;
    } catch (error) {
        const defaultMsg = 'Lo sentimos ocurrió un error.<br>No es posible realizar tu pedido.';
        const extraMsg   = error.message ? `<br>${error.message}` : '';

        return {
            error: true,
            msj: error.response.data.msj || (defaultMsg + extraMsg),
            data: error.response.data.data || 'Error fatal'
        }
    }
}

export default apiClient;