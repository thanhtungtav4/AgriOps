export const API_CONFIG = {
  BASE_URL: 'http://localhost:8000/api/v1',
  TIMEOUT_MS: 15000,
  RETRY_DELAY_MS: 3000,
  MAX_RETRIES: 3,
} as const;

export const setApiBaseUrl = (url: string): void => {
  (API_CONFIG as { BASE_URL: string }).BASE_URL = url;
};

export const getApiBaseUrl = (): string => API_CONFIG.BASE_URL;