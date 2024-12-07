<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'product')]
    public function index(Request $request, ProductRepository $productRepository, Product $product): JsonResponse
    {
        $products = $productRepository->findAll();

        return $this->json($products);
    }

    #[Route('/product/new', name: 'product_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse{
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        $form->submit($request->toArray());

        if($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->json([
                'result' => 'Success!',
            ]);
        }

        return $this->json([
            'result' => 'Something went wrong'
        ]);
    }

    #[Route('product/show/{id}', name: 'product_show')]
    public function show(Request $request, Product $product): JsonResponse{
        return $this->json($product);
    }

    #[Route('product/edit/{id}', name: 'product_edit')]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): JsonResponse{
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        $form->submit($request->toArray());
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->json([
                'result' => 'Success!'
            ]);
        }

        return $this->json([
            'message' => 'Fail!'
        ]);
    }

    #[Route('product/delete/{id}', name: 'product_delete')]
    public function delete(Request $request, EntityManagerInterface $entityManager, Product $product){
        $entityManager->remove($product);
        $entityManager->flush();

        return $this->json('Succesfully deleted!');
    }
}
