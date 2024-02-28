<?php

namespace App\Controller\Blog;

use App\Entity\Post\Category;
use App\Form\SearchType;
use App\Model\SearchData;
use App\Repository\Post\CategoryRepository;
use App\Repository\Post\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    #[Route('/category/{slug}', name: 'category.index', methods: ['GET'])]
    public function index(Request $request, Category $category, PostRepository $postRepository): Response
    {
        $searchData = new SearchData();
        $form = $this->createForm(SearchType::class, $searchData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $searchData->page = $request->query->getInt('page', 1);
            $posts = $postRepository->findBySearch($searchData);

            return $this->render('pages/post/index.html.twig', [
                'posts'=> $posts,
                'category'=> $category,
                'form'=> $form
            ]);
        }

        return $this->render('category/index.html.twig', [
            'category' => $category,
            'form'=> $form,
            'posts'=> $postRepository->findPublished($request->query->getInt('page', 1), $category)
        ]);
    }
}
