import { useState, useCallback } from 'react';
import { CraftingResult } from '../types';

/**
 * Custom hook for managing crafting result display
 * This hook handles only UI state for showing crafting results
 * Business logic is handled by the crafting workflow hook
 */
export function useCraftingResult() {
  const [result, setResult] = useState<CraftingResult | null>(null);
  const [showResult, setShowResult] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  /**
   * Display a crafting result from the crafting workflow
   * No business logic - just display the result
   */
  const displayResult = useCallback((craftingResult: CraftingResult) => {
    setResult(craftingResult);
    setShowResult(true);
    setError(null);
  }, []);

  /**
   * Hide the result modal/display
   */
  const hideResult = useCallback(() => {
    setShowResult(false);
    setResult(null);
    setError(null);
  }, []);

  /**
   * Reset all result state
   */
  const resetResult = useCallback(() => {
    setResult(null);
    setShowResult(false);
    setLoading(false);
    setError(null);
  }, []);

  /**
   * Handle any errors that occur during result processing
   */
  const handleError = useCallback((errorMessage: string) => {
    setError(errorMessage);
    setLoading(false);
    setShowResult(false);
  }, []);

  return {
    // Result data
    result,
    showResult,
    loading,
    error,

    // Actions (UI only)
    displayResult,
    hideResult,
    resetResult,
    handleError,

    // Helper computed values
    hasResult: result !== null,
    resultQuality: result?.quality || null,
    resultItem: result?.item || null,
    resultMessage: result?.message || null,
  };
}
