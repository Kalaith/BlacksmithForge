/**
 * Main Application Component
 * Clean, modular architecture following frontend standards
 */

import './styles/globals.css';
import './styles/style.css';
import './styles/forge-theme.css';
import React, { useState, useEffect } from 'react';
import GameLayout from './components/layout/GameLayout';
import GameHeader from './components/layout/GameHeader';
import GameNav from './components/layout/GameNav';
import ForgePage from './pages/ForgePage';
import RecipesPage from './pages/RecipesPage';
import MaterialsPage from './pages/MaterialsPage';
import CustomersPage from './pages/CustomersPage';
import UpgradesPage from './pages/UpgradesPage';
import ExportImportModal from './components/ui/ExportImportModal';
import AchievementsPanel from './components/ui/AchievementsPanel';
import { useAuthContext, useGameDataContext } from './providers/GameDataProvider';
import QuickActions from './components/layout/QuickActions';

type TabKey = 'forge' | 'recipes' | 'materials' | 'customers' | 'upgrades';

const App: React.FC = () => {
  const { user, loading, error, continueAsGuest, getLinkAccountUrl } = useAuthContext();
  const gameData = useGameDataContext();
  const [activeTab, setActiveTab] = useState<TabKey>('forge');
  const [showExportImport, setShowExportImport] = useState(false);
  const [showAchievements, setShowAchievements] = useState(false);

  // Keyboard shortcuts
  useEffect(() => {
    const handleKeyPress = (event: KeyboardEvent) => {
      if (event.ctrlKey || event.metaKey) {
        switch (event.key) {
          case 's':
            event.preventDefault();
            setShowExportImport(true);
            break;
          case 'a':
            event.preventDefault();
            setShowAchievements(true);
            break;
        }
      }
    };

    window.addEventListener('keydown', handleKeyPress);
    return () => window.removeEventListener('keydown', handleKeyPress);
  }, []);

  if (loading) {
    return (
      <div className="game-container game-container--centered">
        <div className="panel-card">Loading forge session...</div>
      </div>
    );
  }

  if (!user) {
    const loginUrl = getLinkAccountUrl();
    return (
      <div className="game-container game-container--auth">
        <div className="panel-card auth-card">
          <h1>Blacksmith's Forge</h1>
          <p>Start forging as a guest, or sign in with WebHatchery to keep your forge tied to your account.</p>
          {error ? <p className="auth-error">{error}</p> : null}
          <div className="auth-actions">
            <button onClick={() => void continueAsGuest()} className="btn btn-primary">
              Continue as Guest
            </button>
            <a href={loginUrl} className="btn btn-secondary">
              Login with WebHatchery
            </a>
          </div>
        </div>
      </div>
    );
  }

  return (
    <GameLayout>
      <GameHeader />
      <GameNav activeTab={activeTab} onTabChange={tab => setActiveTab(tab as TabKey)} />

      <QuickActions
        onAchievements={() => setShowAchievements(true)}
        onSave={() => setShowExportImport(true)}
      />

      <main className="game-content">
        {gameData.error ? <div className="error">{gameData.error}</div> : null}
        {activeTab === 'forge' && <ForgePage onTabChange={tab => setActiveTab(tab as TabKey)} />}
        {activeTab === 'recipes' && <RecipesPage />}
        {activeTab === 'materials' && <MaterialsPage />}
        {activeTab === 'customers' && <CustomersPage />}
        {activeTab === 'upgrades' && <UpgradesPage />}
      </main>

      {/* Modals */}
      <ExportImportModal isOpen={showExportImport} onClose={() => setShowExportImport(false)} />
      <AchievementsPanel isOpen={showAchievements} onClose={() => setShowAchievements(false)} />
    </GameLayout>
  );
};

export default App;
