import { Capacitor } from '@capacitor/core';

const API_URL_OVERRIDE_KEY = 'api_url_override';

export function resolveApiUrl(baseUrl: string): string {
  const override = localStorage.getItem(API_URL_OVERRIDE_KEY)?.trim();
  if (override) {
    return override;
  }

  // Android emulator cannot reach host machine via localhost.
  if (Capacitor.getPlatform() === 'android') {
    return baseUrl
      .replace('://localhost', '://10.0.2.2')
      .replace('://127.0.0.1', '://10.0.2.2');
  }

  return baseUrl;
}

export function setApiUrlOverride(url: string): void {
  localStorage.setItem(API_URL_OVERRIDE_KEY, url.trim());
}

export function clearApiUrlOverride(): void {
  localStorage.removeItem(API_URL_OVERRIDE_KEY);
}
