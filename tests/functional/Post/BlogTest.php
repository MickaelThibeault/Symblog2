<?php

namespace App\Tests\functional\Post;

use App\Entity\Post\Category;
use App\Entity\Post\Post;
use App\Entity\Post\Tag;
use App\Repository\Post\CategoryRepository;
use App\Repository\Post\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BlogTest extends WebTestCase
{
    public function testBlogPageWorks(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorExists('h1');
        $this->assertSelectorTextContains('h1', 'Symblog: Le post créé de A à Z avec Symfony');
    }

    public function testPaginationWorks(): void
    {
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $posts = $crawler->filter('div.card');
        $this->assertCount(9, $posts);

        $link = $crawler->selectLink('2')->extract(['href'])[0];
        $crawler = $client->request(Request::METHOD_GET, $link);
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $posts = $crawler->filter('div.card');
        $this->assertGreaterThanOrEqual(1, count($posts));
    }

//    public function testDropdownWorks(): void
//    {
//        $client = static::createClient();
//
//        $crawler = $client->request(Request::METHOD_GET, '/');
//
//        $this->assertResponseIsSuccessful();
//        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
//
//        $link = $crawler->filter('ul[data-te-dropdown-menu-ref]>li>a')->link()->getUri();
//        $client->request(Request::METHOD_GET, $link);
//
//        $this->assertResponseIsSuccessful();
//        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
//        $this->assertRouteSame('category.index');
//    }

    public function testFilterWorks(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface */
        $urlGeneratorInterface = $client->getContainer()->get('router');

        /** @var EntityManagerInterface */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var PostRepository */
        $postRepository = $entityManager->getRepository(Post::class);

        /** @var CategoryRepository */
        $categoryRepository = $entityManager->getRepository(Category::class);

        /** @var Post */
        $post = $postRepository->findOneBy([]);

        /** @var Tag */
        $tag = $post->getTags()[0];

        /** @var Category */
        $category = $categoryRepository->findOneBy([]);

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGeneratorInterface->generate('post.index')
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $searchs = [
            substr($post->getTitle(), 0, 3),
            substr($tag->getName(), 0, 3)
        ];

        foreach ($searchs as $search) {
            $form = $crawler->filter('form[name=search]')->form([
                'search[q]' => $search,
                'search[categories][0]' => $category->getId()
            ]);

            $crawler = $client->submit($form);

            $this->assertResponseIsSuccessful();
            $this->assertResponseStatusCodeSame(Response::HTTP_OK);
            $this->assertRouteSame('post.index');

            $nbPosts = count($crawler->filter('div.card'));
            $posts = $crawler->filter('div.card');
            $count = 0;

            foreach ($posts as $index => $post) {
                $title = $crawler->filter("div.card h5")->getNode($index);
                if (
                    str_contains($title->textContent, $search) ||
                    str_contains($tag->getName(), $search)
                ) {
                    $postCategories = $crawler->filter('div.card div.badges')->getNode($index)->childNodes;

                    for ($i = 1; $i < $postCategories->count(); $i++) {
                        $postCategory = $postCategories->item($i);
                        $name = trim($postCategory->textContent);

                        if ($name === $category->getName()) {
                            $count++;
                        }
                    }
                }
            }

            $this->assertEquals($nbPosts, $count);
        }

    }

    public function testSearchBarReturnsNoItems(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface */
        $urlGeneratorInterface = $client->getContainer()->get('router');

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGeneratorInterface->generate('post.index')
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->filter('form[name=search]')->form([
                'search[q]'=> 'aaazzzrrruuiioon'
            ]

        );

        $crawler = $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertRouteSame('post.index');

        $this->assertSelectorExists('form[name=search]');
        $this->assertSelectorNotExists('div.card');
    }
}