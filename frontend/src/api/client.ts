/** API base: env > same-origin /api (prod behind nginx) > localhost (local dev). */
function resolveApiBase(): string {
  const fromEnv = (import.meta.env.VITE_API_URL as string | undefined)?.trim();
  if (fromEnv) {
    return `${fromEnv.replace(/\/$/, '')}/api`;
  }
  if (typeof window !== 'undefined' && !['localhost', '127.0.0.1'].includes(window.location.hostname)) {
    return `${window.location.origin}/api`;
  }
  return 'http://localhost:5000/api';
}

const API_BASE = resolveApiBase();

function headers(): HeadersInit {
  const h: HeadersInit = { 'Content-Type': 'application/json' };
  const token = localStorage.getItem('token');
  if (token) {
    h['Authorization'] = `Bearer ${token}`;
  }
  return h;
}

export async function login(email: string, password: string): Promise<string> {
  const res = await fetch(`${API_BASE}/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password }),
  });
  if (!res.ok) {
    throw new Error('Неверный email или пароль');
  }
  const data = await res.json();
  saveAuth(data.token, { email: data.email, shopId: data.shopId });
  return data.token as string;
}

export interface OrderPayload {
  number: string;
  total: number;
  customerName: string;
  count: number;
  product_id: string;
}

export interface ShopInfo {
  id: number;
  name: string;
}

export async function fetchShops(): Promise<ShopInfo[]> {
  const res = await fetch(`${API_BASE}/shops`);
  if (!res.ok) throw new Error('Не удалось загрузить магазины');
  return res.json();
}

export async function createOrder(shopId: number, payload: OrderPayload) {
  const res = await fetch(`${API_BASE}/shops/${shopId}/orders`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    const detail = err.detail ?? err.message ?? err['hydra:description'];
    throw new Error(detail || `Ошибка создания заказа (${res.status})`);
  }
  return res.json();
}

export async function fetchOrders(shopId: number) {
  const res = await fetch(`${API_BASE}/shops/${shopId}/orders`, { headers: headers() });
  if (!res.ok) throw new Error('Не удалось загрузить заказы');
  return res.json();
}

export async function connectTelegram(shopId: number, body: { botToken: string; chatId: string; enabled: boolean }) {
  const res = await fetch(`${API_BASE}/shops/${shopId}/telegram/connect`, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify(body),
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    const detail = err.detail ?? err.message ?? err['hydra:description'];
    throw new Error(detail || `Ошибка сохранения (${res.status})`);
  }
  return res.json();
}

export interface TelegramStatus {
  enabled: boolean;
  chatId: string | null;
  hasBotToken: boolean;
  lastSentAt: string | null;
  sentCount: number;
  failedCount: number;
}

export async function fetchTelegramStatus(shopId: number): Promise<TelegramStatus> {
  const res = await fetch(`${API_BASE}/shops/${shopId}/telegram/status`, { headers: headers() });
  if (!res.ok) throw new Error('Не удалось загрузить статус');
  return res.json();
}

export interface AuthUser {
  shopId: number;
  email: string;
}

export function saveAuth(token: string, user: AuthUser) {
  localStorage.setItem('token', token);
  localStorage.setItem('user', JSON.stringify(user));
}

export function getAuth(): AuthUser | null {
  const raw = localStorage.getItem('user');
  return raw ? JSON.parse(raw) : null;
}

export function logout() {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
}

export function decodeShopIdFromToken(): number | null {
  const user = getAuth();
  return user?.shopId ?? null;
}
