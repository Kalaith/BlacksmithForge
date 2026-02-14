import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App';
import { GameDataProvider } from './providers/GameDataProvider';

const root = ReactDOM.createRoot(document.getElementById('root') as HTMLElement);
root.render(
  <React.StrictMode>
    <GameDataProvider>
      <App />
    </GameDataProvider>
  </React.StrictMode>
);
