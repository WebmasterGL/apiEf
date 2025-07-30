<?php

namespace XalokBundle\Entity;

//use Doctrine\ORM\Mapping as ORM;

/**
 * StaticPage
 *
 * @ORM\Table(name="static_page")
 * @ORM\Entity
 */
class StaticPage
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"comment":"Identificador único de la tabla", "unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=75, nullable=false)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=75, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="string", nullable=false)
     */
    private $text;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return StaticPage
     */
    public function setSlug($slug) {
        $this->slug = $slug;
        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug() {
        return $this->slug;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return StaticPage
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return StaticPage
     */
    public function setText($text) {
        $this->text = $text;
        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText() {
        return $this->text;
    }

    public function __toString() {
        return $this->getText();
    }

}