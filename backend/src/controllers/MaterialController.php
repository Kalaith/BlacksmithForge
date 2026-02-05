<?php

namespace App\Controllers;

use App\Services\MaterialService;
use App\Http\Response;
use App\Http\Request;

class MaterialController
{
    private MaterialService $materialService;

    public function __construct(MaterialService $materialService)
    {
        $this->materialService = $materialService;
    }

    public function getAll(Request $request, Response $response, array $args): Response
    {
        try {
            $materials = $this->materialService->getAllMaterials();
            
            $data = [
                'success' => true,
                'data' => array_map(fn($material) => $material->toArray(), $materials),
                'count' => count($materials)
            ];
            
            return $this->jsonResponse($response, $data);
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) ($args['id'] ?? 0);
            
            if ($id <= 0) {
                return $this->errorResponse($response, 'Invalid material ID', 400);
            }

            $material = $this->materialService->getMaterialById($id);
            
            if (!$material) {
                return $this->errorResponse($response, 'Material not found', 404);
            }
            
            $data = [
                'success' => true,
                'data' => $material->toArray()
            ];
            
            return $this->jsonResponse($response, $data);
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $request->getParsedBody();
            
            if (!$data || !is_array($data)) {
                return $this->errorResponse($response, 'Invalid request data', 400);
            }

            $material = $this->materialService->createMaterial($data);
            
            $responseData = [
                'success' => true,
                'message' => 'Material created successfully',
                'data' => $material->toArray()
            ];
            
            return $this->jsonResponse($response, $responseData, 201);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($response, $e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) ($args['id'] ?? 0);
            
            if ($id <= 0) {
                return $this->errorResponse($response, 'Invalid material ID', 400);
            }

            $data = $request->getParsedBody();
            
            if (!$data || !is_array($data)) {
                return $this->errorResponse($response, 'Invalid request data', 400);
            }

            $material = $this->materialService->updateMaterial($id, $data);
            
            if (!$material) {
                return $this->errorResponse($response, 'Material not found', 404);
            }
            
            $responseData = [
                'success' => true,
                'message' => 'Material updated successfully',
                'data' => $material->toArray()
            ];
            
            return $this->jsonResponse($response, $responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($response, $e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) ($args['id'] ?? 0);
            
            if ($id <= 0) {
                return $this->errorResponse($response, 'Invalid material ID', 400);
            }

            $success = $this->materialService->deleteMaterial($id);
            
            if (!$success) {
                return $this->errorResponse($response, 'Material not found', 404);
            }
            
            $data = [
                'success' => true,
                'message' => 'Material deleted successfully'
            ];
            
            return $this->jsonResponse($response, $data);
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    public function getByType(Request $request, Response $response, array $args): Response
    {
        try {
            $type = $args['type'] ?? '';
            
            if (empty($type)) {
                return $this->errorResponse($response, 'Material type is required', 400);
            }

            $materials = $this->materialService->getMaterialsByType($type);
            
            $data = [
                'success' => true,
                'data' => array_map(fn($material) => $material->toArray(), $materials),
                'count' => count($materials)
            ];
            
            return $this->jsonResponse($response, $data);
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    public function getByRarity(Request $request, Response $response, array $args): Response
    {
        try {
            $rarity = $args['rarity'] ?? '';
            
            if (empty($rarity)) {
                return $this->errorResponse($response, 'Material rarity is required', 400);
            }

            $materials = $this->materialService->getMaterialsByRarity($rarity);
            
            $data = [
                'success' => true,
                'data' => array_map(fn($material) => $material->toArray(), $materials),
                'count' => count($materials)
            ];
            
            return $this->jsonResponse($response, $data);
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    public function getUserMaterials(Request $request, Response $response, array $args): Response
    {
        try {
            $userId = $this->getAuthUserId($request);
            
            if (!$userId) {
                return $this->errorResponse($response, 'Invalid user ID', 400);
            }

            $materials = $this->materialService->getUserMaterials($userId);
            
            $data = [
                'success' => true,
                'data' => $materials
            ];
            
            return $this->jsonResponse($response, $data);
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    public function purchaseMaterial(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $request->getParsedBody();
            
            if (!$data || !is_array($data)) {
                return $this->errorResponse($response, 'Invalid request data', 400);
            }

            $userId = $this->getAuthUserId($request);
            $materialId = (int) ($data['material_id'] ?? 0);
            $quantity = (int) ($data['quantity'] ?? 0);
            
            if (!$userId || $materialId <= 0 || $quantity <= 0) {
                return $this->errorResponse($response, 'Invalid user ID, material ID, or quantity', 400);
            }

            $result = $this->materialService->purchaseMaterial($userId, $materialId, $quantity);
            
            if (!$result['success']) {
                return $this->errorResponse($response, $result['message'], 400);
            }
            
            $responseData = [
                'success' => true,
                'message' => "Successfully purchased {$quantity} materials",
                'data' => $result
            ];
            
            return $this->jsonResponse($response, $responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($response, $e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    private function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    private function errorResponse(Response $response, string $message, int $status = 400): Response
    {
        $data = [
            'success' => false,
            'message' => $message
        ];
        
        return $this->jsonResponse($response, $data, $status);
    }

    private function getAuthUserId(Request $request): ?int
    {
        $authUser = $request->getAttribute('auth_user');
        return isset($authUser['id']) ? (int) $authUser['id'] : null;
    }
}
