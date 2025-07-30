<?php

namespace BackendBundle\Entity;

/**
 * Flags
 */
class Flags
{
    /**
     * @var integer
     */
    private $idflags;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $imageUrl;


    /**
     * Get idflags
     *
     * @return integer
     */
    public function getIdflags()
    {
        return $this->idflags;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Flags
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set imageUrl
     *
     * @param string $imageUrl
     *
     * @return Flags
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * Get imageUrl
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * Set idPage
     *
     * @param \BackendBundle\Entity\Page $idPage
     *
     * @return Flags
     */
    public function setIdPage(\BackendBundle\Entity\Page $idPage = null)
    {
        $this->idPage = $idPage;

        return $this;
    }
    /**
     * @var string
     */
    private $html;


    /**
     * Set html
     *
     * @param string $html
     *
     * @return Flags
     */
    public function setHtml($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Get html
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @var boolean
     */
    private $active;


    /**
     * Set active
     *
     * @param string $active
     *
     * @return Flags
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }
}
