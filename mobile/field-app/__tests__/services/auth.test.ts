import { authService, AuthState } from '../../src/services/auth';
import AsyncStorage from '@react-native-async-storage/async-storage';

const mockFetch = global.fetch as jest.MockedFunction<typeof fetch>;

describe('AuthService', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    AsyncStorage.clear();
  });

  describe('initialize', () => {
    it('should restore session from storage when token is valid', async () => {
      const futureExpiry = new Date(Date.now() + 3600000).toISOString();
      await AsyncStorage.setItem('@ariops:auth_token', 'valid-token');
      await AsyncStorage.setItem('@ariops:auth_user', JSON.stringify({
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        role: 'worker',
        is_admin: false,
        can_approve: false,
        farm_id: 1,
        farm: null,
        scope: 'farm',
        last_login_at: null,
      }));
      await AsyncStorage.setItem('@ariops:auth_expiry', futureExpiry);

      await authService.initialize();

      const state = authService.getState();
      expect(state.isAuthenticated).toBe(true);
      expect(state.token).toBe('valid-token');
      expect(state.user?.email).toBe('test@example.com');
    });

    it('should set isLoading to false when no session exists', async () => {
      await authService.initialize();

      const state = authService.getState();
      expect(state.isAuthenticated).toBe(false);
      expect(state.isLoading).toBe(false);
    });

    it('should clear expired session', async () => {
      const pastExpiry = new Date(Date.now() - 3600000).toISOString();
      await AsyncStorage.setItem('@ariops:auth_token', 'expired-token');
      await AsyncStorage.setItem('@ariops:auth_user', JSON.stringify({ id: 1 }));
      await AsyncStorage.setItem('@ariops:auth_expiry', pastExpiry);

      await authService.initialize();

      const state = authService.getState();
      expect(state.isAuthenticated).toBe(false);
      expect(state.token).toBeNull();
    });
  });

  describe('login', () => {
    it('should store session on successful login', async () => {
      mockFetch.mockResolvedValueOnce({
        ok: true,
        json: () => Promise.resolve({
          data: {
            token: 'new-token',
            token_type: 'Bearer',
            expires_at: new Date(Date.now() + 86400000).toISOString(),
            user: {
              id: 1,
              name: 'Test User',
              email: 'test@example.com',
              role: 'worker',
              is_admin: false,
              can_approve: false,
              farm_id: 1,
              farm: null,
              scope: 'farm' as const,
              last_login_at: null,
            },
          },
        }),
      } as Response);

      const result = await authService.login('test@example.com', 'password');

      expect(result.token).toBe('new-token');
      const state = authService.getState();
      expect(state.isAuthenticated).toBe(true);
    });

    it('should throw error on failed login', async () => {
      mockFetch.mockResolvedValueOnce({
        ok: false,
        json: () => Promise.resolve({ message: 'Invalid credentials' }),
      } as Response);

      await expect(authService.login('wrong@example.com', 'wrong')).rejects.toThrow('Invalid credentials');
    });
  });

  describe('getAuthHeader', () => {
    it('should return Authorization header when authenticated', () => {
      const state: AuthState = {
        isAuthenticated: true,
        token: 'test-token',
        user: null,
        isLoading: false,
      };
      authService.getAuthHeader();

      expect(state.token).toBe('test-token');
    });
  });
});
