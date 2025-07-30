<?php

namespace BackendBundle\Entity;

/**
 * Columna
 */
class Columna
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $nombreSistema;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $authors;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->authors = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Columna
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Columna
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set nombreSistema
     *
     * @param string $nombreSistema
     *
     * @return Columna
     */
    public function setNombreSistema($nombreSistema)
    {
        $this->nombreSistema = $nombreSistema;

        return $this;
    }

    /**
     * Get nombreSistema
     *
     * @return string
     */
    public function getNombreSistema()
    {
        return $this->nombreSistema;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Columna
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

    /**
     * Add authors
     *
     * @param \BackendBundle\Entity\Author $authors
     *
     * @return Columna
     */
    public function addAuthors(\BackendBundle\Entity\Author $authors)
    {
        $this->authors[] = $authors;

        return $this;
    }

    /**
     * Remove authors
     *
     * @param \BackendBundle\Entity\Author $authors
     */
    public function removeAuthors(\BackendBundle\Entity\Author $authors)
    {
        $this->authors->removeElement($authors);
    }

    /**
     * Get authors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    public function __toString() {
        $a = array(
            "id"   => $this->id,
            "name" => $this->nombre,
            "slug" => $this->slug
        );

        return json_encode( $a, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT );
    }
    /**
     * @var array
     */
    private $seo;

    /**
     * @var array
     */
    private $social;

    /**
     * @var \BackendBundle\Entity\Image
     */
    private $image;


    /**
     * Set seo
     *
     * @param array $seo
     *
     * @return Columna
     */
    public function setSeo($seo)
    {
        $this->seo = $seo;

        return $this;
    }

    /**
     * Get seo
     *
     * @return array
     */
    public function getSeo()
    {
        return $this->seo;
    }

    /**
     * Set social
     *
     * @param array $social
     *
     * @return Columna
     */
    public function setSocial($social)
    {
        $this->social = $social;

        return $this;
    }

    /**
     * Get social
     *
     * @return array
     */
    public function getSocial()
    {
        return $this->social;
    }

    /**
     * Set image
     *
     * @param \BackendBundle\Entity\Image $image
     *
     * @return Columna
     */
    public function setImage(\BackendBundle\Entity\Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \BackendBundle\Entity\Image
     */
    public function getImage()
    {
        return $this->image;
    }



    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var \DateTime
     */
    private $activatedAt;


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Columna
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Columna
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

    /**
     * Set activatedAt
     *
     * @param \DateTime $activatedAt
     *
     * @return Columna
     */
    public function setActivatedAt($activatedAt)
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    /**
     * Get activatedAt
     *
     * @return \DateTime
     */
    public function getActivatedAt()
    {
        return $this->activatedAt;
    }

    /**
     * Add author
     *
     * @param \BackendBundle\Entity\Author $author
     *
     * @return Columna
     */
    public function addAuthor(\BackendBundle\Entity\Author $author)
    {
        $this->authors[] = $author;

        return $this;
    }

    /**
     * Remove author
     *
     * @param \BackendBundle\Entity\Author $author
     */
    public function removeAuthor(\BackendBundle\Entity\Author $author)
    {
        $this->authors->removeElement($author);
    }
}
