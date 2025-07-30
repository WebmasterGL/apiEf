<?php

namespace BackendBundle\Entity;

/**
 * Programacion
 */
class Programacion
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $diaHora;

    /**
     * @var string
     */
    private $nameConductor;

    /**
     * @var string
     */
    private $colorHexa;

    /**
     * @var string
     */
    private $twitter;

    /**
     * @var string
     */
    private $description;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Programacion
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
     * Set diaHora
     *
     * @param string $diaHora
     *
     * @return Programacion
     */
    public function setDiaHora($diaHora)
    {
        $this->diaHora = $diaHora;

        return $this;
    }

    /**
     * Get diaHora
     *
     * @return string
     */
    public function getDiaHora()
    {
        return $this->diaHora;
    }

    /**
     * Set nameConductor
     *
     * @param string $nameConductor
     *
     * @return Programacion
     */
    public function setNameConductor($nameConductor)
    {
        $this->nameConductor = $nameConductor;

        return $this;
    }

    /**
     * Get nameConductor
     *
     * @return string
     */
    public function getNameConductor()
    {
        return $this->nameConductor;
    }

    /**
     * Set colorHexa
     *
     * @param string $colorHexa
     *
     * @return Programacion
     */
    public function setColorHexa($colorHexa)
    {
        $this->colorHexa = $colorHexa;

        return $this;
    }

    /**
     * Get colorHexa
     *
     * @return string
     */
    public function getColorHexa()
    {
        return $this->colorHexa;
    }

    /**
     * Set twitter
     *
     * @param string $twitter
     *
     * @return Programacion
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;

        return $this;
    }

    /**
     * Get twitter
     *
     * @return string
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Programacion
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    /**
     * @var \BackendBundle\Entity\Category
     */
    private $category;

    /**
     * @var \BackendBundle\Entity\Image
     */
    private $imageHost;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $columna;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->columna = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set category
     *
     * @param \BackendBundle\Entity\Category $category
     *
     * @return Programacion
     */
    public function setCategory(\BackendBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \BackendBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set imageHost
     *
     * @param \BackendBundle\Entity\Image $imageHost
     *
     * @return Programacion
     */
    public function setImageHost(\BackendBundle\Entity\Image $imageHost = null)
    {
        $this->imageHost = $imageHost;

        return $this;
    }

    /**
     * Get imageHost
     *
     * @return \BackendBundle\Entity\Image
     */
    public function getImageHost()
    {
        return $this->imageHost;
    }

    /**
     * Add columna
     *
     * @param \BackendBundle\Entity\Columna $columna
     *
     * @return Programacion
     */
    public function addColumna(\BackendBundle\Entity\Columna $columna)
    {
        $this->columna[] = $columna;

        return $this;
    }

    /**
     * Remove columna
     *
     * @param \BackendBundle\Entity\Columna $columna
     */
    public function removeColumna(\BackendBundle\Entity\Columna $columna)
    {
        $this->columna->removeElement($columna);
    }

    /**
     * Get columna
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getColumna()
    {
        return $this->columna;
    }
    /**
     * @var \BackendBundle\Entity\Image
     */
    private $imageTapiz;


    /**
     * Set imageTapiz
     *
     * @param \BackendBundle\Entity\Image $imageTapiz
     *
     * @return Programacion
     */
    public function setImageTapiz(\BackendBundle\Entity\Image $imageTapiz = null)
    {
        $this->imageTapiz = $imageTapiz;

        return $this;
    }

    /**
     * Get imageTapiz
     *
     * @return \BackendBundle\Entity\Image
     */
    public function getImageTapiz()
    {
        return $this->imageTapiz;
    }
    /**
     * @var string
     */
    private $svgChannel;


    /**
     * Set svgChannel
     *
     * @param string $svgChannel
     *
     * @return Programacion
     */
    public function setSvgChannel($svgChannel)
    {
        $this->svgChannel = $svgChannel;

        return $this;
    }

    /**
     * Get svgChannel
     *
     * @return string
     */
    public function getSvgChannel()
    {
        return $this->svgChannel;
    }

    /**
     * @var boolean
     */
    private $active;


    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Programacion
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }
}
