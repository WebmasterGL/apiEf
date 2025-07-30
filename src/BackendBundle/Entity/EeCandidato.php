<?php

namespace BackendBundle\Entity;

/**
 * EeCandidato
 */
class EeCandidato
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $candidato;

    /**
     * @var string
     */
    private $foto;

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
     * Set candidato
     *
     * @param string $candidato
     *
     * @return EeCandidato
     */
    public function setCandidato($candidato)
    {
        $this->candidato = $candidato;

        return $this;
    }

    /**
     * Get candidato
     *
     * @return string
     */
    public function getCandidato()
    {
        return $this->candidato;
    }

    /**
     * Set foto
     *
     * @param string $foto
     *
     * @return EeCandidato
     */
    public function setFoto($foto)
    {
        $this->foto = $foto;

        return $this;
    }

    /**
     * Get foto
     *
     * @return string
     */
    public function getFoto()
    {
        return $this->foto;
    }

    /**
     * Set estatus
     *
     * @param boolean $estatus
     *
     * @return EeCandidato
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
