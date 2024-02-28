<?php

namespace App\Repository\Post;

use App\Entity\Post\Category;
use App\Entity\Post\Post;
use App\Entity\Post\Tag;
use App\Model\SearchData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(private PaginatorInterface $paginator, ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

//    /**
//     * @return Post[] Returns an array of Post objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Post
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     *
     * Get published posts
     *
     * @param int $page
     * @param ?Category $category
     * @param ?Tag $tag
     * @return PaginationInterface
     */
    public function findPublished(int $page, ?Category $category = null, ?Tag $tag = null): PaginationInterface
    {
        $data = $this->createQueryBuilder('p')
            ->andWhere('p.state = :state')
            ->setParameter('state', 'STATE_PUBLISHED')
            ->orderBy('p.createdAt', 'DESC')
        ;


        if (isset($category)) {
            $data = $data
                ->join('p.categories', 'c')
                ->andWhere('c.id = :category')
                ->setParameter('category', $category->getId())
                ;
        }

        if (isset($tag)) {
            $data = $data
                ->join('p.tags', 't')
                ->andWhere('t.id = :tag')
                ->setParameter('tag', $tag->getId())
            ;
        }

        $data->getQuery()
            ->getResult()
        ;

        $posts = $this->paginator->paginate(
            $data,
            $page,
            9
        );

        return $posts;
    }


    /**
     * Get published posts thanks to Search Data value
     *
     * @param SearchData $searchData
     * @return PaginationInterface
     *
     */
    public function findBySearch(SearchData $searchData): PaginationInterface
    {
        $data = $this->createQueryBuilder('p')
            ->andWhere('p.state = :state')
            ->setParameter('state', 'STATE_PUBLISHED')
            ->orderBy('p.createdAt', 'DESC')
            ;

        if (!empty($searchData->q)) {
            $data = $data
                ->join('p.tags', 't')
                ->andWhere('p.title LIKE :q') // search on post's title
                ->orWhere('t.name LIKE :q') // search on tag's name
                ->setParameter('q', "%$searchData->q%")
            ;
        }

        if (!empty($searchData->categories)) {
            $data = $data
                ->join('p.categories', 'c')
                ->andWhere('c.id IN (:categories)')
                ->setParameter('categories', $searchData->categories)
            ;
        }

        $data = $data
            ->getQuery()
            ->getResult();

        $posts = $this->paginator->paginate(
            $data,
            $searchData->page,
            9
        );

        return $posts;
    }
}
