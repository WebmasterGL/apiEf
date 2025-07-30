<?php

namespace BackendBundle\Entity;

/**
 * Programaciontv
 */
class Programaciontv
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $desde;

    /**
     * @var \DateTime
     */
    private $hasta;

    /**
     * @var string
     */
    private $programa;

    /**
     * @var string
     */
    private $dia;


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
     * Set desde
     *
     * @param \DateTime $desde
     *
     * @return Programaciontv
     */
    public function setDesde($desde)
    {
        $this->desde = $desde;

        return $this;
    }

    /**
     * Get desde
     *
     * @return \DateTime
     */
    public function getDesde()
    {
        return $this->desde;
    }

    /**
     * Set hasta
     *
     * @param \DateTime $hasta
     *
     * @return Programaciontv
     */
    public function setHasta($hasta)
    {
        $this->hasta = $hasta;

        return $this;
    }

    /**
     * Get hasta
     *
     * @return \DateTime
     */
    public function getHasta()
    {
        return $this->hasta;
    }

    /**
     * Set programa
     *
     * @param string $programa
     *
     * @return Programaciontv
     */
    public function setPrograma($programa)
    {
        $this->programa = $programa;

        return $this;
    }

    /**
     * Get programa
     *
     * @return string
     */
    public function getPrograma()
    {
        return $this->programa;
    }

    /**
     * Set dia
     *
     * @param string $dia
     *
     * @return Programaciontv
     */
    public function setDia($dia)
    {
        $this->dia = $dia;

        return $this;
    }

    /**
     * Get dia
     *
     * @return string
     */
    public function getDia()
    {
        return $this->dia;
    }
}
