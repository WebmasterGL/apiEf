<?php

namespace ApipublicaBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Controller\VimeoController as BaseVimeoController;

class VimeoController extends BaseVimeoController
{

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "Vimeo",
     *     description="Busca los videos que coinciden con la cadena enviada",
     *     requirements={
     *      {"name"="query", "dataType"="string", "required"=true, "description"="Cadena a buscar en los videos de la plataforma de Vimeo"},
     *      {"name"="page", "dataType"="integer", "default"=1, "required"=true, "description"="Cadena a buscar en los videos de la plataforma de Vimeo"},
     *      {"name"="sizepage", "dataType"="integer", "default"=10, "required"=true, "description"="Tamañoo de la página, 100 como máximo"}
     *    }
     * )
     */
    public function searchAction($query,$page,$sizepage){

        $response = parent::searchAction($query,$page,$sizepage);

        return $response;

    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "Vimeo",
     *     description="Consultando info de Vimeo",
     *     requirements={
     *      {"name"="page", "dataType"="integer", "default"=1, "required"=true, "description"="Cadena a buscar en los videos de la plataforma de Vimeo"},
     *      {"name"="sizepage", "dataType"="integer", "default"=10, "required"=true, "description"="Tamañoo de la página, 100 como máximo"}
    *    }
     * )
     */
    public function getListAction($page,$sizepage){
        $response = parent::getListAction($page,$sizepage);

        return $response;
    }


    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "Vimeo",
     *     description="Consultando video x ID de Vimeo",
     *     requirements={
     *      {"name"="videoID", "dataType"="integer", "required"=true, "description"="ID de Vimeo"}
     *    }
     * )
     */
    public function getVideoIdAction($videoID){
        $response = parent::getVideoIdAction($videoID);

        return $response;
    }

}
