import axios from 'axios';

let axiosInstance = axios.create({
    baseURL: 'https://codechefprogrammingpractice.000webhostapp.com',
    /* other custom settings */
});

export default axiosInstance;