/**
 * Main Application Component
 * Clean, modular architecture following frontend standards
 */

import './styles/globals.css';
import './styles/style.css';
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

type TabKey = 'forge' | 'recipes' | 'materials' | 'customers' | 'upgrades';

const App: React.FC = () => {
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

  return (
    <GameLayout>
      <GameHeader />
      <GameNav activeTab={activeTab} onTabChange={tab => setActiveTab(tab as TabKey)} />

      {/* Quick Action Buttons */}
      <div className="fixed top-4 right-4 flex gap-2 z-40">
        <button
          onClick={() => setShowAchievements(true)}
          className="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded-full shadow-lg transition-colors"
          title="Achievements (Ctrl+A)"
        >
          🏆
        </button>
        <button
          onClick={() => setShowExportImport(true)}
          className="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-full shadow-lg transition-colors"
          title="Save Management (Ctrl+S)"
        >
          💾
        </button>
      </div>

      <main className="game-content">
        {activeTab === 'forge' && <ForgePage />}
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
