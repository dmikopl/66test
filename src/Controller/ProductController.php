<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/products')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly ProductRepository $productRepository
    ) {
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['sku']) || !isset($data['price']) || !isset($data['currency'])) {
            return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $product = $this->productService->create(
                $data['name'],
                $data['sku'],
                $data['price'],
                $data['currency']
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->serializeProduct($product), Response::HTTP_CREATED);
    }

    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 10)));
        $status = $request->query->get('status');

        $qb = $this->productRepository->createQueryBuilder('p')
            ->where('p.deletedAt IS NULL');

        if ($status) {
            $qb->andWhere('p.status = :status')
               ->setParameter('status', $status);
        }

        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        $products = $qb->getQuery()->getResult();

        $total = $qb->select('COUNT(p.id)')
                   ->getQuery()
                   ->getSingleScalarResult();

        return new JsonResponse([
            'data' => array_map([$this, 'serializeProduct'], $products),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int) $total,
                'pages' => ceil($total / $limit),
            ],
        ]);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $product = $this->productRepository->findActiveById($id);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializeProduct($product, true));
    }

    #[Route('/{id}', methods: ['PATCH'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $product = $this->productRepository->findActiveById($id);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $expectedVersion = $data['version'] ?? null;

        try {
            if (isset($data['name']) || isset($data['sku'])) {
                $this->productService->update(
                    $product,
                    $data['name'] ?? null,
                    $data['sku'] ?? null,
                    $expectedVersion
                );
            }

            if (isset($data['price']) && isset($data['currency'])) {
                $this->productService->changePrice($product, $data['price'], $data['currency'], $expectedVersion);
            }
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'modified by another user')) {
                return new JsonResponse([
                    'error' => 'Product has been modified by another user',
                    'currentVersion' => $product->getVersion(),
                ], Response::HTTP_CONFLICT);
            }
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->serializeProduct($product));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $product = $this->productRepository->findActiveById($id);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $this->productService->softDelete($product);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/restore', methods: ['POST'])]
    public function restore(string $id): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$product->isDeleted()) {
            return new JsonResponse(['error' => 'Product is not deleted'], Response::HTTP_BAD_REQUEST);
        }

        $this->productService->restore($product);

        return new JsonResponse($this->serializeProduct($product));
    }

    private function serializeProduct(Product $product, bool $includeHistory = false): array
    {
        $data = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'price' => $product->getPrice(),
            'currency' => $product->getCurrency(),
            'status' => $product->getStatus(),
            'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $product->getUpdatedAt()->format('Y-m-d H:i:s'),
            'version' => $product->getVersion(),
        ];

        if ($includeHistory) {
            $data['priceHistory'] = array_map(function ($history) {
                return [
                    'oldPrice' => $history->getOldPrice(),
                    'newPrice' => $history->getNewPrice(),
                    'currency' => $history->getCurrency(),
                    'changedAt' => $history->getChangedAt()->format('Y-m-d H:i:s'),
                ];
            }, $product->getPriceHistories()->toArray());
        }

        return $data;
    }
}
