<?php

namespace App\Controller;

use App\Repository\ShopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ShopController extends AbstractController
{
    public function __construct(
        private readonly ShopRepository $shopRepository,
    ) {
    }

    #[Route('/shops', name: 'shops_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $items = [];
        foreach ($this->shopRepository->findBy([], ['id' => 'ASC']) as $shop) {
            $items[] = [
                'id' => $shop->getId(),
                'name' => $shop->getName(),
            ];
        }

        return $this->json($items);
    }
}
