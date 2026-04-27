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
  <OrnatePanel
    title="Inventory"
    icon="🧰"
    className="dashboard-panel dashboard-panel--inventory"
    variant="tertiary"
  >
    <div
      className="dashboard-asset dashboard-asset--inventory"
      style={{ '--panel-art': `url("${uiAssets.inventory}")` } as React.CSSProperties}
    />
    {loading ? (
      <p className="dashboard-copy">Checking the stockroom...</p>
    ) : inventory.length > 0 ? (
      <div className="inventory-chest" aria-label="Inventory chest">
        {inventory.slice(0, 6).map((item, index) => (
          <button
            key={item.id ?? index}
            className={`inventory-chest__slot inventory-chest__slot--${item.type}`}
            title={`${item.name}: ${item.value}g`}
            type="button"
            disabled
          >
            <span>{item.icon}</span>
            <strong>{item.value}g</strong>
          </button>
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
