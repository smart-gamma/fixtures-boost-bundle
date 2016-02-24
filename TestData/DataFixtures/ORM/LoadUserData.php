<?php

namespace Gamma\FixturesBoostBundle\TestData\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Gamma\FixturesBoostBundle\TestData\Entity\User;

/**
 * LoadUserData.
 */
class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user
            ->setId(1)
        ;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 1;
    }
}
