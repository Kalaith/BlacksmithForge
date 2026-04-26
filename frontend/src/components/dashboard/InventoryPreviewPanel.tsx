import React from 'react';
import { uiAssets } from '../../data/ui-assets';
import { InventoryItem } from '../../types/inventory';
import FramedButton from '../ui/FramedButton';
import OrnatePanel from '../ui/OrnatePanel';

interface InventoryPreviewPanelProps {
  inventory: InventoryItem[];
  loading: boolean;
}

const InventoryPreviewPanel: React.FC<InventoryPreviewPanelProps> = ({ inventory, loading }) => (
  <OrnatePanel title="Inventory" icon="🧰" className="dashboard-panel dashboard-panel--inventory">
    <div
      className="dashboard-asset dashboard-asset--inventory"
      style={{ '--panel-art': `url("${uiAssets.inventory}")` } as React.CSSProperties}
    />
    {loading ? (
      <p className="dashboard-copy">Checking the stockroom...</p>
    ) : inventory.length > 0 ? (
      <div className="dashboard-list dashboard-list--compact">
        {inventory.slice(0, 3).map((item, index) => (
          <div key={item.id ?? index} className="dashboard-row dashboard-row--compact">
            <span>{item.icon}</span>
            <span>{item.name}</span>
            <strong>{item.value}g</strong>
          </div>
        ))}
      </div>
    ) : (
      <p className="dashboard-copy">Your materials and crafted items will appear here.</p>
    )}
    <FramedButton icon="🧰" disabled>
      View Inventory
    </FramedButton>
  </OrnatePanel>
);

export default InventoryPreviewPanel;

