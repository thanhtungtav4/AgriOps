import React, { createContext, useContext, useState, useEffect, useCallback, useRef } from 'react';
import axios from 'axios';

const AuthContext = createContext(null);

const API_BASE = '/api/v1';
const TOKEN_KEY = 'operations_token';
const USER_KEY = 'operations_user';
const LOGIN_PATH = '/operations/login';

const clearAuthStorage = () => {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
};

const getStoredToken = () => localStorage.getItem(TOKEN_KEY);

const clearAuthToken = () => {
    delete axios.defaults.headers.common['Authorization'];
    clearAuthStorage();
};

const setAuthToken = (token) => {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    localStorage.setItem(TOKEN_KEY, token);
};

const safeJsonParse = (jsonString, fallback = null) => {
    try {
        return JSON.parse(jsonString);
    } catch {
        return fallback;
    }
};

const extractErrorMessage = (err) => {
    if (!err.response) {
        return 'Không thể kết nối máy chủ. Vui lòng thử lại.';
    }
    const data = err.response.data;
    return data?.error?.message || data?.message || data?.error || `Lỗi ${err.response.status}: Vui lòng thử lại.`;
};

export function AuthProvider({ children }) {
    const [token, setToken] = useState(getStoredToken);
    const [user, setUser] = useState(() => {
        const stored = localStorage.getItem(USER_KEY);
        return stored ? safeJsonParse(stored, null) : null;
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const isLoggingOut = useRef(false);

    const logout = useCallback(() => {
        isLoggingOut.current = true;
        clearAuthToken();
        setToken(null);
        setUser(null);
        axios.post(`${API_BASE}/auth/logout`).catch(() => {});
    }, []);

    useEffect(() => {
        if (token) {
            setAuthToken(token);
        } else if (!isLoggingOut.current) {
            clearAuthToken();
        } else {
            clearAuthStorage();
        }
    }, [token]);

    useEffect(() => {
        if (!token) {
            setLoading(false);
            return;
        }

        axios.get(`${API_BASE}/auth/me`)
            .then(response => {
                setUser(response.data.data);
                localStorage.setItem(USER_KEY, JSON.stringify(response.data.data));
                setError(null);
            })
            .catch(err => {
                if (err.response?.status === 401) {
                    logout();
                }
            })
            .finally(() => setLoading(false));
    }, [token, logout]);

    const login = useCallback(async (email, password) => {
        setLoading(true);
        setError(null);
        try {
            const response = await axios.post(`${API_BASE}/auth/login`, { email, password });
            const { token: newToken, user: userData } = response.data.data;
            setToken(newToken);
            setUser(userData);
            localStorage.setItem(TOKEN_KEY, newToken);
            localStorage.setItem(USER_KEY, JSON.stringify(userData));
            return true;
        } catch (err) {
            setError(extractErrorMessage(err));
            return false;
        } finally {
            setLoading(false);
        }
    }, []);

    const clearError = useCallback(() => setError(null), []);

    return (
        <AuthContext.Provider value={{
            token,
            user,
            loading,
            error,
            login,
            logout,
            clearError,
            isAuthenticated: !!token && !!user,
        }}>
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within AuthProvider');
    }
    return context;
}

export { clearAuthToken, clearAuthStorage, getStoredToken };
export default AuthContext;