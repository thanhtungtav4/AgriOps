import AsyncStorage from '@react-native-async-storage/async-storage';
import { getApiBaseUrl } from '../config/api';
import type { ApiResponse, AuthResponse, User } from '../types';

const AUTH_TOKEN_KEY = '@ariops:auth_token';
const AUTH_USER_KEY = '@ariops:auth_user';
const AUTH_EXPIRY_KEY = '@ariops:auth_expiry';

export interface AuthState {
  isAuthenticated: boolean;
  token: string | null;
  user: User | null;
  isLoading: boolean;
}

const initialState: AuthState = {
  isAuthenticated: false,
  token: null,
  user: null,
  isLoading: true,
};

class AuthService {
  private state: AuthState = { ...initialState };
  private listeners: Set<(state: AuthState) => void> = new Set();

  getState(): AuthState {
    return { ...this.state };
  }

  subscribe(listener: (state: AuthState) => void): () => void {
    this.listeners.add(listener);
    listener(this.getState());
    return () => this.listeners.delete(listener);
  }

  private notify(): void {
    this.listeners.forEach(listener => listener(this.getState()));
  }

  async initialize(): Promise<void> {
    try {
      const [token, userJson, expiry] = await Promise.all([
        AsyncStorage.getItem(AUTH_TOKEN_KEY),
        AsyncStorage.getItem(AUTH_USER_KEY),
        AsyncStorage.getItem(AUTH_EXPIRY_KEY),
      ]);

      if (token && userJson && expiry) {
        const expiryTime = new Date(expiry).getTime();
        if (expiryTime > Date.now()) {
          this.state = {
            isAuthenticated: true,
            token,
            user: JSON.parse(userJson),
            isLoading: false,
          };
          this.notify();
          return;
        }
      }
    } catch {}

    this.state = { ...initialState, isLoading: false };
    this.notify();
  }

  async login(email: string, password: string): Promise<AuthResponse> {
    const response = await fetch(`${getApiBaseUrl()}/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email, password }),
    });

    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: 'Login failed' }));
      throw new Error(error.message || 'Login failed');
    }

    const payload: ApiResponse<AuthResponse> = await response.json();
    await this.storeSession(payload.data);
    return payload.data;
  }

  async logout(): Promise<void> {
    try {
      const token = this.state.token;
      if (token) {
        await fetch(`${getApiBaseUrl()}/auth/logout`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
          },
        });
      }
    } catch {}

    await this.clearSession();
  }

  getAuthHeader(): Record<string, string> {
    const token = this.state.token;
    return token ? { Authorization: `Bearer ${token}` } : {};
  }

  private async storeSession(data: AuthResponse): Promise<void> {
    const { token, user, expires_at } = data;
    await Promise.all([
      AsyncStorage.setItem(AUTH_TOKEN_KEY, token),
      AsyncStorage.setItem(AUTH_USER_KEY, JSON.stringify(user)),
      AsyncStorage.setItem(AUTH_EXPIRY_KEY, expires_at),
    ]);

    this.state = {
      isAuthenticated: true,
      token,
      user,
      isLoading: false,
    };
    this.notify();
  }

  private async clearSession(): Promise<void> {
    await Promise.all([
      AsyncStorage.removeItem(AUTH_TOKEN_KEY),
      AsyncStorage.removeItem(AUTH_USER_KEY),
      AsyncStorage.removeItem(AUTH_EXPIRY_KEY),
    ]);

    this.state = { ...initialState, isLoading: false };
    this.notify();
  }
}

export const authService = new AuthService();
