<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\Shop;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $acacia = new Shop('Акация');
        $shik = new Shop('Шик блеск красота');
        $manager->persist($acacia);
        $manager->persist($shik);
        $manager->flush();

        $this->createUser($manager, 'dram1008@yandex.ru', $acacia);
        $this->createUser($manager, 'owner@shik-blask.ru', $shik);

        $this->seedOrders($manager, $acacia, [
            ['A-1001', '1500', 'Иван', 'a1', 1],
            ['A-1002', '3200', 'Мария', 'a2', 1],
            ['A-1003', '2100', 'Олег', 'a3', 1],
            ['A-1004', '4500', 'Елена', 'a1', 2],
            ['A-1005', '1800', 'Дмитрий', 'a2', 1],
        ]);

        $this->seedOrders($manager, $shik, [
            ['S-2001', '2800', 'Анна', 's1', 1],
            ['S-2002', '3500', 'Пётр', 's2', 1],
            ['S-2003', '1900', 'Светлана', 's3', 1],
            ['S-2004', '5200', 'Николай', 's1', 2],
            ['S-2005', '2400', 'Юлия', 's2', 1],
        ]);

        $manager->flush();
    }

    private function createUser(ObjectManager $manager, string $email, Shop $shop): void
    {
        $user = new User();
        $user->setEmail($email);
        $user->setShop($shop);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $manager->persist($user);
    }

    /**
     * @param list<array{0: string, 1: string, 2: string, 3: string, 4: int}> $rows
     */
    private function seedOrders(ObjectManager $manager, Shop $shop, array $rows): void
    {
        foreach ($rows as [$number, $total, $name, $productId, $count]) {
            $order = new Order();
            $order->setShop($shop);
            $order->setNumber($number);
            $order->setTotal($total);
            $order->setCustomerName($name);
            $order->setProductId($productId);
            $order->setCount($count);
            $manager->persist($order);
        }
    }
}
