<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CategoryFormType;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;

final class CategoryController extends AbstractController
{
    #[Route('/admin/category', name: 'app_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        return $this->render('category/index.html.twig', [
            'categories' => $categories
        ]);
    }

        #[Route('/admin/category/new', name: 'app_category_new')]

        public function addCategory(EntityManagerInterface $entityManager, Request $request):Response
        {

            $category =new Category();
             $form = $this->createForm(CategoryFormType::class, $category);
             $form->handleRequest($request);

             if($form->isSubmitted() && $form->isValid()){
                //dd($category);
                $entityManager->persist($category);
                $entityManager->flush();
                $this->addFlash('success' , 'Catégorie ajoutée avec succes !');
                return $this->redirectToRoute('app_category');
             }


            return $this->render('category/new.html.twig', ['form'=>$form->createView()]);
        }

                #[Route('/admin/category/{id}/edit', name: 'app_category_edit')]

                public function editCategory(EntityManagerInterface $entityManager, Category $category, Request $request ):Response
                {
             $form = $this->createForm(CategoryFormType::class, $category);
             $form->handleRequest($request);

                          if($form->isSubmitted() && $form->isValid()){
                             $entityManager->flush();
                            $this->addFlash('success' , 'Catégorie modifiée avec succes !');
                             return $this->redirectToRoute('app_category');
                          }

            return $this->render('category/edit.html.twig', ['form'=>$form->createView()]);

                }


                                #[Route('/admin/category/{id}/delete', name: 'app_category_delete')]

                                public function deleteCategory(EntityManagerInterface $entityManager, Category $category, ):Response
                                {
                                    $entityManager->remove($category);
                                    $entityManager->flush();
                                    $this->addFlash('danger' , 'Catégorie supprimer avec succes !');
                                    return $this->redirectToRoute('app_category');
                                }

}
