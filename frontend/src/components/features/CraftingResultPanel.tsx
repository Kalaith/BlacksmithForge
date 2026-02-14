import React from 'react';
import { CraftingResult } from '../../types';

interface CraftingResultPanelProps {
  result: CraftingResult | null;
  onCraftAnother: () => void;
}

const CraftingResultPanel: React.FC<CraftingResultPanelProps> = ({ result, onCraftAnother }) => (
  <div className="crafting-result">
    <div className="status status--success">{result?.quality} Quality Crafted!</div>
    <div className="result-details">
      <div className="crafted-item">
        {result?.item.icon} {result?.item.name}
      </div>
      <div className="item-quality">Quality: {result?.quality}</div>
      <div className="item-value">Value: {result?.item.value}g</div>
      {result?.item.stats && (
        <div className="item-stats">
          {Object.entries(result.item.stats).map(
            ([stat, value]) =>
              value !== undefined && (
                <div key={stat} className="item-stat">
                  {stat.charAt(0).toUpperCase() + stat.slice(1)}: {value}
                </div>
              )
          )}
        </div>
      )}
      {result?.message && <div className="result-message">{result.message}</div>}
    </div>
    <button className="btn btn--primary" onClick={onCraftAnother}>
      Craft Another
    </button>
  </div>
);

export default CraftingResultPanel;
