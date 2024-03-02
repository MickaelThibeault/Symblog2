<?php

namespace App\Controller\Blog;

use App\Entity\Post\Comment;
use App\Entity\Post\Post;
use App\Form\CommentType;
use App\Form\SearchType;
use App\Model\SearchData;
use App\Repository\Post\PostRepository;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostController extends AbstractController
{
    #[Route('/', name: 'post.index', methods: ['GET'])]
    public function index(Request $request, PostRepository $postRepository): Response
    {
        $posts = $postRepository->findPublished($request->query->getInt('page', 1));

        $searchData = new SearchData();
        $form = $this->createForm(SearchType::class, $searchData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $searchData->page = $request->query->getInt('page', 1);
            $posts = $postRepository->findBySearch($searchData);

            return $this->render('pages/post/index.html.twig', [
                'posts'=> $posts,
                'form'=> $form
            ]);
        }

        return $this->render('pages/post/index.html.twig',
            [
                'posts'=> $posts,
                'form'=> $form
            ]
        );
    }

    #[Route('/article/{slug}', name: 'post.show', methods: ['GET', 'POST'])]
    public function show(Request $request, Post $post, EntityManagerInterface $manager): Response
    {
        $comment = new Comment();
        if ($this->getUser()) {
            $comment->setAuthor($this->getUser());
            $comment->setPost($post);
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($comment);
            $manager->flush();

            $this->addFlash('success', 'Votre commentaire a bien été enregistré. Il sera soumis à modération dans les plus brefs délais.');
            return $this->redirectToRoute('post.show', ['slug' => $post->getSlug()]);
        }

        return $this->render('pages/post/show.html.twig',
            [
                'post' => $post,
                'form' => $form
            ]
        );
    }
}
