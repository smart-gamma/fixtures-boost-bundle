<?php

namespace Gamma\FixturesBoostBundle\TestData\Entity;

/**
 * Sample entity.
 */
class User
{
    /**
     * @var int
     */
    protected $id;

    /**
     * Get id.
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @return this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
