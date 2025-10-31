<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ShopController extends AbstractController
{
    #[Route('/shop', name: 'app_shop')]
   #[Route('/shop', name: 'app_shop')]
public function index(
    ProductRepository $productRepository,
    CategoryRepository $categoryRepository,
    SubCategoryRepository $subCategoryRepository,
    Request $request,
    PaginatorInterface $paginator
): Response
{
    $subcategoryId = $request->query->get('subcategory');

    if ($subcategoryId) {
        $subcategory = $subCategoryRepository->find($subcategoryId);
        $data = $subcategory ? $subcategory->getProducts() : [];
    } else {
        $data = $productRepository->findBy([], ['id' => 'DESC']);
        $subcategory = null;
    }

    $products = $paginator->paginate(
        $data,
        $request->query->getInt('page', 1),
        8
    );
    
    // 🔥 AJAX request case
    if ($request->isXmlHttpRequest()) {
        return $this->render('shop/_products.html.twig', [
            'products' => $products
        ]);
    }
    
    return $this->render('shop/index.html.twig', [
        'products' => $products,
        'categories' => $categoryRepository->findAll(),
        'selectedSubCategory' => $subcategory ? $subcategoryId : null,
    ]);
}

}
