export interface Bouquet {
  id: string;
  name: string;
  price: number;
  shopId: number;
  shopName: string;
  color: string;
}

export const bouquets: Bouquet[] = [
  { id: 'a1', name: 'Нежность', price: 2490, shopId: 1, shopName: 'Акация', color: '#f8e1e4' },
  { id: 'a2', name: 'Весенний сад', price: 3190, shopId: 1, shopName: 'Акация', color: '#e8f5e9' },
  { id: 'a3', name: 'Классика', price: 1890, shopId: 1, shopName: 'Акация', color: '#fff3e0' },
  { id: 's1', name: 'Гламур', price: 4590, shopId: 2, shopName: 'Шик блеск красота', color: '#f3e5f5' },
  { id: 's2', name: 'Император', price: 5290, shopId: 2, shopName: 'Шик блеск красота', color: '#e3f2fd' },
  { id: 's3', name: 'Сияние', price: 2790, shopId: 2, shopName: 'Шик блеск красота', color: '#fce4ec' },
];
