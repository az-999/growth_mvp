import { FormEvent, useState } from 'react';
import type { Bouquet } from '../data/bouquets';
import { createOrder } from '../api/client';
import './OrderModal.css';

interface Props {
  bouquet: Bouquet;
  shopId: number;
  onClose: () => void;
  onSuccess: () => void;
}

export default function OrderModal({ bouquet, shopId, onClose, onSuccess }: Props) {
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [quantity, setQuantity] = useState(1);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      const number = `A-${Date.now().toString(36).toUpperCase()}`;
      const total = bouquet.price * quantity;
      const customerName = `${name}, тел. ${phone}, ×${quantity}`;
      await createOrder(shopId, { number, total, customerName });
      onSuccess();
      onClose();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Ошибка');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal" onClick={(e) => e.stopPropagation()}>
        <h3>Заказ: {bouquet.name}</h3>
        <p className="modal-price">{bouquet.price} ₽ × {quantity} = {bouquet.price * quantity} ₽</p>
        <form onSubmit={handleSubmit}>
          <label>
            Имя
            <input value={name} onChange={(e) => setName(e.target.value)} required />
          </label>
          <label>
            Телефон
            <input value={phone} onChange={(e) => setPhone(e.target.value)} required type="tel" />
          </label>
          <label>
            Количество
            <input
              type="number"
              min={1}
              value={quantity}
              onChange={(e) => setQuantity(Number(e.target.value))}
              required
            />
          </label>
          {error && <p className="modal-error">{error}</p>}
          <div className="modal-actions">
            <button type="button" className="btn btn-outline" onClick={onClose}>
              Отмена
            </button>
            <button type="submit" className="btn btn-primary" disabled={loading}>
              {loading ? 'Отправка…' : 'Заказать'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
