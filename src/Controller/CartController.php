<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use App\Form\CartType;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;


class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(CartRepository $cartRepository): JsonResponse
    {
        $result = $cartRepository->findAll();
        return $this->json($result);
    }

    #[Route('/cart/new', name: 'cart_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse{
        $cart = new Cart();
        $entityManager->persist($cart);
        $entityManager->flush();
    
        return $this->json([
            'result' => 'Success!',
            'cartId' => $cart->getId()
        ]);
    }
    
    #[Route('/cart/newProduct/{id}', name: 'cart_new_product')]
    public function newProduct(Request $request, EntityManagerInterface $entityManager, CartRepository $cartRepository, ProductRepository $productRepository, $id): JsonResponse{
        
        $cart = $entityManager->getRepository(Cart::class)->find($id);

        $data = json_decode($request->getContent(), true);

        $product = $entityManager->getRepository(Product::class)->find($data['productId']);

        $cart->addProduct($product);
    

        $entityManager->persist($cart);
        $entityManager->flush();

        return $this->json([
            'result' => 'Success!'
        ]);
    }
}
