import { useEffect, useState } from 'react';
import { getAuth, fetchOrders } from '../api/client';

interface Order {
  id: number;
  number: string;
  total: number;
  customerName: string;
  count: number;
  productId: string;
  createdAt: string;
}

export default function OrdersPage() {
  const [orders, setOrders] = useState<Order[]>([]);
  const [error, setError] = useState('');

  useEffect(() => {
    const shopId = getAuth()?.shopId;
    if (!shopId) return;
    fetchOrders(shopId)
      .then(setOrders)
      .catch((e) => setError(e.message));
  }, []);

  if (error) return <p className="alert alert-error">{error}</p>;

  return (
    <div>
      <h1>Заказы</h1>
      <table className="orders-table">
        <thead>
          <tr>
            <th>№</th>
            <th>Номер</th>
            <th>Товар</th>
            <th>Кол-во</th>
            <th>Сумма</th>
            <th>Клиент</th>
            <th>Дата</th>
          </tr>
        </thead>
        <tbody>
          {orders.map((o) => (
            <tr key={o.id}>
              <td>{o.id}</td>
              <td>{o.number}</td>
              <td>{o.productId}</td>
              <td>{o.count}</td>
              <td>{o.total.toLocaleString('ru-RU')} ₽</td>
              <td>{o.customerName}</td>
              <td>{new Date(o.createdAt).toLocaleString('ru-RU')}</td>
            </tr>
          ))}
        </tbody>
      </table>
      {orders.length === 0 && <p>Заказов пока нет</p>}
    </div>
  );
}
