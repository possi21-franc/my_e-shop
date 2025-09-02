<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods:['GET'])]
    public function index(ProductRepository $productRepository,CategoryRepository $categoryRepository, Request $request, PaginatorInterface $paginator): Response
    {
         $data = $productRepository->findBy([], ['id'=>'DESC']);
         $products =$paginator->paginate(
            $data,
            $request->query->getInt(key:'page', default:1),
            limit:8
         );

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $products,
            'categories' => $categoryRepository->findAll()
        ]);
    }


    #[Route('/home/product/{id}/show', name: 'app_home_product_show', methods:['GET'])]
    public function show(Product $product, $id, ProductRepository $productRepository, CategoryRepository $categoryRepository ): Response
    {
        $lastProducts = $productRepository->findBy([], ['id'=>'DESC'], limit:8);
         
        return $this->render('home/show.html.twig', [
            'controller_name' => 'HomeController',
            'product' => $product,
            'products' => $lastProducts,
             'categories' => $categoryRepository->findAll()

        ]);
    }

    #[Route('/home/product/{id}/filter', name: 'app_home_product_filter', methods:['GET'])]
    public function filterProduct( $id, SubCategoryRepository $subCategoryRepository, CategoryRepository $categoryRepository, Request $request, PaginatorInterface $paginator ): Response
    {
         $data = $subCategoryRepository->find($id)->getProducts();
        $products =$paginator->paginate(
            $data,
            $request->query->getInt(key:'page', default:1),
            limit:4
         );         $subCategory = $subCategoryRepository->find($id);
        return $this->render('home/filter.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $products,
            'subCategory' => $subCategory,
            'categories' => $categoryRepository->findAll()


        ]);
    }
}
