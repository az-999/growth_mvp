import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  server: {
    host: '0.0.0.0',
    port: 5173,
    // Vite 5.4+ blocks unknown Host headers (reverse proxy / public domain)
    allowedHosts: ['localhost', '.vedicai.ru'],
  },
});
