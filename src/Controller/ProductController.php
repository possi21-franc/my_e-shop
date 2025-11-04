<?php

namespace App\Controller;

use App\Entity\AddProductHistory;
use App\Entity\ImageProduct;
use App\Entity\Product;
use App\Form\AddProductHistoryType;
use App\Form\ProductType;
use App\Form\ProductUpdateFormType;
use App\Repository\AddProductHistoryRepository;
use App\Repository\ProductRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Response as BrowserKitResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/editor/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $product = new Product();
    $form = $this->createForm(ProductType::class, $product);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        // ✅ Upload image principale déjà existante
        $image = $form->get('image')->getData();
        if ($image) {
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFileName = $slugger->slug($originalName);
            $newFileName = $safeFileName . "-" . uniqid() . "." . $image->guessExtension();

            try {
                $image->move(
                    $this->getParameter('image_dir'),
                    $newFileName
                );
            } catch (\Exception $exception) {}

            $product->setImage($newFileName);
        }

        // ✅ Upload des images secondaires (multiple)
        $imagesFiles = $form->get('imagesFiles')->getData();
        if ($imagesFiles) {
            foreach ($imagesFiles as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalName);
                $newFileName = $safeFileName . "-" . uniqid() . "." . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('image_dir'),
                        $newFileName
                    );
                } catch (\Exception $exception) {}

                $imageProduct = new ImageProduct();
                $imageProduct->setImage($newFileName);
                $imageProduct->setProduct($product);

                $entityManager->persist($imageProduct);
            }
        }

        // ✅ Sauvegarde produit + historique comme avant
        $entityManager->persist($product);
        $entityManager->flush();

        $stockHistory = new AddProductHistory();
        $stockHistory->setQte($product->getStock());
        $stockHistory->setProduct($product);
        $stockHistory->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($stockHistory);
        $entityManager->flush();

        $this->addFlash('success', 'Produit ajouté avec succès !');
        return $this->redirectToRoute('app_product_index');
    }

    return $this->render('product/new.html.twig', [
        'product' => $product,
        'form' => $form
    ]);
}


    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $form = $this->createForm(ProductUpdateFormType::class, $product);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        // ✅ Mise à jour image principale
        $image = $form->get('image')->getData();
        if ($image) {
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFileName = $slugger->slug($originalName);
            $newFileName = $safeFileName . "-" . uniqid() . "." . $image->guessExtension();

            try {
                $image->move(
                    $this->getParameter('image_dir'),
                    $newFileName
                );
            } catch (\Exception $exception) {}

            $product->setImage($newFileName);
        }

        // ✅ Upload nouvelles images secondaires (multiple)
        $imagesFiles = $form->get('imagesFiles')->getData();
        if ($imagesFiles) {
            foreach ($imagesFiles as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalName);
                $newFileName = $safeFileName . "-" . uniqid() . "." . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('image_dir'),
                        $newFileName
                    );
                } catch (\Exception $exception) {}

                $imageProduct = new ImageProduct();
                $imageProduct->setImage($newFileName);
                $imageProduct->setProduct($product);

                $entityManager->persist($imageProduct);
            }
        }

        $entityManager->flush();

        $this->addFlash('success', 'Produit modifié avec succès !');
        return $this->redirectToRoute('app_product_index');
    }

    return $this->render('product/edit.html.twig', [
        'product' => $product,
        'form' => $form,
    ]);
}


#[Route('/image/remove/{id}', name: 'app_image_remove', methods: ['GET','POST'])]
public function removeImage(ImageProduct $imageProduct, EntityManagerInterface $entityManager): Response
{
    // Récupérer le chemin de l'image sur le serveur
    $imagePath = $this->getParameter('image_dir') . '/' . $imageProduct->getImage();

    // Supprimer le fichier du serveur si il existe
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }

    // Supprimer l'entité de la base de données
    $entityManager->remove($imageProduct);
    $entityManager->flush();

    $this->addFlash('success', 'Image supprimée avec succès !');

    // Redirection vers la page précédente (édition du produit)
    return $this->redirect($this->generateUrl('app_product_edit', ['id' => $imageProduct->getProduct()->getId()]));
}


    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('danger' , 'Produit supprimer avec succes !');
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
        #[Route('/add/product/{id}/stock', name: 'app_product_stock_add', methods: ['POST','GET'])]

        public function addStock($id, EntityManagerInterface $entityManager, Request $request, ProductRepository $productRepository):Response
        {
            $addStock = new AddProductHistory(); 
            $form = $this->createForm(AddProductHistoryType::class, $addStock, );
            $form->handleRequest($request);

            $product = $productRepository->find($id);

             if($form->isSubmitted() && $form->isValid() ){

                if($addStock->getQte() > 0){
                $newQte = $product->getStock() + $addStock->getQte();
                $product->setStock($newQte);

                $addStock->setCreatedAt(new DateTimeImmutable());
                $addStock->setProduct($product);

                $entityManager->persist($addStock);
                $entityManager->flush();

                $this->addFlash('success', 'votre stock a été mise à jour avec sussces !');

                return $this->redirectToRoute('app_product_index');

                }

                else{
                $this->addFlash('danger', 'le stock ne doit pas être inferieur à 0 !');
                return $this->redirectToRoute('app_product_stock_add', ['id'=> $product->getId() ]);
                }
            }


            return $this->render('product/addStock.html.twig', 
            [
                'form'=> $form->createView(),
                'product'=> $product
            ]
        );

        }


        #[Route('/add/product/{id}/stock/history', name: 'app_product_stock_history', methods: ['GET'])]

        public function productAddHistory($id, ProductRepository $productRepository, AddProductHistoryRepository $addProductHistoryRepository): Response
        {
             $product = $productRepository->find($id);
             $productAddHistory = $addProductHistoryRepository->findBy(['product'=>$product], ['id'=>'DESC']);

             return $this->render('product/addStockHistoryShow.html.twig', [
                'productsAdded'=>$productAddHistory,
                'product'=>$product

             ]);



        }


}
