<?php

namespace App\Repository;

use App\Entity\Ateliers;
use App\Entity\Details;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method Details|null find($id, $lockMode = null, $lockVersion = null)
 * @method Details|null findOneBy(array $criteria, array $orderBy = null)
 * @method Details[]    findAll()
 * @method Details[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Details::class);
    }

   /* /**
   * @return array
   * @throws \Doctrine\DBAL\DBALException
   */
   /*
    public function getNextDates(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT details.date, details.places, details.id, user_id as participants, ateliers.id AS ateliers, ateliers.nom, ateliers.description, ateliers.event, ateliers.visibility, ateliers.cover, ateliers.alt FROM details INNER JOIN ateliers ON details.atelier_id = ateliers.id LEFT JOIN reservation_ateliers ON details.id = reservation_ateliers.details_id LEFT JOIN user ON reservation_ateliers.user_id = user.id WHERE details.date >= NOW() AND ateliers.visibility = true AND ateliers.event = false        ';

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }
    */

    public function findNextDates()
    {
        return $this->createQueryBuilder('d')
            ->innerJoin('App\Entity\Ateliers', 'a', Join::WITH, 'd.atelier = a.id')
            ->andWhere('d.date >= :now')
            ->setParameter('now', new \DateTime())
            ->andWhere('a.visibility = true')
            ->andWhere('a.event = false')
            ->orderBy('d.date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNextEvents()
    {
        return $this->createQueryBuilder('d')
            ->innerJoin('App\Entity\Ateliers', 'a', Join::WITH, 'd.atelier = a.id')
            ->andWhere('d.date >= :now')
            ->setParameter('now', new \DateTime())
            ->andWhere('a.visibility = true')
            ->andWhere('a.event = true')
            ->orderBy('d.date', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /*
    public function findOneBySomeField($value): ?Details
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
