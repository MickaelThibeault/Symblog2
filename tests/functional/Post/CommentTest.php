<?php

namespace App\Tests\functional\Post;

use App\Entity\Post\Comment;
use App\Entity\Post\Post;
use App\Entity\User;
use App\Repository\Post\CommentRepository;
use App\Repository\Post\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommentTest extends WebTestCase
{
    public function testPostCommentWorks(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface */
        $urlGeneratorInterface = $client->getContainer()->get('router');

        /** @var EntityManagerInterface */
        $entityManager = $client->getContainer()->get('doctrine.orm.default_entity_manager');

        /** @var  PostRepository */
        $postRepository = $entityManager->getRepository(Post::class);

        /** @var  UserRepository */
        $userRepository = $entityManager->getRepository(User::class);

        $post = $postRepository->findOneBy([]);

        $user = $userRepository->findOneBy([]);
        $client->loginUser($user);

        $crawler = $client->request('GET',
            $urlGeneratorInterface->generate('post.show', ['slug' => $post->getSlug()]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->filter('form[name=comment]')->form([
            'comment[content]' => 'test pour le fonctionnement'
        ]);

        $client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertRouteSame('post.show', ['slug' => $post->getSlug()]);
        $this->assertSelectorTextContains('div.alert.alert-success',
            'Votre commentaire a bien été enregistré. Il sera soumis à modération dans les plus brefs délais.'
        );

    }

    public function testPostCommentIfUserNotLoggedIn(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface */
        $urlGeneratorInterface = $client->getContainer()->get('router');

        /** @var EntityManagerInterface */
        $entityManager = $client->getContainer()->get('doctrine.orm.default_entity_manager');

        /** @var  PostRepository */
        $postRepository = $entityManager->getRepository(Post::class);

        $post = $postRepository->findOneBy([]);

        $client->request('GET',
            $urlGeneratorInterface->generate('post.show', ['slug' => $post->getSlug()]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorNotExists('div.comment__new');
    }

    public function testDeleteComment(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface */
        $urlGeneratorInterface = $client->getContainer()->get('router');

        /** @var EntityManagerInterface */
        $entityManager = $client->getContainer()->get('doctrine.orm.default_entity_manager');

        /** @var CommentRepository $commentRepository */
        $commentRepository = $entityManager->getRepository(Comment::class);

        /** @var  UserRepository */
        $userRepository = $entityManager->getRepository(User::class);

        $user = $userRepository->findOneBy([]);

        $post = $commentRepository->findOneBy(['author' => $user])->getPost();

        $client->loginUser($user);

        $crawler = $client->request('GET',
            $urlGeneratorInterface->generate('post.show', ['slug' => $post->getSlug()]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->filter('form[name=comment_delete]')->form();

        $client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success',
            'Votre commentaire a bien été supprimé.'
        );

    }
}
