<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Oder;
use App\Entity\OrderProducts;
use App\Form\OrderType;
use App\Repository\OderRepository;
use App\Repository\OrderProductsRepository;
use App\Repository\ProductRepository;
use App\Services\Cart;
use App\Services\StripePayment;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{

    public function __construct(private MailerInterface $mailer)
    {
        
    }
    #[Route('/order', name: 'app_order')]
    public function index(Request $request,
     EntityManagerInterface $entityManager ,
      SessionInterface $session, 
      ProductRepository $productRepository,
       Cart $cart, ): Response
    {
         
         $data = $cart->getCart($session);
        

        $order = new Oder();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid()){


            if(!empty($data['cart'])){

             $totalPrice =  $data['total'] + $order->getCity()->getShippingCost();
             $order->setTotalPrice($totalPrice);
             $order->setCreatedAt(new DateTimeImmutable());
             $order->setIsPaymentCompleted(0);
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

             if($order->isPayOnDelivery()){


            $session->set('cart', []);

            $html = $this->renderView('mail/order_confirm.html.twig', [
                'order'=> $order,
                'products' => $data['cart']  
            ]);

            $email = (new Email())
            ->from('myEshop@gmail.com')
            ->to($order->getAdresse())
            ->subject('confirmation de reception de la commande')
            ->html($html);

            $this->mailer->send($email);
            return $this->redirectToRoute('app_order_message');
            
            }

             //gestion du payement avec stripe

            $payment = new StripePayment();
            $shippingCost = $order->getCity()->getShippingCost();
            $payment->startPayment($data, $shippingCost, $order->getId());
            $stripeRedirectUrl = $payment->getStripeRedirectUrl();
            return $this->redirect($stripeRedirectUrl);
                 


        }

        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
            'form' => $form,
            'items' => $data['cart'],
            'total' => $data['total'],
        ]);
    }

        #[Route('/editor/order/{type}/', name: 'app_order_show')]

        public function getAllOrder( $type ,  OderRepository $oderRepository, Request $request, PaginatorInterface $paginator):Response
        {

            if($type == 'is-completed'){
            $data = $oderRepository->findBy(['isCompleted'=> 1 ],['id'=>'DESC']);
            }elseif($type == 'pay-on-stripe-undelivery'){
            $data = $oderRepository->findBy(['isCompleted'=> null, 'payOnDelivery' => 0, 'isPaymentCompleted' => 1 ],['id'=>'DESC']);
            }elseif($type == 'pay-on-delivery-delivery' ){
             $data = $oderRepository->findBy(['isCompleted'=> 1, 'payOnDelivery' => 1,  ],['id'=>'DESC']);

            }
            elseif($type == 'pay-on-delivery-undelivery' ){
             $data = $oderRepository->findBy(['isCompleted'=> null, 'payOnDelivery' => 1,  ],['id'=>'DESC']);

            }

           // dd($orders);

           $order =$paginator->paginate(
            $data,
            $request->query->getInt(key:'page', default:1),
            limit:2
         );
            return $this->render('order/order_show.html.twig', [
                'orders'=>$order,
            ]);
        }

                #[Route('/editor/order/{id}/is_completed/update', name: 'app_order_is_completed_update')]

                public function isCompleted($id , OderRepository $oderRepository, EntityManagerInterface $entityManager, Request $request):Response
                {
                    $order  = $oderRepository->find($id);
                    $order->setIsCompleted(true);
                    $entityManager->flush();
                    $this->addFlash('sucess', "votre commande a été livré ");
                    return $this->redirect($request->headers->get('referer'));
                }

                 #[Route('/editor/order/{id}/remove', name: 'app_order_remove')]

                 public function remove( EntityManagerInterface $entityManager, Oder $order,Request $request):Response
                 {
                    $entityManager->remove($order);
                    $entityManager->flush();
                    $this->addFlash('danger', "votre commande a été supprimée ");
                    return $this->redirect($request->headers->get('referer'));


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

            #[Route('/order_mail', name: 'app_order_mail')]

            public function mail():Response
            {

                return $this->render('mail/order_confirm.html.twig');
            }


}
