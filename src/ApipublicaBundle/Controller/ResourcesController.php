<?php

namespace ApipublicaBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;

// Import required classes
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;

class ResourcesController extends Controller
{
    var $ga = array(
        "v"   => 1,                                             //version
        "t"   => "pageview",                                    //hit's type
        "tid" => "UA-112838768-1",                              //EF account
        "cid" => "1ac3b8f5-b0d9-4c95-b93e-ff29193a7130",        //client id
        "an"  => "APIHitterTest",                               //app name
        "cd"  => "APICallTest"                                  //screen name
    );

    var $site = "https://www.elfinanciero.com.mx/";

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "Resources",
     *     description="Sirve recursos de acuerdo a lo solicitado",
     *     requirements={
     *      {"name"="type", "dataType"="string", "required"=true, "default"="file", "description"="Tipo de recurso a obtener"},
     *      {"name"="path", "dataType"="string", "required"=false, "default"="/", "description"="Path de archivo"}
     *    }
     * )
     */
    public function getAction( Request $request ){
        $type = $request->get('type',"");
        $path = $request->get('path',"");

        $this->ga_hitter( $path );                                                  //Google Analytics hitter

        switch( $type ){
            case "file":
                return $this->redirect($this->site . $path );
                break;
            default:
                return "No data";
        }
    }

    /*
     * @desc return standard ga hitter config
     * @author jmm
     * @params path
     * */
    private function ga_hitter( $path ){
        $ga       = $this->ga;
        $ga["dp"] = $path;
        $ga       = http_build_query( $ga );
        $ch       = curl_init();

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        curl_setopt($ch,CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_URL,"https://www.google-analytics.com/collect");
        curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-type: application/x-www-form-urlencoded'));
        curl_setopt($ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
        curl_setopt($ch,CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $ga );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $server_output = curl_exec ($ch);

        curl_close ($ch);

        return($server_output);
    }
}
