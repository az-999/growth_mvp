import { Link, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { getAuth, logout } from '../api/client';
import './CabinetLayout.css';

export default function CabinetLayout() {
  const auth = getAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const shopId = auth?.shopId ?? 1;
  const telegramPath = `/shops/${shopId}/growth/telegram`;

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <div className="cabinet">
      <header className="cabinet-header container">
        <Link to="/">← На сайт</Link>
        <span>{auth?.email}</span>
        <button type="button" className="btn btn-outline" onClick={handleLogout}>
          Выйти
        </button>
      </header>
      <div className="cabinet-body container">
        <nav className="cabinet-nav">
          <Link to="/cabinet/orders" className={location.pathname.includes('orders') ? 'active' : ''}>
            Заказы
          </Link>
          <Link to={telegramPath} className={location.pathname.includes('telegram') ? 'active' : ''}>
            Настройки Telegram
          </Link>
        </nav>
        <main className="cabinet-main">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
