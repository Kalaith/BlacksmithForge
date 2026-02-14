import React from 'react';
import { Material } from '../../types/game';
import { MATERIAL_BUY_QUANTITIES } from '../../constants/gameConfig';

interface MaterialCardProps {
  material: Material;
  owned: number;
  coins: number;
  onPurchase: (materialId: number | undefined, quantity: number) => void;
}

const MaterialCard: React.FC<MaterialCardProps> = ({ material, owned, coins, onPurchase }) => (
  <div className="material-card">
    <div className="material-info">
      <div className="material-name">
        {material.icon} {material.name}
      </div>
      <div className="material-cost">{material.cost}g</div>
    </div>
    <div className="material-description">{material.description}</div>
    <div className={`quality-badge quality-${material.quality}`}>{material.quality}</div>
    <div
      style={{
        marginTop: '12px',
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
      }}
    >
      <span>Owned: {owned}</span>
      <div>
        {MATERIAL_BUY_QUANTITIES.map(qty => (
          <button
            key={qty}
            className={`btn btn--sm ${qty === 1 ? 'btn--secondary' : 'btn--primary'}`}
            onClick={() => onPurchase(material.id, qty)}
            disabled={coins < material.cost * qty}
          >
            Buy {qty}
          </button>
        ))}
      </div>
    </div>
  </div>
);

export default MaterialCard;
