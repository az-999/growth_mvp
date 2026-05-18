import { useState } from 'react';
import { Link } from 'react-router-dom';
import { bouquets } from '../data/bouquets';
import OrderModal from '../components/OrderModal';
import './LandingPage.css';

export default function LandingPage() {
  const [selectedId, setSelectedId] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);
  const selected = bouquets.find((b) => b.id === selectedId);

  const shops = [
    { id: 1, name: 'Акация' },
    { id: 2, name: 'Шик блеск красота' },
  ];

  return (
    <div className="landing">
      <header className="landing-header container">
        <h1>Доставка цветов</h1>
        <Link to="/login" className="btn btn-outline">
          Войти
        </Link>
      </header>

      {success && (
        <div className="container">
          <div className="alert alert-success">Заказ успешно сформирован</div>
        </div>
      )}

      <main className="container">
        {shops.map((shop) => (
          <section key={shop.id} className="shop-section">
            <h2>{shop.name}</h2>
            <div className="bouquet-grid">
              {bouquets
                .filter((b) => b.shopId === shop.id)
                .map((b) => (
                  <article key={b.id} className="bouquet-card" style={{ background: b.color }}>
                    <div className="bouquet-visual" />
                    <h3>{b.name}</h3>
                    <p className="bouquet-price">{b.price.toLocaleString('ru-RU')} ₽</p>
                    <button type="button" className="btn btn-primary" onClick={() => setSelectedId(b.id)}>
                      Заказать
                    </button>
                  </article>
                ))}
            </div>
          </section>
        ))}
      </main>

      {selected && (
        <OrderModal bouquet={selected} onClose={() => setSelectedId(null)} onSuccess={() => setSuccess(true)} />
      )}
    </div>
  );
}
