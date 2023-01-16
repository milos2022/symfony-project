<?php

namespace App\DataFixtures;

use App\Entity\Buyer;
use DateTimeImmutable;
use App\Entity\Auction;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $auction = new Auction();
        $auction->setName('Auc 01');
        $auction->setReservePrice(100);
        $auction->setCreatedAt(new DateTimeImmutable());
        $manager->persist($auction);

        $buyer = new Buyer();
        $buyer->setName('Buyer01');
        $buyer->setAuction($auction);
        $buyer->setWins(0);
        $manager->persist($buyer);

        $buyer2 = new Buyer();
        $buyer2->setName('Buyer02');
        $buyer2->setAuction($auction);
        $buyer2->setWins(0);
        $manager->persist($buyer2);

        $buyer3 = new Buyer();
        $buyer3->setName('Buyer01');
        $buyer3->setAuction($auction);
        $buyer3->setWins(0);
        $manager->persist($buyer3);
        
        $manager->persist($auction);

        $auction2 = new Auction();
        $auction2->setName('Auc 02');
        $auction2->setReservePrice(150);
        $auction2->setCreatedAt(new DateTimeImmutable());
        $manager->persist($auction2);

        $manager->flush();
    }
}
