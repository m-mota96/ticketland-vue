const apiClient = async (url, data, method = 'GET')=> {
    try {
        let options = {
            method,
            headers: {
                'Content-type': 'application/json; charset=utf-8'
            },
            data: JSON.stringify(data),
        };

        const response = await axios(`${window.location.origin}/${url}`, options);
        return response;
    } catch (error) {
        return error.response.data;
    }
}

export default apiClient;