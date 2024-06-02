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
     * Display artworks on sales by user
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
     * display auctions  by artwork
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
     * display auctions made by user
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

    /**
     * display accepted auctions
     *
     * @param Artwork $artwork
     * @return Auction|null
     */
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


    /**
     * display lost auction (based on the archive status)
     *
     * @param Artwork $artwork
     * @param User $user
     * @return boolean
     */
    public function findRefusedAuctionsByUser(Artwork $artwork, User $user): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.artwork', 'aw')
            ->andWhere('aw = :artwork')
            ->andWhere('a.user = :user')
            ->andWhere('a.sold = :sold')
            ->andWhere('aw.archived = :archived')
            ->setParameter('artwork', $artwork)
            ->setParameter('user', $user)
            ->setParameter('sold', 'no')
            ->setParameter('archived', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * display current auctions
     *
     * @param User $user
     * @return array
     */
    public function findOngoingAuctionsByUser(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.artwork', 'aw')
            ->andWhere('a.user = :user')
            ->andWhere('a.sold = :sold')
            ->andWhere('aw.archived = :archived')
            ->setParameter('user', $user)
            ->setParameter('sold', 'no') // auction not won yet
            ->setParameter('archived', false) // artwork not archived
            ->getQuery()
            ->getResult();
    }


    /**
     * Count number of auctions by artwork
     *
     * @param Artwork $artwork
     * @return integer
     */
    public function countAuctionsByArtwork(Artwork $artwork): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.artwork = :artwork')
            ->setParameter('artwork', $artwork)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Display 3 biggest offers
     *
     * @param Artwork $artwork
     * @return array
     */
    public function topThree(Artwork $artwork): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.artwork = :artwork')
            ->setParameter('artwork', $artwork)
            ->orderBy('a.amount', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
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
