<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Repository\UserRepository;



final class UserController extends AbstractController
{
    #[Route('/admin/user', name: 'app_user')]
    public function index(UserRepository $userRepository): Response
    {

        $users = $userRepository->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

        #[Route('/admin/user/{id}/to/editor', name: 'app_user_to_editor')]
        public function changeRole(EntityManagerInterface $entityManager, User $user):Response
        {
             $user->setRoles(["ROLE_EDITOR", "ROLE_USER"]);
             $entityManager->flush();
            $this->addFlash('success', 'le rôle a été ajouté a votre utilisateur !');
            return $this->redirectToRoute('app_user');

        }
        
        #[Route('/admin/user/{id}/remove/editor', name: 'app_user_remove_editor')]
        public function removeRole(EntityManagerInterface $entityManager, User $user):Response
        {
             $user->setRoles([]);
             $entityManager->flush();
            $this->addFlash('danger', 'le rôle a été retiré a votre utilisateur !');
            return $this->redirectToRoute('app_user');

        }
        
        #[Route('/admin/user/{id}/delete', name: 'app_user_delete')]
        public function removeUser(EntityManagerInterface $entityManager, User $user):Response
        {
             $entityManager->remove($user);
             $entityManager->flush();
            $this->addFlash('danger', 'votre utilisateur a été supprimé !');
            return $this->redirectToRoute('app_user');

        }
        


}
