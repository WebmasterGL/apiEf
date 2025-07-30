<?php

namespace BackendBundle\Entity;

/**
 * Parallax
 */
class Parallax
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $structure;

    /**
     * @var \DateTime
     */
    private $updatedAt;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set structure
     *
     * @param string $structure
     *
     * @return Parallax
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Get structure
     *
     * @return string
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Parallax
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}

