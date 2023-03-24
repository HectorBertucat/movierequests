<?php

namespace App\Repository;

use App\Entity\Request;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Request>
 *
 * @method Request|null find($id, $lockMode = null, $lockVersion = null)
 * @method Request|null findOneBy(array $criteria, array $orderBy = null)
 * @method Request[]    findAll()
 * @method Request[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestRepository extends ServiceEntityRepository
{

    public const PAGINATOR_PER_PAGE = 5;
    public const NB_LAST_REQUESTS = 5;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Request::class);
    }

    public function getRequestPaginator(int $offset, User $user = null, int $status = null): Paginator
    {
        $query = $this->createQueryBuilder('r')
            ->orderBy('r.status', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(self::PAGINATOR_PER_PAGE);

        if ($user != null) {
            $query->andWhere('r.madeBy = ' . $user->getId());
        }

        if ($status =! null) {
            $query->andWhere('r.status = ' . $status);
        }

        $query->getQuery();

        return new Paginator($query);
    }

        public function getLastAcceptedRequests(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.date_fullfilled', 'DESC')
            ->andWhere('r.status = 2')
            ->setMaxResults(self::NB_LAST_REQUESTS)
            ->getQuery()
            ->getResult();
    }

    public function save(Request $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Request $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Request[] Returns an array of Request objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Request
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
