<?php

namespace BackendBundle\Entity;

/**
 * EeEncuesta
 */
class EeEncuesta
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $prcBruto = '0';

    /**
     * @var integer
     */
    private $efectivo = '0';

    /**
     * @var \DateTime
     */
    private $periodoini;

    /**
     * @var \DateTime
     */
    private $periodofin;

    /**
     * @var \DateTime
     */
    private $fchcaptura;

    /**
     * @var string
     */
    private $metodologia;

    /**
     * @var integer
     */
    private $muestra;

    /**
     * @var float
     */
    private $prcError;

    /**
     * @var \BackendBundle\Entity\EeCandidato
     */
    private $eeCandidato;

    /**
     * @var \BackendBundle\Entity\EeEncuestadora
     */
    private $eeEncuestadora;

    /**
     * @var \BackendBundle\Entity\EeTipoencuesta
     */
    private $eeTipoencuesta;


    /**
     * Set id
     *
     * @param integer $id
     *
     * @return EeEncuesta
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set prcBruto
     *
     * @param integer $prcBruto
     *
     * @return EeEncuesta
     */
    public function setPrcBruto($prcBruto)
    {
        $this->prcBruto = $prcBruto;

        return $this;
    }

    /**
     * Get prcBruto
     *
     * @return integer
     */
    public function getPrcBruto()
    {
        return $this->prcBruto;
    }

    /**
     * Set efectivo
     *
     * @param integer $efectivo
     *
     * @return EeEncuesta
     */
    public function setEfectivo($efectivo)
    {
        $this->efectivo = $efectivo;

        return $this;
    }

    /**
     * Get efectivo
     *
     * @return integer
     */
    public function getEfectivo()
    {
        return $this->efectivo;
    }

    /**
     * Set periodoini
     *
     * @param \DateTime $periodoini
     *
     * @return EeEncuesta
     */
    public function setPeriodoini($periodoini)
    {
        $this->periodoini = $periodoini;

        return $this;
    }

    /**
     * Get periodoini
     *
     * @return \DateTime
     */
    public function getPeriodoini()
    {
        return $this->periodoini;
    }

    /**
     * Set periodofin
     *
     * @param \DateTime $periodofin
     *
     * @return EeEncuesta
     */
    public function setPeriodofin($periodofin)
    {
        $this->periodofin = $periodofin;

        return $this;
    }

    /**
     * Get periodofin
     *
     * @return \DateTime
     */
    public function getPeriodofin()
    {
        return $this->periodofin;
    }

    /**
     * Set fchcaptura
     *
     * @param \DateTime $fchcaptura
     *
     * @return EeEncuesta
     */
    public function setFchcaptura($fchcaptura)
    {
        $this->fchcaptura = $fchcaptura;

        return $this;
    }

    /**
     * Get fchcaptura
     *
     * @return \DateTime
     */
    public function getFchcaptura()
    {
        return $this->fchcaptura;
    }

    /**
     * Set metodologia
     *
     * @param string $metodologia
     *
     * @return EeEncuesta
     */
    public function setMetodologia($metodologia)
    {
        $this->metodologia = $metodologia;

        return $this;
    }

    /**
     * Get metodologia
     *
     * @return string
     */
    public function getMetodologia()
    {
        return $this->metodologia;
    }

    /**
     * Set muestra
     *
     * @param integer $muestra
     *
     * @return EeEncuesta
     */
    public function setMuestra($muestra)
    {
        $this->muestra = $muestra;

        return $this;
    }

    /**
     * Get muestra
     *
     * @return integer
     */
    public function getMuestra()
    {
        return $this->muestra;
    }

    /**
     * Set prcError
     *
     * @param float $prcError
     *
     * @return EeEncuesta
     */
    public function setPrcError($prcError)
    {
        $this->prcError = $prcError;

        return $this;
    }

    /**
     * Get prcError
     *
     * @return float
     */
    public function getPrcError()
    {
        return $this->prcError;
    }

    /**
     * Set eeCandidato
     *
     * @param \BackendBundle\Entity\EeCandidato $eeCandidato
     *
     * @return EeEncuesta
     */
    public function setEeCandidato(\BackendBundle\Entity\EeCandidato $eeCandidato = null)
    {
        $this->eeCandidato = $eeCandidato;

        return $this;
    }

    /**
     * Get eeCandidato
     *
     * @return \BackendBundle\Entity\EeCandidato
     */
    public function getEeCandidato()
    {
        return $this->eeCandidato;
    }

    /**
     * Set eeEncuestadora
     *
     * @param \BackendBundle\Entity\EeEncuestadora $eeEncuestadora
     *
     * @return EeEncuesta
     */
    public function setEeEncuestadora(\BackendBundle\Entity\EeEncuestadora $eeEncuestadora = null)
    {
        $this->eeEncuestadora = $eeEncuestadora;

        return $this;
    }

    /**
     * Get eeEncuestadora
     *
     * @return \BackendBundle\Entity\EeEncuestadora
     */
    public function getEeEncuestadora()
    {
        return $this->eeEncuestadora;
    }

    /**
     * Set eeTipoencuesta
     *
     * @param \BackendBundle\Entity\EeTipoencuesta $eeTipoencuesta
     *
     * @return EeEncuesta
     */
    public function setEeTipoencuesta(\BackendBundle\Entity\EeTipoencuesta $eeTipoencuesta = null)
    {
        $this->eeTipoencuesta = $eeTipoencuesta;

        return $this;
    }

    /**
     * Get eeTipoencuesta
     *
     * @return \BackendBundle\Entity\EeTipoencuesta
     */
    public function getEeTipoencuesta()
    {
        return $this->eeTipoencuesta;
    }
}
