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
    setPurchasingId(upgradeId);
    try {
      const result = await upgradesAPI.purchase(upgradeId);
      if (!result.success) {
        setPurchaseError(result.message || 'Unable to purchase upgrade.');
      } else {
        setOwnedUpgradeIds(prev => (prev.includes(upgradeId) ? prev : [...prev, upgradeId]));
        await refreshSession();
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
        <h2>🛠️ Forge Upgrades</h2>
        <p className="text-muted">
          Upgrades are listed from the backend. Purchases are validated server-side.
        </p>
        {purchaseError && <div className="error">{purchaseError}</div>}
        <div className="upgrades-grid">
          {upgrades.map((upgrade, idx) => {
            const isOwned = upgrade.id ? ownedUpgradeIds.includes(upgrade.id) : false;
            return (
              <div key={`${upgrade.name}-${idx}`} className="upgrade-card">
                <div className="upgrade-header">
                  <span className="upgrade-icon">{upgrade.icon}</span>
                  <div className="upgrade-name">{upgrade.name}</div>
                </div>
                <div className="upgrade-effect">{upgrade.effect}</div>
                <div className="upgrade-cost">{upgrade.cost}g</div>
                <button
                  className="btn btn--secondary btn--sm"
                  disabled={
                    !isAuthenticated ||
                    !upgrade.id ||
                    isOwned ||
                    Number(profile?.coins ?? 0) < upgrade.cost ||
                    (upgrade.unlockLevel
                      ? Number(profile?.level ?? 1) < upgrade.unlockLevel
                      : false) ||
                    purchasingId === upgrade.id
                  }
                  onClick={() => handlePurchase(upgrade.id)}
                >
                  {isOwned ? 'Owned' : purchasingId === upgrade.id ? 'Purchasing...' : 'Purchase'}
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
