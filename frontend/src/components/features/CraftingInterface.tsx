import React, { useEffect } from 'react';
import { useCrafting } from '../../hooks/useCrafting';
import { useCraftingResult } from '../../hooks/useCraftingResult';
import CraftingMaterialsList from './CraftingMaterialsList';
import MissingMaterialsWarning from './MissingMaterialsWarning';
import CraftingProgress from './CraftingProgress';
import CraftingResultPanel from './CraftingResultPanel';

interface CraftingInterfaceProps {
  selectedRecipe: string | null;
  canCraft?: boolean;
}

const CraftingInterface: React.FC<CraftingInterfaceProps> = ({ selectedRecipe }) => {
  const {
    recipe,
    validation,
    canCraft,
    hammerClicks,
    hammerAccuracy,
    craftingStarted,
    result,
    loading,
    error,
    handleStartCrafting,
    handleHammer,
    resetCrafting,
    isComplete,
    maxHammerClicks,
    progressPercentage,
  } = useCrafting(selectedRecipe);

  const {
    result: craftingResult,
    showResult,
    displayResult,
    hideResult,
    resetResult,
  } = useCraftingResult();

  // Handle crafting completion
  useEffect(() => {
    if (result && !showResult) {
      displayResult(result);
    }
  }, [result, showResult, displayResult]);

  // Reset result when starting new crafting
  useEffect(() => {
    if (craftingStarted && showResult) {
      hideResult();
      resetResult();
    }
  }, [craftingStarted, showResult, hideResult, resetResult]);

  if (!recipe) {
    return (
      <div className="crafting-interface">
        <div className="message">Select a recipe to start crafting</div>
      </div>
    );
  }

  if (loading) {
    return (
      <div className="crafting-interface">
        <div className="loading">Loading crafting data...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="crafting-interface">
        <div className="error">
          Error: {error}
          <button onClick={resetCrafting} className="btn btn--secondary">
            Try Again
          </button>
        </div>
      </div>
    );
  }

  const handleNewCraft = () => {
    resetCrafting();
    resetResult();
    hideResult();
  };

  return (
    <div className="crafting-interface">
      <div className="recipe-details">
        <h4>
          {recipe.icon} {recipe.name}
        </h4>

        <CraftingMaterialsList
          materials={recipe.materials}
          userMaterials={validation?.user_materials ?? {}}
        />

        <MissingMaterialsWarning
          materials={recipe.materials}
          userMaterials={validation?.user_materials ?? {}}
        />
      </div>

      {/* Crafting Actions */}
      {!craftingStarted && !showResult ? (
        <button className="btn btn--primary" onClick={handleStartCrafting} disabled={!canCraft}>
          {canCraft ? 'Start Crafting' : 'Insufficient Materials'}
        </button>
      ) : craftingStarted && !isComplete ? (
        <CraftingProgress
          hammerClicks={hammerClicks}
          hammerAccuracy={hammerAccuracy}
          progressPercentage={progressPercentage}
          craftingStarted={craftingStarted}
          onHammer={handleHammer}
          maxClicks={maxHammerClicks}
        />
      ) : showResult ? (
        <CraftingResultPanel result={craftingResult} onCraftAnother={handleNewCraft} />
      ) : null}
    </div>
  );
};

export default CraftingInterface;
