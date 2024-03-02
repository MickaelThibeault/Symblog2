<?php

namespace App\Controller\Blog;

use App\Entity\Post\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentController extends AbstractController
{
    #[Route('/comment/{id}', name: 'comment.delete')]
    #[IsGranted(attribute: new Expression('is_granted("ROLE_USER") and user === subject["comment"].getAuthor()'),
                subject: ['comment'] )]
    public function delete(Request $request, Comment $comment, EntityManagerInterface $manager): Response
    {
        $params = ['slug' => $comment->getPost()->getSlug()];
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $manager->remove($comment);
            $manager->flush();

            $this->addFlash('success', 'Votre commentaire a bien été supprimé.');
        }

        return $this->redirectToRoute('post.show', $params);
    }
}
