<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;
class GoogleApiController extends Controller
{

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     *
     * @ApiDoc(
     *  section = "Google Search Places",
     *  description="Busqueda de un Lugar a través de Api Google Places",
     *   requirements={
     *      {"name"="query", "dataType"="string", "required"="false", "description"="Escribe un lugar a buscar"}
     *   },
     *   headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     * )
     */
    public function searchAction($query){
       $apiKey = $this->container->getParameter('apiKeyGooglePlaces');

        $query = str_replace(' ', '%20', $query);

       $url = 'https://maps.googleapis.com/maps/api/place/textsearch/json?query='. $query .'&key='.$apiKey;

        return $this->executeUrl($url);
    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     *
     * @ApiDoc(
     *  section = "Google Search Places",
     *  description="Se obtienen detalles de un place_id, es necesario ejecutar el search antes para obtenerlo",
     *   requirements={
     *      {"name"="place_id", "dataType"="string", "required"="false", "description"="Escribe el place_id deseado"}
     *   },
     *   headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     * )
     */
    public function getDetailsAction($place_id){
        $apiKey = $this->container->getParameter('apiKeyGooglePlaces');

        $url = 'https://maps.googleapis.com/maps/api/place/details/json?placeid='. $place_id .'&key='.$apiKey;

        return $this->executeUrl($url);

    }

    private function executeUrl($url){


        $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_USERAGENT      => "spider", // who am i
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10
        );

        $ch = curl_init($url);
        curl_setopt_array( $ch, $options );
        $content = curl_exec( $ch );

        curl_close( $ch );



        $response = new Response();

        $response->setContent($content);

        $response->headers->set("Content-Type", "application/json");

        return $response;

    }
}
