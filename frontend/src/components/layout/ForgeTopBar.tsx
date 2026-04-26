import React from 'react';
import { useAuthContext } from '../../providers/GameDataProvider';
import ResourceStat from '../ui/ResourceStat';

const ForgeTopBar: React.FC = () => {
  const { user, profile, isAuthenticated, authMode, getLinkAccountUrl } = useAuthContext();
  const displayName = user?.username || user?.email || 'Unknown User';
  const forgeName = String(profile?.forge_name ?? "Blacksmith's Forge");

  return (
    <header className="forge-topbar">
      <div className="forge-brand">
        <div className="forge-brand__mark">⚒️</div>
        <div>
          <h1>Blacksmith's Forge</h1>
          <p>Build. Craft. Prosper.</p>
        </div>
      </div>

      <div className="forge-topbar__right">
        <div className="forge-stats">
          <ResourceStat label="Gold" value={Number(profile?.coins ?? 0)} icon="🪙" />
          <ResourceStat label="Reputation" value={Number(profile?.reputation ?? 0)} icon="🛡️" />
          <ResourceStat label="Level" value={Number(profile?.level ?? 1)} icon="⌃" />
        </div>

        <div className="forge-user">
          <div className="forge-user__avatar">🧑‍🏭</div>
          <div>
            <div className={`forge-user__badge ${isAuthenticated ? 'is-active' : ''}`}>
              {isAuthenticated ? (authMode === 'guest' ? 'Guest Session' : 'Authenticated') : 'Not Logged In'}
            </div>
            <div className="forge-user__name">{displayName}</div>
            <div className="forge-user__forge">{forgeName}</div>
            {user?.is_guest ? (
              <a className="forge-user__link" href={getLinkAccountUrl()}>
                Link Account
              </a>
            ) : null}
          </div>
        </div>
      </div>
    </header>
  );
};

export default ForgeTopBar;

