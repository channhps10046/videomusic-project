<?php

namespace App\Repository;

use App\Entity\Video;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;


/**
 * @method Video|null find($id, $lockMode = null, $lockVersion = null)
 * @method Video|null findOneBy(array $criteria, array $orderBy = null)
 * @method Video[]    findAll()
 * @method Video[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Video::class);
        $this->paginator = $paginator;
    }

    public function findByChildIds(array $value)
    {
        $dbquery = $this->createQueryBuilder('v')->andWhere('v.category IN (:val)')->setParameter('val', $value)->getQuery()->getResult();
        return $dbquery;
    }



    public function videoDetails($id)
    {
        return $this->createQueryBuilder('v')->leftJoin('v.comments', 'c')->leftJoin('c.user', 'u')->addSelect('c', 'u')->where('v.id = :id')->setParameter('id', $id)->getQuery()->getOneOrNullResult();
    }


    public function comments($value)
    {
        return $this->createQueryBuilder('v')->leftJoin('v.comments', 'c')->leftJoin('c.user', 'u')->addSelect('c', 'u')->where('v.id = :id')->setParameter('val', $value)->getQuery()->getResult();
    }

    public function findByTitle(string $query)
    {
        $queryBuilder = $this->createQueryBuilder('v');
        $searchTerms = $this->prepareQery($query);

        foreach ($searchTerms as $key => $term) {
            $queryBuilder->orWhere('v.title LIKE :t_' . $key)->setParameter('t_' . $key, '%' . trim($term) . '%');
        }

        $dbQuery = $queryBuilder->orderBy('v.title', 'ASC')->getQuery()->getResult();

        return $dbQuery;
    }

    private function prepareQery(string $query): array
    {
        $term = array_unique(explode(' ', $query));
        return array_filter($term, function ($term) {
            return 2 <= mb_strlen($term);
        });
    }


    // /**
    //  * @return Video[] Returns an array of Video objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Video
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
