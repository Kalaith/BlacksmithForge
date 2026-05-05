import axios from 'axios';
import { getActiveToken, persistLoginUrl } from '../auth/storage';

const BASE_URL = import.meta.env.VITE_API_URL;
if (!BASE_URL) {
    throw new Error('Missing required environment variable: VITE_API_URL');
}

/**
 * Standardized Web Hatchery Axios instance.
 */
export const axiosClient = axios.create({
    baseURL: BASE_URL,
    headers: {
        'Content-Type': 'application/json',
    },
});

// Request Interceptor: Attach Auth Token
axiosClient.interceptors.request.use(
    (config) => {
        try {
            const token = getActiveToken();
            if (token) {
                config.headers.Authorization = `Bearer ${token}`;
            }
        } catch (error) {
            console.warn('Failed to parse auth token from local storage', error);
        }

        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response Interceptor: Handle 401s and standardize errors
axiosClient.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        if (error.response?.status === 401) {
            const loginUrl =
                error.response?.data?.login_url ||
                import.meta.env.VITE_WEB_HATCHERY_LOGIN_URL;

            if (loginUrl) {
                try {
                    persistLoginUrl(loginUrl);
                    window.dispatchEvent(new CustomEvent('webhatchery:login-required', { detail: { loginUrl } }));
                } catch (storageError) {
                    console.warn('Failed to persist login URL to auth storage', storageError);
                }
            }
        }
        return Promise.reject(error);
    }
);
