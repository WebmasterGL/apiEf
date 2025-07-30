<?php

namespace BackendBundle\Entity;

/**
 * EeTipoencuesta
 */
class EeTipoencuesta
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $tipoencuesta;

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
     * Set tipoencuesta
     *
     * @param string $tipoencuesta
     *
     * @return EeTipoencuesta
     */
    public function setTipoencuesta($tipoencuesta)
    {
        $this->tipoencuesta = $tipoencuesta;

        return $this;
    }

    /**
     * Get tipoencuesta
     *
     * @return string
     */
    public function getTipoencuesta()
    {
        return $this->tipoencuesta;
    }

    /**
     * Set estatus
     *
     * @param boolean $estatus
     *
     * @return EeTipoencuesta
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
