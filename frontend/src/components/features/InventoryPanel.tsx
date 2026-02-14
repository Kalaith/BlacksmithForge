import React from 'react';
import { useAuthContext } from '../../providers/GameDataProvider';
import { useInventory } from '../../hooks/useAPI';

interface InventoryItem {
  id?: string | number;
  name: string;
  icon: string;
  quality?: string;
  value: number;
  type: string;
}

const InventoryPanel: React.FC = () => {
  const { user } = useAuthContext();
  const { inventory, loading } = useInventory(user?.id);

  if (loading) {
    return (
      <div className="inventory-grid">
        <div>Loading inventory...</div>
      </div>
    );
  }

  if (!inventory || inventory.length === 0) {
    return (
      <div className="inventory-grid">
        <div>No items in inventory.</div>
      </div>
    );
  }

  return (
    <div className="inventory-grid">
      {inventory.map((item: InventoryItem, idx: number) => (
        <div key={item.id ?? idx} className="inventory-item">
          <div className="item-icon">{item.icon}</div>
          <div className="item-name">{item.name}</div>
          {item.quality && <div className="item-quantity">{item.quality} Quality</div>}
          <div className="item-value">{item.value}g</div>
        </div>
      ))}
    </div>
  );
};

export default InventoryPanel;
