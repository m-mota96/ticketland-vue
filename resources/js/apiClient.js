const apiClient = async (url, method = 'GET', data = null)=> {
    try {
        let options = {
            method,
            headers: {
                'Content-type': 'application/json; charset=utf-8'
            },
            data: JSON.stringify(data),
        };

        const response = await axios(`${window.location.origin}/${url}`, options);
        return response.data;
    } catch (error) {
        return error.response.data;
    }
}

export default apiClient;