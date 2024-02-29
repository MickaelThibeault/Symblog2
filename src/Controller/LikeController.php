<?php

namespace App\Controller;

use App\Entity\Post\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LikeController extends AbstractController
{
    #[Route('/like/article/{id}', name: 'like.post')]
    #[IsGranted('ROLE_USER')]
    public function like(Post $post, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();

        if ($post->isLikedByUser($user)) {
            $post->removeLike($user);
            $manager->flush();

            return $this->json([
                'message' => 'Le like a été supprimé !',
                'nbLike' => count($post->getLikes())
            ]);
        }

        $post->addLike($user);
        $manager->flush();

        return $this->json([
            'message' => 'Le like a été ajouté !',
            'nbLike' => count($post->getLikes())
            ]);

    }
}