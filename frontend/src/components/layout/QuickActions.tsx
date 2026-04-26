import React from 'react';

interface QuickActionsProps {
  onAchievements: () => void;
  onSave: () => void;
}

const QuickActions: React.FC<QuickActionsProps> = ({ onAchievements, onSave }) => (
  <div className="forge-quick-actions">
    <button onClick={onAchievements} title="Achievements (Ctrl+A)">
      🏆
    </button>
    <button onClick={onSave} title="Save Management (Ctrl+S)">
      💾
    </button>
  </div>
);

export default QuickActions;

