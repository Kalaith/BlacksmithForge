import React from 'react';

interface CraftingMaterialsListProps {
  materials: Record<string, number>;
  userMaterials: Record<string, number>;
}

const CraftingMaterialsList: React.FC<CraftingMaterialsListProps> = ({
  materials,
  userMaterials,
}) => {
  const entries = Object.entries(materials);

  return (
    <div className="required-materials">
      <h5>Required Materials:</h5>
      {entries.map(([materialName, quantity]) => {
        const userQuantity = userMaterials?.[materialName] || 0;
        const lacking = userQuantity < quantity;

        return (
          <div
            key={materialName}
            className={`material-requirement${lacking ? ' insufficient' : ''}`}
          >
            {userQuantity} / {quantity} {materialName}
            {lacking && (
              <span style={{ color: 'var(--color-error)' }}>
                {' '}
                (Need {quantity - userQuantity} more)
              </span>
            )}
          </div>
        );
      })}
    </div>
  );
};

export default CraftingMaterialsList;
