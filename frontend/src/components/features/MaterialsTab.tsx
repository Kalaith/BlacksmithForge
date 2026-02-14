import React, { useEffect, useState } from 'react';
import { useGameDataContext, useAuthContext } from '../../providers/GameDataProvider';
import { materialsAPI } from '../../api/api';
import MaterialCard from './MaterialCard';

interface MaterialsTabProps {
  active: boolean;
}

const MaterialsTab: React.FC<MaterialsTabProps> = ({ active }) => {
  const { materials } = useGameDataContext();
  const { user, profile, isAuthenticated, refreshSession } = useAuthContext();
  const [userMaterials, setUserMaterials] = useState<Record<string, number>>({});
  const [purchaseMessage, setPurchaseMessage] = useState<string | null>(null);

  useEffect(() => {
    const loadUserMaterials = async () => {
      if (!isAuthenticated || !user?.id) return;
      const data = await materialsAPI.getUserMaterials(user.id);
      setUserMaterials(data);
    };
    loadUserMaterials();
  }, [isAuthenticated, user?.id]);

  const handlePurchase = async (materialId: number | undefined, quantity: number) => {
    if (!materialId || !isAuthenticated || !user?.id) return;
    const success = await materialsAPI.purchaseMaterial(materialId, quantity);
    if (success) {
      setPurchaseMessage(`Purchased ${quantity} items.`);
      const data = await materialsAPI.getUserMaterials(user.id);
      setUserMaterials(data);
      await refreshSession();
    } else {
      setPurchaseMessage('Purchase failed.');
    }
    setTimeout(() => setPurchaseMessage(null), 2000);
  };

  if (!active) return null;

  return (
    <section id="materials-tab" className="tab-content active">
      <div className="materials-container">
        <h2>⚒️ Material Market</h2>
        {purchaseMessage && (
          <div className="status status--info" style={{ marginBottom: 12 }}>
            {purchaseMessage}
          </div>
        )}
        <div className="materials-grid">
          {materials.map(material => (
            <MaterialCard
              key={material.name}
              material={material}
              owned={userMaterials[material.name] ?? 0}
              coins={Number(profile?.coins ?? 0)}
              onPurchase={handlePurchase}
            />
          ))}
        </div>
      </div>
    </section>
  );
};

export default MaterialsTab;
