<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Artwork;
use App\Entity\Auction;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Auction>
 *
 * @method Auction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Auction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Auction[]    findAll()
 * @method Auction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuctionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Auction::class);
    }

    /**
     * afficher les ventes d'un utilisateur
     *
     * @param User $user
     * @return array
     */
    public function findSales(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.artwork', 'aw')
            ->where('aw.author = :user')
            ->setParameter('user', $user)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * afficher les enchères de chaque oeuvre
     *
     * @param Artwork $artwork
     * @return void
     */
    public function findAuctionsByArtwork(Artwork $artwork)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.artwork = :artwork')
            ->setParameter('artwork', $artwork)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Afficher les enchères d'un user
     *
     * @param User $user
     * @return array
     */
    public function findAuctionsByUser(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :userId')
            ->setParameter('userId', $user)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAcceptedAuction(Artwork $artwork): ?Auction
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.artwork = :artwork')
            ->andWhere('a.sold = :sold')
            ->setParameter('artwork', $artwork)
            ->setParameter('sold', 'yes')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countAuctionsByArtwork(Artwork $artwork): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.artwork = :artwork')
            ->setParameter('artwork', $artwork)
            ->getQuery()
            ->getSingleScalarResult();
    }

    //    /**
    //     * @return Auction[] Returns an array of Auction objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Auction
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
