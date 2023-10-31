// plugins/axios.js

import axios from 'axios';

const axiosInstance = axios.create({
  baseURL: 'http://backend.test/api/',
});

export default axiosInstance;
