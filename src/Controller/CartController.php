<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Services\Cart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{

    public function __construct(
        private readonly  ProductRepository $productRepository,

    )
    {
        
    }

    #[Route('/cart', name: 'app_cart', methods:['GET'])]
    public function index(SessionInterface $session, Cart $cart): Response
    {
        $data = $cart->getCart($session);
        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
            'items' =>$data['cart'],
            'total' =>$data['total'],
            'cart_count' => array_sum($data['cart']), // somme totale des articles
        ]);
    }

        #[Route('/cart/add/{id}', name: 'app_cart_new', methods:['GET'])]

        public function addToCart(SessionInterface $session, $id): Response
        {
            $cart = $session->get('cart',[]);
            if(!empty($cart[$id])){
                $cart[$id]++;
            
            } 

            else {
                $cart[$id] = 1 ;
            }

            $session->set('cart',$cart); 
            return $this->redirectToRoute('app_cart') ;
        }

                #[Route('/cart/remove/{id}', name: 'app_cart_remove', methods:['GET'])]

                public function removeToCart($id, SessionInterface $session): Response
                {
                    $cart = $session->get('cart', []);
                    if(!empty($cart[$id])){
                        unset($cart[$id]);
                    }

                    $session->set('cart',$cart);

                 return $this->redirectToRoute('app_cart');


                }

               #[Route('/cart/remove', name: 'app_cart_remove_cart', methods:['GET'])]

               public function remove(SessionInterface $session):Response
               {
                     $session->set('cart', []);

                     return $this->redirectToRoute('app_cart');
               }

               #[Route('/cart/decrease/{id}', name: 'app_cart_decrease')]
public function decreaseCart(SessionInterface $session, $id): Response
{
    $cart = $session->get('cart', []);

    if (!empty($cart[$id])) {
        if ($cart[$id] > 1) {
            $cart[$id]--; // réduire la quantité
        } else {
            unset($cart[$id]); // supprimer si la quantité devient 0
        }
    }

    $session->set('cart', $cart);
    return $this->redirectToRoute('app_cart');
}







}
