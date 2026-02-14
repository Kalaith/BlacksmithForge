import React from 'react';

interface MissingMaterialsWarningProps {
  materials: Record<string, number>;
  userMaterials: Record<string, number>;
}

const MissingMaterialsWarning: React.FC<MissingMaterialsWarningProps> = ({
  materials,
  userMaterials,
}) => {
  const missingEntries = Object.entries(materials)
    .map(([materialName, quantity]) => {
      const userQuantity = userMaterials?.[materialName] || 0;
      const missing = quantity - userQuantity;
      return missing > 0 ? { materialName, missing } : null;
    })
    .filter(Boolean) as Array<{ materialName: string; missing: number }>;

  if (missingEntries.length === 0) return null;

  return (
    <div className="missing-materials-warning">
      <strong>Missing Materials:</strong>
      {missingEntries.map(missing => (
        <div key={missing.materialName} className="missing-material">
          {missing.materialName}: Need {missing.missing} more
        </div>
      ))}
    </div>
  );
};

export default MissingMaterialsWarning;
