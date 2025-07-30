<?php

namespace BackendBundle\Entity;

/**
 * Folds
 */
class Folds
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var \BackendBundle\Entity\Tipofolds
     */
    private $idtipo;

    /**
     * @var \BackendBundle\Entity\Category
     */
    private $category;


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
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return Folds
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set idtipo
     *
     * @param \BackendBundle\Entity\Tipofolds $idtipo
     *
     * @return Folds
     */
    public function setIdtipo(\BackendBundle\Entity\Tipofolds $idtipo = null)
    {
        $this->idtipo = $idtipo;

        return $this;
    }

    /**
     * Get idtipo
     *
     * @return \BackendBundle\Entity\Tipofolds
     */
    public function getIdtipo()
    {
        return $this->idtipo;
    }

    /**
     * Set category
     *
     * @param \BackendBundle\Entity\Category $category
     *
     * @return Folds
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
        return $this->category->getId();
    }

    public function __toString()
    {
        $a = array(
            "id"          => $this->id,
            "descripcion" => $this->descripcion,
            "tipo"        => json_decode( $this->idtipo, true ),
            "category"    => json_decode( $this->category, true )
        );

        return json_encode( $a, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT );
    }
}
