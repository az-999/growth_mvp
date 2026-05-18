import { Navigate, Route, Routes } from 'react-router-dom';
import LandingPage from './pages/LandingPage';
import LoginPage from './pages/LoginPage';
import OrdersPage from './pages/OrdersPage';
import TelegramSettingsPage from './pages/TelegramSettingsPage';
import CabinetLayout from './components/CabinetLayout';
import SettingsRedirect from './components/SettingsRedirect';
import { getAuth } from './api/client';

function PrivateRoute({ children }: { children: React.ReactNode }) {
  return getAuth() ? <>{children}</> : <Navigate to="/login" replace />;
}

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<LandingPage />} />
      <Route path="/login" element={<LoginPage />} />
      <Route
        path="/cabinet"
        element={
          <PrivateRoute>
            <CabinetLayout />
          </PrivateRoute>
        }
      >
        <Route path="orders" element={<OrdersPage />} />
        <Route path="settings" element={<SettingsRedirect />} />
      </Route>
      <Route
        path="/shops/:shopId/growth/telegram"
        element={
          <PrivateRoute>
            <CabinetLayout />
          </PrivateRoute>
        }
      >
        <Route index element={<TelegramSettingsPage />} />
      </Route>
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
