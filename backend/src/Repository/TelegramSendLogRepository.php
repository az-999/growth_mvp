<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\Shop;
use App\Entity\TelegramSendLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<TelegramSendLog> */
class TelegramSendLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramSendLog::class);
    }

    public function findByShopAndOrder(Shop $shop, Order $order): ?TelegramSendLog
    {
        return $this->findOneBy(['shop' => $shop, 'order' => $order]);
    }

    public function countByShopAndStatusSince(Shop $shop, string $status, \DateTimeImmutable $since): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.shop = :shop')
            ->andWhere('l.status = :status')
            ->andWhere('l.sentAt >= :since')
            ->setParameter('shop', $shop)
            ->setParameter('status', $status)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findLastSentAt(Shop $shop): ?\DateTimeImmutable
    {
        $result = $this->createQueryBuilder('l')
            ->select('l.sentAt')
            ->andWhere('l.shop = :shop')
            ->andWhere('l.status = :status')
            ->setParameter('shop', $shop)
            ->setParameter('status', TelegramSendLog::STATUS_SENT)
            ->orderBy('l.sentAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result === null) {
            return null;
        }

        return $result['sentAt'] instanceof \DateTimeImmutable
            ? $result['sentAt']
            : new \DateTimeImmutable($result['sentAt']);
    }
}
