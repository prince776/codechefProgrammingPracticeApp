import axios from 'axios';

let axiosInstance = axios.create({
    baseURL: 'http://localhost:8080',
    /* other custom settings */
});

export default axiosInstance;