import { FormEvent, useCallback, useEffect, useState } from 'react';
import { Navigate, useParams } from 'react-router-dom';
import {
  connectTelegram,
  fetchTelegramStatus,
  getAuth,
  type TelegramStatus,
} from '../api/client';
import './TelegramSettingsPage.css';

function maskChatId(chatId: string): string {
  if (chatId.length <= 4) return '****';
  return '*'.repeat(chatId.length - 4) + chatId.slice(-4);
}

export default function TelegramSettingsPage() {
  const { shopId: shopIdParam } = useParams();
  const auth = getAuth();
  const shopId = auth?.shopId;

  if (!shopId) {
    return <Navigate to="/login" replace />;
  }

  const routeShopId = Number(shopIdParam);
  if (shopIdParam && routeShopId !== shopId) {
    return <Navigate to={`/shops/${shopId}/growth/telegram`} replace />;
  }

  const [botToken, setBotToken] = useState('');
  const [chatId, setChatId] = useState('');
  const [enabled, setEnabled] = useState(true);
  const [hasBotToken, setHasBotToken] = useState(false);
  const [status, setStatus] = useState<TelegramStatus | null>(null);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  const applyStatus = useCallback((data: TelegramStatus) => {
    setStatus(data);
    setEnabled(data.enabled);
    setHasBotToken(data.hasBotToken);
    if (data.chatId) {
      setChatId(data.chatId);
    }
  }, []);

  const loadStatus = useCallback(() => {
    return fetchTelegramStatus(shopId)
      .then(applyStatus)
      .catch((e) => setError(e.message));
  }, [shopId, applyStatus]);

  useEffect(() => {
    void loadStatus();
  }, [loadStatus]);

  const handleSave = async (e: FormEvent) => {
    e.preventDefault();
    setError('');
    setMessage('');
    try {
      const saved = await connectTelegram(shopId, { botToken, chatId, enabled });
      setChatId(saved.chatId as string);
      setEnabled(saved.enabled as boolean);
      setHasBotToken(true);
      setBotToken('');
      setMessage('Настройки сохранены');
      await loadStatus();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Ошибка');
    }
  };

  return (
    <div className="telegram-settings">
      <h1>Настройки Telegram</h1>

      <aside className="hint">
        <strong>Как узнать chatId:</strong> напишите боту @userinfobot или @getidsbot в Telegram — он вернёт ваш chat id.
        Для группы добавьте бота в группу и используйте id группы (обычно отрицательное число).
      </aside>

      <form onSubmit={handleSave} className="settings-form">
        <label>
          Bot Token
          <input
            type="password"
            value={botToken}
            onChange={(e) => setBotToken(e.target.value)}
            placeholder={
              hasBotToken ? 'Токен сохранён — введите новый, только если нужно заменить' : '123456:ABC-DEF...'
            }
            required={!hasBotToken}
            autoComplete="off"
          />
        </label>
        <label>
          Chat ID
          <input
            value={chatId}
            onChange={(e) => setChatId(e.target.value)}
            placeholder="987654321"
            required
          />
        </label>
        <label className="toggle">
          <input type="checkbox" checked={enabled} onChange={(e) => setEnabled(e.target.checked)} />
          Включить уведомления
        </label>
        {error && <p className="alert alert-error">{error}</p>}
        {message && <p className="alert alert-success">{message}</p>}
        <button type="submit" className="btn btn-primary">
          Сохранить
        </button>
      </form>

      {status && (
        <section className="status-block">
          <h2>Статус интеграции</h2>
          <ul>
            <li>Включено: {status.enabled ? 'да' : 'нет'}</li>
            <li>Chat ID: {status.chatId ? maskChatId(status.chatId) : '—'}</li>
            <li>Bot Token: {status.hasBotToken ? 'сохранён' : 'не задан'}</li>
            <li>Последняя отправка: {status.lastSentAt || '—'}</li>
            <li>Отправлено за 7 дней: {status.sentCount}</li>
            <li>Ошибок за 7 дней: {status.failedCount}</li>
          </ul>
        </section>
      )}
    </div>
  );
}
