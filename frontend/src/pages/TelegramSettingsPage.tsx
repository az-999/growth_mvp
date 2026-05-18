import { FormEvent, useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { connectTelegram, fetchTelegramStatus, getAuth } from '../api/client';
import './TelegramSettingsPage.css';

export default function TelegramSettingsPage() {
  const { shopId: shopIdParam } = useParams();
  const authShopId = getAuth()?.shopId;
  const shopId = Number(shopIdParam) || authShopId || 1;

  const [botToken, setBotToken] = useState('');
  const [chatId, setChatId] = useState('');
  const [enabled, setEnabled] = useState(true);
  const [status, setStatus] = useState<Record<string, unknown> | null>(null);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  const loadStatus = () =>
    fetchTelegramStatus(shopId)
      .then(setStatus)
      .catch((e) => setError(e.message));

  useEffect(() => {
    const auth = getAuth();
    if (auth && auth.shopId !== shopId) {
      setError('Нет доступа к этому магазину');
      return;
    }
    void loadStatus();
    // shopId only — getAuth() returns a new object each call; [auth] caused infinite refetch
  }, [shopId]);

  const handleSave = async (e: FormEvent) => {
    e.preventDefault();
    setError('');
    setMessage('');
    try {
      await connectTelegram(shopId, { botToken, chatId, enabled });
      setMessage('Настройки сохранены');
      setBotToken('');
      loadStatus();
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
            placeholder="123456:ABC-DEF..."
            required
          />
        </label>
        <label>
          Chat ID
          <input value={chatId} onChange={(e) => setChatId(e.target.value)} placeholder="987654321" required />
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
            <li>Chat ID: {(status.chatId as string) || '—'}</li>
            <li>Последняя отправка: {(status.lastSentAt as string) || '—'}</li>
            <li>Отправлено за 7 дней: {status.sentCount as number}</li>
            <li>Ошибок за 7 дней: {status.failedCount as number}</li>
          </ul>
        </section>
      )}
    </div>
  );
}
