<?php

namespace Application\Sonata\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Application\Sonata\UserBundle\Entity\User;
use Application\Sonata\UserBundle\Entity\Group;

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
        $lang = $manager->getRepository('ApplicationSonataUserBundle:Lang')->findOneBy(['name' => 'English']);

        $group = $manager->getRepository('ApplicationSonataUserBundle:Group')
            ->findOneBy(['systemName' => Group::LEVEL_MASTER]);

        $user = new User();
        $user
            ->setUsername('admin@gkeep.loc')
            ->setEmail('admin@gkeep.loc')
            ->setPlainPassword('admin')
            ->setEnabled(true)
            ->setLocked(false)
            ->setLang($lang)
            ->setFirstName('John')
            ->setLastName('Dou')
            ->setGroups([$group])
            ->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_ALLOWED_TO_SWITCH']);
        $manager->persist($user);

        $manager->flush();

        $this->addReference('user_admin', $user);
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 140;
    }
}
