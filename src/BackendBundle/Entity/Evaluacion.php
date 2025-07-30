<?php

namespace BackendBundle\Entity;

/**
 * Evaluacion
 */
class Evaluacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $calificacion;

    /**
     * @var \DateTime
     */
    private $fchEvaluacion;

    /**
     * @var \DateTime
     */
    private $anioEvaluacion;

    /**
     * @var boolean
     */
    private $mesEvaluacion;

    /**
     * @var \BackendBundle\Entity\Page
     */
    private $page;

    /**
     * @var \BackendBundle\Entity\Personaje
     */
    private $personaje;


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
     * Set calificacion
     *
     * @param boolean $calificacion
     *
     * @return Evaluacion
     */
    public function setCalificacion($calificacion)
    {
        $this->calificacion = $calificacion;

        return $this;
    }

    /**
     * Get calificacion
     *
     * @return boolean
     */
    public function getCalificacion()
    {
        return $this->calificacion;
    }

    /**
     * Set fchEvaluacion
     *
     * @param \DateTime $fchEvaluacion
     *
     * @return Evaluacion
     */
    public function setFchEvaluacion($fchEvaluacion)
    {
        $this->fchEvaluacion = $fchEvaluacion;

        return $this;
    }

    /**
     * Get fchEvaluacion
     *
     * @return \DateTime
     */
    public function getFchEvaluacion()
    {
        return $this->fchEvaluacion;
    }

    /**
     * Set anioEvaluacion
     *
     * @param \DateTime $anioEvaluacion
     *
     * @return Evaluacion
     */
    public function setAnioEvaluacion($anioEvaluacion)
    {
        $this->anioEvaluacion = $anioEvaluacion;

        return $this;
    }

    /**
     * Get anioEvaluacion
     *
     * @return \DateTime
     */
    public function getAnioEvaluacion()
    {
        return $this->anioEvaluacion;
    }

    /**
     * Set mesEvaluacion
     *
     * @param boolean $mesEvaluacion
     *
     * @return Evaluacion
     */
    public function setMesEvaluacion($mesEvaluacion)
    {
        $this->mesEvaluacion = $mesEvaluacion;

        return $this;
    }

    /**
     * Get mesEvaluacion
     *
     * @return boolean
     */
    public function getMesEvaluacion()
    {
        return $this->mesEvaluacion;
    }

    /**
     * Set page
     *
     * @param \BackendBundle\Entity\Page $page
     *
     * @return Evaluacion
     */
    public function setPage(\BackendBundle\Entity\Page $page = null)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page
     *
     * @return \BackendBundle\Entity\Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set personaje
     *
     * @param \BackendBundle\Entity\Personaje $personaje
     *
     * @return Evaluacion
     */
    public function setPersonaje(\BackendBundle\Entity\Personaje $personaje = null)
    {
        $this->personaje = $personaje;

        return $this;
    }

    /**
     * Get personaje
     *
     * @return \BackendBundle\Entity\Personaje
     */
    public function getPersonaje()
    {
        return $this->personaje;
    }
}
