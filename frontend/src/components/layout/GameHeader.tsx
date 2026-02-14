import React from 'react';
import { useAuthContext } from '../../providers/GameDataProvider';

const GameHeader: React.FC = () => {
  const { user, profile, isAuthenticated } = useAuthContext();

  const displayName = user?.username || user?.email || 'Unknown User';
  const forgeName = String(profile?.forge_name ?? 'Forge');
  const gold = Number(profile?.coins ?? 0);
  const reputation = Number(profile?.reputation ?? 0);
  const level = Number(profile?.level ?? 1);

  return (
    <header className="game-header">
      <h1>🔨 Blacksmith's Forge</h1>
      <div className="header-right">
        <div className="player-stats">
          <div className="stat">
            <span className="stat-label">Gold:</span>
            <span className="stat-value">{gold}</span>
          </div>
          <div className="stat">
            <span className="stat-label">Reputation:</span>
            <span className="stat-value">{reputation}</span>
          </div>
          <div className="stat">
            <span className="stat-label">Level:</span>
            <span className="stat-value">{level}</span>
          </div>
        </div>
        <div className="user-status">
          <span className={`user-badge ${isAuthenticated ? 'user-badge--ok' : 'user-badge--warn'}`}>
            {isAuthenticated ? 'Authenticated' : 'Not Logged In'}
          </span>
          <span className="user-name">{displayName}</span>
          <span className="user-forge">{forgeName}</span>
        </div>
      </div>
    </header>
  );
};

export default GameHeader;
