import React from 'react';
import { Material } from '../../types/game';
import { MATERIAL_BUY_QUANTITIES } from '../../constants/gameConfig';

interface MaterialCardProps {
  material: Material;
  owned: number;
  coins: number;
  onPurchase: (materialId: number | undefined, quantity: number) => void;
  recentlyPurchased?: boolean;
}

const getPriceMood = (cost: number): 'cheap' | 'normal' | 'expensive' => {
  if (cost <= 10) return 'cheap';
  if (cost >= 50) return 'expensive';
  return 'normal';
};

const MaterialCard: React.FC<MaterialCardProps> = ({
  material,
  owned,
  coins,
  onPurchase,
  recentlyPurchased = false,
}) => (
  <div className={`material-card material-card--${getPriceMood(material.cost)}${recentlyPurchased ? ' just-bought' : ''}`}>
    <div className="material-info">
      <div className="material-name">
        {material.icon} {material.name}
      </div>
      <div className="material-cost">{material.cost}g</div>
    </div>
    <div className="material-description">{material.description}</div>
    <div className="material-market-state">
      {material.cost <= 10 ? 'Good buy' : material.cost >= 50 ? 'High demand' : 'Steady supply'}
    </div>
    <div className={`quality-badge quality-${material.quality}`}>{material.quality}</div>
    <div className="material-purchase-row">
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
