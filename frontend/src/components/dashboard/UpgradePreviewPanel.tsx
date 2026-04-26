import React from 'react';
import { uiAssets } from '../../data/ui-assets';
import { ForgeUpgrade } from '../../types/game.d';
import FramedButton from '../ui/FramedButton';
import OrnatePanel from '../ui/OrnatePanel';

interface UpgradePreviewPanelProps {
  upgrades: ForgeUpgrade[];
  onViewUpgrades: () => void;
}

const UpgradePreviewPanel: React.FC<UpgradePreviewPanelProps> = ({ upgrades, onViewUpgrades }) => (
  <OrnatePanel title="Upgrades" icon="⬆️" className="dashboard-panel dashboard-panel--summary">
    <div
      className="dashboard-asset"
      style={{ '--panel-art': `url("${uiAssets.upgrades}")` } as React.CSSProperties}
    />
    <p className="dashboard-copy">Improve your forge and unlock new abilities.</p>
    <div className="dashboard-ledger">
      {upgrades.slice(0, 3).map(upgrade => (
        <div key={upgrade.name}>
          <span>
            {upgrade.icon} {upgrade.name}
          </span>
          <strong>{upgrade.cost}g</strong>
        </div>
      ))}
    </div>
    <FramedButton icon="⌂" onClick={onViewUpgrades}>
      View Upgrades
    </FramedButton>
  </OrnatePanel>
);

export default UpgradePreviewPanel;

