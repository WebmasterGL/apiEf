<?php

namespace XalokBundle\Entity;

/**
 * PageMetadata
 */
class PageMetadata
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var array
     */
    private $allowedModules;

    /**
     * @var array
     */
    private $newModules;

    /**
     * @var string
     */
    private $checksum;


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
     * Set allowedModules
     *
     * @param array $allowedModules
     *
     * @return PageMetadata
     */
    public function setAllowedModules($allowedModules)
    {
        $this->allowedModules = $allowedModules;

        return $this;
    }

    /**
     * Get allowedModules
     *
     * @return array
     */
    public function getAllowedModules()
    {
        return $this->allowedModules;
    }

    /**
     * Set newModules
     *
     * @param array $newModules
     *
     * @return PageMetadata
     */
    public function setNewModules($newModules)
    {
        $this->newModules = $newModules;

        return $this;
    }

    /**
     * Get newModules
     *
     * @return array
     */
    public function getNewModules()
    {
        return $this->newModules;
    }

    /**
     * Set checksum
     *
     * @param string $checksum
     *
     * @return PageMetadata
     */
    public function setChecksum($checksum)
    {
        $this->checksum = $checksum;

        return $this;
    }

    /**
     * Get checksum
     *
     * @return string
     */
    public function getChecksum()
    {
        return $this->checksum;
    }
}

