<?php

namespace App\Controller;

use App\Repository\OderRepository;
use App\Services\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StripeController extends AbstractController
{
    #[Route('/pay/success', name: 'app_stripe_success')]
    public function success(Cart $cart,  SessionInterface $session,  ): Response
    {
        $session->set('cart', []);
        return $this->render('stripe/success.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }


    #[Route('/pay/cancel', name: 'app_stripe_cancel')]
    public function cancel(): Response
    {
        return $this->render('stripe/cancel.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }

     #[Route('/stripe/notify', name: 'app_stripe_notify')]

     public function srtipeNotify(Request $request, OderRepository $oderRepository, EntityManagerInterface $entityManager):Response
     {
           Stripe::setApiKey($_SERVER['STRIPE_SECRET']);
           $endpoint_secret  = 'whsec_1ccfc35b0ae3c52c46ee14fa2b5374af3a4040c09ad2fe6ce0f1949189d4171f';
           $payload = $request->getContent();
           $sig_header = $request->headers->get('stripe-signature');
           $event = null ; 

           try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
           } catch (\UnexpectedValueException $e) {
            return new Response('payload invalide', 400);
           }catch(\Stripe\Exception\SignatureVerificationException $e)
           {
            return new Response('signature invalide ');
           }

           switch($event->type){
            case 'payment_intent.succeeded': //contient l'objet payment_intent
                $paymentIntent = $event->data->object;
                //$fileName = 'stripe-details-'.uniqid().'txt';
                $orderId = $paymentIntent->metadata->orderId;
                $order = $oderRepository->find($orderId);
                $totalAmount = $order->getTotalPrice();
                $stripeTotalAmount = $paymentIntent->amount/100;
                 
                if ($totalAmount == $stripeTotalAmount) {
                $order->setIsPaymentCompleted(1);
                $entityManager->flush();

                }
                //file_put_contents($fileName, $orderId);
                break;

            case 'payment_method.attached': //contient l'objet payment_method
                $paymentMethod = $event->data->object;
                break;
            default:
                break;

           }

           return new Response('évenement reçu', 200);

     }

}
