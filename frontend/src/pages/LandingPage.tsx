import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { bouquets } from '../data/bouquets';
import OrderModal from '../components/OrderModal';
import { fetchShops, type ShopInfo } from '../api/client';
import './LandingPage.css';

export default function LandingPage() {
  const [shops, setShops] = useState<ShopInfo[]>([]);
  const [loadError, setLoadError] = useState('');
  const [selectedId, setSelectedId] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);

  const selected = bouquets.find((b) => b.id === selectedId);
  const selectedShopId = selected ? shops.find((s) => s.name === selected.shopName)?.id : undefined;

  useEffect(() => {
    fetchShops()
      .then(setShops)
      .catch((e) => setLoadError(e.message));
  }, []);

  return (
    <div className="landing">
      <header className="landing-header container">
        <h1>Доставка цветов</h1>
        <Link to="/login" className="btn btn-outline">
          Войти
        </Link>
      </header>

      {loadError && (
        <div className="container">
          <div className="alert alert-error">{loadError}</div>
        </div>
      )}

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
                .filter((b) => b.shopName === shop.name)
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

      {selected && selectedShopId && (
        <OrderModal
          bouquet={selected}
          shopId={selectedShopId}
          onClose={() => setSelectedId(null)}
          onSuccess={() => setSuccess(true)}
        />
      )}
    </div>
  );
}
