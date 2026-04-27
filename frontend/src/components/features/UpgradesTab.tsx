import React, { useState, useEffect } from 'react';
import { useGameDataContext, useAuthContext } from '../../providers/GameDataProvider';
import { upgradesAPI } from '../../api/api';

interface UpgradesTabProps {
  active: boolean;
}

const UpgradesTab: React.FC<UpgradesTabProps> = ({ active }) => {
  const { upgrades } = useGameDataContext();
  const { profile, isAuthenticated, refreshSession } = useAuthContext();
  const [purchasingId, setPurchasingId] = useState<number | null>(null);
  const [purchaseError, setPurchaseError] = useState<string | null>(null);
  const [purchaseMessage, setPurchaseMessage] = useState<string | null>(null);
  const [ownedUpgradeIds, setOwnedUpgradeIds] = useState<number[]>([]);

  useEffect(() => {
    let isActive = true;
    const loadOwnedUpgrades = async () => {
      if (!active || !isAuthenticated) {
        setOwnedUpgradeIds([]);
        return;
      }
      const ids = await upgradesAPI.getPurchased();
      if (isActive) {
        setOwnedUpgradeIds(ids);
      }
    };

    loadOwnedUpgrades();
    return () => {
      isActive = false;
    };
  }, [active, isAuthenticated]);

  const handlePurchase = async (upgradeId?: number) => {
    if (!upgradeId) {
      setPurchaseError('Upgrade is missing an ID.');
      return;
    }
    if (!isAuthenticated) {
      setPurchaseError('Please log in to purchase upgrades.');
      return;
    }

    setPurchaseError(null);
    setPurchaseMessage(null);
    setPurchasingId(upgradeId);
    try {
      const result = await upgradesAPI.purchase(upgradeId);
      if (!result.success) {
        setPurchaseError(result.message || 'Unable to purchase upgrade.');
      } else {
        setOwnedUpgradeIds(prev => (prev.includes(upgradeId) ? prev : [...prev, upgradeId]));
        setPurchaseMessage(result.message || 'Upgrade installed.');
        await refreshSession();
        window.setTimeout(() => setPurchaseMessage(null), 2400);
      }
    } catch (error) {
      setPurchaseError(error instanceof Error ? error.message : 'Unable to purchase upgrade.');
    } finally {
      setPurchasingId(null);
    }
  };

  if (!active) return null;

  return (
    <section id="upgrades-tab" className="tab-content active">
      <div className="upgrades-container">
        <div className="detail-heading">
          <span>🛠️</span>
          <div>
            <h2>Forge Upgrades</h2>
            <p>Install permanent workshop improvements and unlock stronger milestones.</p>
          </div>
        </div>
        {purchaseError && <div className="error">{purchaseError}</div>}
        {purchaseMessage && <div className="status status--success upgrade-purchase-message">{purchaseMessage}</div>}
        <div className="upgrades-grid">
          {upgrades.map((upgrade, idx) => {
            const isOwned = upgrade.id ? ownedUpgradeIds.includes(upgrade.id) : false;
            const isLocked = upgrade.unlockLevel
              ? Number(profile?.level ?? 1) < upgrade.unlockLevel
              : false;
            const canAfford = Number(profile?.coins ?? 0) >= upgrade.cost;
            return (
              <div
                key={`${upgrade.name}-${idx}`}
                className={`upgrade-card upgrade-card--milestone${isOwned ? ' is-owned' : ''}${isLocked ? ' is-locked' : ''}`}
              >
                <div className="upgrade-header">
                  <span className="upgrade-icon">{upgrade.icon}</span>
                  <div>
                    <div className="upgrade-name">{upgrade.name}</div>
                    <div className="upgrade-state">
                      {isOwned ? 'Installed' : isLocked ? `Unlocks at level ${upgrade.unlockLevel}` : 'Available'}
                    </div>
                  </div>
                </div>
                <div className="upgrade-effect">{upgrade.effect}</div>
                <div className="upgrade-tier-track" aria-label={isOwned ? 'Upgrade installed' : 'Upgrade not installed'}>
                  <span className={isOwned ? 'is-filled' : ''} />
                  <span className={isOwned ? 'is-filled' : ''} />
                  <span />
                </div>
                <div className="upgrade-cost">{upgrade.cost}g</div>
                <button
                  className="btn btn--secondary btn--sm"
                  disabled={
                    !isAuthenticated ||
                    !upgrade.id ||
                    isOwned ||
                    !canAfford ||
                    isLocked ||
                    purchasingId === upgrade.id
                  }
                  onClick={() => handlePurchase(upgrade.id)}
                >
                  {isOwned
                    ? 'Installed'
                    : purchasingId === upgrade.id
                      ? 'Installing...'
                      : canAfford
                        ? 'Install Upgrade'
                        : 'Need More Gold'}
                </button>
              </div>
            );
          })}
          {upgrades.length === 0 && <div className="no-upgrades">No upgrades available.</div>}
        </div>
      </div>
    </section>
  );
};

export default UpgradesTab;
