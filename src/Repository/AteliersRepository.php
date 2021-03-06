<?php

namespace App\Repository;

use App\Entity\Ateliers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method Ateliers|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ateliers|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ateliers[]    findAll()
 * @method Ateliers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AteliersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ateliers::class);
    }

    public function getAteliers()
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.event = false')
            ->getQuery()
            ->getResult()
            ;
    }

    public function getAteliersVisible()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT * FROM ateliers LEFT JOIN details ON ateliers.id = details.atelier_id WHERE visibility = true AND event = false AND details.atelier_id is null';
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getEvents()
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.event = true')
            ->getQuery()
            ->getResult()
            ;
    }

    public function getEventsVisible()
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.event = true')
            ->andWhere('a.visibility = true')
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return Ateliers[] Returns an array of Ateliers objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ateliers
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
