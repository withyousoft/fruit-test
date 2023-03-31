<?php

namespace App\Controller;

use App\Entity\Fruit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FruitController extends AbstractController
{
    #[Route('/fruit', name: 'app_fruit')]
    public function index(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $limit = $request->get('limit');
        $offset = $request->get('offset');
        $nameQuery = $request->get('name');
        $familyQuery = $request->get('family');
        
        $fruitRepository = $entityManager->getRepository(Fruit::class);
        List($total, $fruits) = $fruitRepository->findByNameAndFamily($nameQuery, $familyQuery, $limit, $offset);
        
        return $this->json([
            'data' => $fruits,
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'name' => $nameQuery,
            'family' => $familyQuery,
            'total' => $total,
            'success' => true,
        ]);
    }

    #[Route('/fruit/favorites', name: 'fruit_favorites', methods: ['GET'])]
    public function favorites(EntityManagerInterface $entityManager): JsonResponse
    {
        $fruitRepository = $entityManager->getRepository(Fruit::class);
        $favoriteFruits = $fruitRepository->findBy(['isFavorite' => true]);
        
        return $this->json([
            'data' => $favoriteFruits,
            'success' => true,
        ]);
    }

    #[Route('/fruit/add-favorite/{id}', name: 'add_favorite', methods: ['PUT'])]
    public function addFavorite(int $id, EntityManagerInterface $entityManager) {
        $fruitRepository = $entityManager->getRepository(Fruit::class);

        $fruit = $fruitRepository->find($id);
        if (!$fruit) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $favoriteFruitsCount = $fruitRepository->count([
            'isFavorite' => true
        ]);

        if ($favoriteFruitsCount < 10) {
            $fruit->setIsFavorite(true);
            $entityManager->flush();

        } else {
            throw $this->createNotFoundException(
                'Favorite fruit count reached limit.'
            );
        }

        return $this->json([
            'success' => true
        ]);

    }

    #[Route('/fruit/remove-favorite/{id}', name: 'remove_favorite', methods: ['PUT'])]
    public function removeFavorite(int $id, EntityManagerInterface $entityManager) {
        $fruitRepository = $entityManager->getRepository(Fruit::class);

        $fruit = $fruitRepository->find($id);
        if (!$fruit) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $fruit->setIsFavorite(false);
        $entityManager->flush();

        return $this->json([
            'success' => true
        ]);

    }
}
