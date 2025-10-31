<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Services\Cart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

final class CartController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {}

    // Page panier
    #[Route('/cart', name: 'app_cart', methods:['GET'])]
    public function index(SessionInterface $session, Cart $cart): Response
    {
        $data = $cart->getCart($session);
        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
            'items' => $data['cart'],
            'total' => $data['total'],
            'cart_count' => array_sum($data['cart']),
        ]);
    }

    // Ajouter un produit
    #[Route('/cart/add/{id}', name: 'app_cart_add', methods:['GET'])]
    public function addToCart(SessionInterface $session, Product $product, Request $request): Response
    {
        $cart = $session->get('cart', []);
        $id = $product->getId();
        $cart[$id] = ($cart[$id] ?? 0) + 1;
        $session->set('cart', $cart);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['count' => array_sum($cart)]);
        }

        return $this->redirectToRoute('app_cart');
    }

    // Supprimer un produit
    #[Route('/cart/remove/{id}', name: 'app_cart_remove', methods:['GET'])]
    public function removeFromCart(SessionInterface $session, $id, Request $request): Response
    {
        $cart = $session->get('cart', []);
        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }
        $session->set('cart', $cart);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['count' => array_sum($cart)]);
        }

        return $this->redirectToRoute('app_cart');
    }

    // Diminuer la quantité
    #[Route('/cart/decrease/{id}', name: 'app_cart_decrease', methods:['GET'])]
    public function decreaseCart(SessionInterface $session, $id, Request $request): Response
    {
        $cart = $session->get('cart', []);
        if (!empty($cart[$id])) {
            if ($cart[$id] > 1) {
                $cart[$id]--;
            } else {
                unset($cart[$id]);
            }
        }
        $session->set('cart', $cart);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['count' => array_sum($cart)]);
        }

        return $this->redirectToRoute('app_cart');
    }

    // Vider le panier
    #[Route('/cart/clear', name: 'app_cart_clear', methods:['GET'])]
    public function clearCart(SessionInterface $session, Request $request): Response
    {
        $session->set('cart', []);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['count' => 0]);
        }

        return $this->redirectToRoute('app_cart');
    }

    // Retourne le count actuel du panier (pour badge)
    #[Route('/cart/count', name: 'app_cart_count', methods:['GET'])]
    public function getCartCount(SessionInterface $session): JsonResponse
    {
        $cart = $session->get('cart', []);
        return new JsonResponse(['count' => array_sum($cart)]);
    }
}
