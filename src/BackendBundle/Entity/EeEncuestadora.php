<?php

namespace BackendBundle\Entity;

/**
 * EeEncuestadora
 */
class EeEncuestadora
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $encuestadora;

    /**
     * @var boolean
     */
    private $estatus;


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
     * Set encuestadora
     *
     * @param string $encuestadora
     *
     * @return EeEncuestadora
     */
    public function setEncuestadora($encuestadora)
    {
        $this->encuestadora = $encuestadora;

        return $this;
    }

    /**
     * Get encuestadora
     *
     * @return string
     */
    public function getEncuestadora()
    {
        return $this->encuestadora;
    }

    /**
     * Set estatus
     *
     * @param boolean $estatus
     *
     * @return EeEncuestadora
     */
    public function setEstatus($estatus)
    {
        $this->estatus = $estatus;

        return $this;
    }

    /**
     * Get estatus
     *
     * @return boolean
     */
    public function getEstatus()
    {
        return $this->estatus;
    }
}
