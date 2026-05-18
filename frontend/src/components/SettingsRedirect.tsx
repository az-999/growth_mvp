import { Navigate } from 'react-router-dom';
import { getAuth } from '../api/client';

export default function SettingsRedirect() {
  const shopId = getAuth()?.shopId ?? 1;
  return <Navigate to={`/shops/${shopId}/growth/telegram`} replace />;
}
