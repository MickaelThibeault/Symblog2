<?php

namespace App\Controller\Blog;

use App\Entity\Post\Tag;
use App\Form\SearchType;
use App\Model\SearchData;
use App\Repository\Post\PostRepository;
use App\Repository\Post\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TagController extends AbstractController
{
    #[Route('/tag/{slug}', name: 'tag.index', methods: ['GET'])]
    public function index(Request $request, Tag $tag, PostRepository $postRepository): Response
    {
        $searchData = new SearchData();
        $form = $this->createForm(SearchType::class, $searchData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $searchData->page = $request->query->getInt('page', 1);
            $posts = $postRepository->findBySearch($searchData);

            return $this->render('pages/post/index.html.twig', [
                'posts'=> $posts,
                'tag'=> $tag,
                'form'=> $form
            ]);
        }

        return $this->render('tag/index.html.twig', [
            'tag' => $tag,
            'form'=> $form,
            'posts'=> $postRepository->findPublished($request->query->getInt('page', 1), null, $tag)
        ]);
    }
}
