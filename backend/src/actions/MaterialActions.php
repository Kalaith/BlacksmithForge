<?php
namespace App\Actions;

use App\Services\MaterialService;
use App\Services\AuthService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class MaterialActions {
    private MaterialService $materialService;
    private AuthService $authService;

    public function __construct(ContainerInterface $container) {
        $this->materialService = $container->get(MaterialService::class);
        $this->authService = $container->get(AuthService::class);
    }

    /**
     * Get all available materials
     */
    public function getAll(Request $request) {
        try {
            $materials = $this->materialService->getAllMaterials();
            
            return [
                'success' => true,
                'data' => $materials,
                'count' => count($materials)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch materials: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get material by ID
     */
    public function get(Request $request) {
        try {
            $id = $request->getAttribute('id');
            $material = $this->materialService->getMaterialById($id);
            
            if (!$material) {
                return [
                    'success' => false,
                    'message' => 'Material not found'
                ];
            }
            
            return [
                'success' => true,
                'data' => $material
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch material: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get user's material quantities
     */
    public function getUserMaterials(Request $request) {
        try {
            $userId = $request->getAttribute('userId');
            
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'User ID is required'
                ];
            }

            $materials = $this->materialService->getUserMaterials($userId);
            
            return [
                'success' => true,
                'data' => $materials
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch user materials: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Purchase materials for user
     */
    public function purchaseMaterial(Request $request) {
        try {
            $data = json_decode($request->getBody(), true);
            
            $userId = $data['user_id'] ?? null;
            $materialId = $data['material_id'] ?? null;
            $quantity = $data['quantity'] ?? null;
            
            if (!$userId || !$materialId || !$quantity) {
                return [
                    'success' => false,
                    'message' => 'User ID, material ID, and quantity are required'
                ];
            }

            if ($quantity <= 0) {
                return [
                    'success' => false,
                    'message' => 'Quantity must be positive'
                ];
            }

            $result = $this->materialService->purchaseMaterial($userId, $materialId, $quantity);
            
            if (!$result['success']) {
                return $result;
            }
            
            return [
                'success' => true,
                'data' => $result,
                'message' => "Successfully purchased {$quantity} materials"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to purchase material: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create new material (admin only)
     */
    public function create(Request $request) {
        try {
            $data = json_decode($request->getBody(), true);
            
            $material = $this->materialService->createMaterial($data);
            
            return [
                'success' => true,
                'data' => $material,
                'message' => 'Material created successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create material: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update material (admin only)
     */
    public function update(Request $request) {
        try {
            $id = $request->getAttribute('id');
            $data = json_decode($request->getBody(), true);
            
            $material = $this->materialService->updateMaterial($id, $data);
            
            if (!$material) {
                return [
                    'success' => false,
                    'message' => 'Material not found'
                ];
            }
            
            return [
                'success' => true,
                'data' => $material,
                'message' => 'Material updated successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update material: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete material (admin only)
     */
    public function delete(Request $request) {
        try {
            $id = $request->getAttribute('id');
            
            $success = $this->materialService->deleteMaterial($id);
            
            if (!$success) {
                return [
                    'success' => false,
                    'message' => 'Material not found'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Material deleted successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete material: ' . $e->getMessage()
            ];
        }
    }
}
