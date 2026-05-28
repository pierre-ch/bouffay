<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    //    /**
    //     * @return Order[] Returns an array of Order objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Order
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Retrieves all orders that contain at least one product sold by the given seller.
     * @return Order[]
     */
    public function findOrdersForSeller(\App\Entity\User $seller): array
    {
        return $this->createQueryBuilder('o')
            ->join('o.orderItems', 'oi')
            ->join('oi.product', 'p')
            ->andWhere('p.seller = :seller')
            ->setParameter('seller', $seller)
            ->orderBy('o.createdAt', 'DESC')
            // Using distinct because an order might have multiple items from the same seller
            ->distinct()
            ->getQuery()
            ->getResult()
        ;
    }
}
