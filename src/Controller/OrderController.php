<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Oder;
use App\Entity\OrderProducts;
use App\Form\OrderType;
use App\Repository\ProductRepository;
use App\Services\Cart;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    public function index(Request $request, EntityManagerInterface $entityManager , SessionInterface $session, ProductRepository $productRepository, Cart $cart ): Response
    {
         
         $data = $cart->getCart($session);

        $order = new Oder();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid()){

            if($order->isPayOnDelivery()){
                if(!empty($data['cart'])){

                    $order->setTotalPrice($data['total']);
             $order->setCreatedAt(new DateTimeImmutable());

            $entityManager->persist($order);
            $entityManager->flush();

            foreach($data['cart'] as $value){
            $orderProduct  = new OrderProducts();
            $orderProduct->setOder($order);
            $orderProduct->setProduct($value['product']);
            $orderProduct->setQte($value['quantity']);
            $entityManager->persist($orderProduct);
            $entityManager->flush();

            }

                }

            $session->set('cart', []);
            return $this->redirectToRoute('app_order_message');

            
             
            }
        }

        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
            'form' => $form,
            'items' => $data['cart'],
            'total' => $data['total'],
        ]);
    }

        #[Route('/city/{id}/shipping/cost', name: 'app_city_shipping_cost')]

        public function cityShippingCost(City $city):Response

        {
              $cityShippingCost = $city->getShippingCost();
              return new Response(json_encode(['status'=>200, "message"=> 'OK', "content"=>$cityShippingCost]));
        }

            #[Route('/order_message', name: 'app_order_message')]

            public function message():Response
            {
                return $this->render('order/order_message.html.twig');
            }


}
