<?php
/**
 * Created by PhpStorm.
 * User: jmorquecho
 * Date: 23/10/18
 * Time: 12:57
 */

namespace ApipublicaBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use BackendBundle\Controller\YoutubeController as BaseYoutubeController;

class YoutubeController extends BaseYoutubeController
{
    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "Youtube",
     *     description="Consultando Youtube",
     *     requirements={
     *      {"name"="maxResults", "dataType"="integer", "default"=10, "required"=true, "description"="Tamañoo de la página, 100 como máximo"}
     *    }
     * )
     */
    public function getListAction( $maxResults ){
        $response = parent::getListAction( $maxResults, null );

        return $response;
    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "Youtube",
     *     description="Busca los videos que coinciden con la cadena enviada",
     *     requirements={
     *      {"name"="query",      "dataType"="string", "required"=true, "description"="Cadena a buscar en los videos de la plataforma de Vimeo"},
     *      {"name"="maxResults", "dataType"="integer", "default"=10,   "required"=true, "description"="Tamaño de la página, 100 como máximo"}
     *    }
     * )
     */
    public function searchAction(Request $request)
    {
        $response = parent::searchVideoAction($request);

        return $response;
    }
}