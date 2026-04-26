import React from 'react';
import { uiAssets } from '../../data/ui-assets';
import { Material } from '../../types/game.d';
import FramedButton from '../ui/FramedButton';
import OrnatePanel from '../ui/OrnatePanel';

interface MarketPreviewPanelProps {
  materials: Material[];
  onBrowse: () => void;
}

const MarketPreviewPanel: React.FC<MarketPreviewPanelProps> = ({ materials, onBrowse }) => (
  <OrnatePanel title="Materials Market" icon="⚒️" className="dashboard-panel dashboard-panel--summary">
    <div
      className="dashboard-asset"
      style={{ '--panel-art': `url("${uiAssets.market}")` } as React.CSSProperties}
    />
    <p className="dashboard-copy">Buy raw materials to fuel your creations.</p>
    <div className="dashboard-ledger">
      {materials.slice(0, 5).map(material => (
        <div key={material.name}>
          <span>
            {material.icon} {material.name}
          </span>
          <strong>{material.cost}g</strong>
        </div>
      ))}
    </div>
    <FramedButton icon="⚖️" onClick={onBrowse}>
      Browse Market
    </FramedButton>
  </OrnatePanel>
);

export default MarketPreviewPanel;

