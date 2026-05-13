import axios from 'axios';

const TOKEN_KEY = 'operations_token';
const USER_KEY = 'operations_user';
const LOGIN_PATH = '/operations/login';

const api = axios.create({
    baseURL: '/api/v1',
});

api.interceptors.request.use(config => {
    const token = localStorage.getItem(TOKEN_KEY);
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

api.interceptors.response.use(
    response => response,
    async error => {
        if (error.response?.status === 401) {
            const currentPath = window.location.pathname;
            if (currentPath !== LOGIN_PATH) {
                localStorage.removeItem(TOKEN_KEY);
                localStorage.removeItem(USER_KEY);
                delete axios.defaults.headers.common['Authorization'];
                window.location.href = LOGIN_PATH;
            }
        }
        return Promise.reject(error);
    }
);

export default api;