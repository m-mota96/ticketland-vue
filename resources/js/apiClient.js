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
        return {
            error: true,
            msj: error.response.data.msj,
            data: error.response.data.data
        }
    }
}

export default apiClient;