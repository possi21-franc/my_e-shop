<?php

namespace App\Services;

use App\Repository\ProductRepository;

class Cart

{

     public function __construct(
        private readonly  ProductRepository $productRepository,

    )
    {
        
    }
    public function getCart($session):array
    {
         $cart = $session->get('cart' , []);
         $cartWhithData = [];
         foreach($cart as $id=>$quantity){
            $cartWhithData[ ] = [
                 'product'=>$this->productRepository->find($id),
                 'quantity'=>$quantity
            ];
         }
          $total = array_sum(array_map(function ($item){
             return $item['product']->getPrice() * $item['quantity'];
          } , $cartWhithData));
           
          return [
            'cart'=>$cartWhithData,
            'total'=>$total,
          ];
    }
}
