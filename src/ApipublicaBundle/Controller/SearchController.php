<?php
/**
 * Created by PhpStorm.
 * User: javiermorquecho
 * Date: 30/10/17
 * Time: 17:30
 */
namespace ApipublicaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Controller\SearchController as BaseSearchController;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SearchController extends BaseSearchController
{

    /**
     * Para acceder a este metodo, NO se require autorizacion(token)
     * @ApiDoc(
     *     section = "Search",
     *     description="Search by types: page(article,column,blogpost), image, videos",
     *     requirements={
     *     {"name"="json",    "dataType"="array",   "required"=true,    "description"="Conditions: search, min_date, max_date, categoryId(str), author(str), (Only for tags->type:[''|'company']), status( default | published | scheduled ). Don't use special chars and use just one word by criteria in no date type columns."},
     *     {"name"="type",    "dataType"="string",  "required"=true,    "default"="page | image | tag | folds | portada | author | category | flags", "description"="page | image | tag | folds | portada | author | category | flags"},
     *     {"name"="subtype", "dataType"="string",  "required"=true,    "default"="article | column | blogpost | carton | nothing for other type", "description"="article | column | blogpost"},
     *     {"name"="page",    "dataType"="int",     "required"="false", "default"="1",      "description"="Page"},
     *     {"name"="size",    "dataType"="int",     "required"="false", "default"="10",     "description"="Tamaño de la pagina, si se omite es 10"},
     *     {"name"="fromcache","dataType"="string", "required"="false", "default"="false",     "description"="Si es true, es para obtener info de cache local de Backend"},
     *    }
     * )
     */
    public function publicallAction( Request $request ){

        $request->request->set( "public", true );
        
        $json                       = $request->get('json',"");
        $clientConditions           = json_decode($json);
        $publicConditions["status"] = "published";
        $publicConditions           = (object)$publicConditions;
        $conditions                 = (object) array_merge((array) $clientConditions, (array) $publicConditions);
        $request->attributes->set('json', json_encode($conditions));
        $fromcache = $request->get('fromcache',"false");
        $helpers = $this->get("app.helpers");

        if($request->get('type',"")=='image'){
            $msg      = 'Image type not allowed in public searching';
            $data     = $helpers->responseData(400, $msg);
            $response = $helpers->responseHeaders(400, $data);
            return $response;
        }


        //Ya no se presenta el supuesto .cache para las peticiones de busqueda de tv

        $response = $this->allAction( $request );


        return $response;
    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *  section = "Search",
     *  description="Search by all types at the same time",
     *     requirements={
     *     {"name"="json",    "dataType"="array",   "required"=true,    "description"="Conditions props: default, min_date, max_date, categoryId(str), author(str), (Only for tags->type:[''|'company']), status( default | published | scheduled ). Don't use special chars and use just one word by criteria in no date type columns."},
     *     {"name"="page",    "dataType"="int",     "required"="false", "default"="1",      "description"="Page"},
     *     {"name"="size",    "dataType"="int",     "required"="false", "default"="10",     "description"="Tamaño de la pagina, si se omite es 10"},
     *    }
     * )
     */
    public function publicalltypesAction( Request $request ){
        $helpers = $this->get("app.helpers");

        $request->request->set( "public", true );
        $json       = $request->get('json',"");
        $conditions = json_decode($json);
        if ( $conditions->search == "*" || $conditions->search == "" ){
            $msg      = 'No search string';
            $data     = $helpers->responseData(400, $msg);
            $response = $helpers->responseHeaders(400, $data);
        }else{
            $response   = $this->alltypesAction( $request );
        }

        return $response;
    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "Search",
     *     description="Get last news for external calling (nacion321)",
     *     requirements={
     *     {"name"="page",    "dataType"="int",     "required"="false", "default"="1",      "description"="Page"},
     *     {"name"="size",    "dataType"="int",     "required"="false", "default"="10",     "description"="Tamaño de la pagina, si se omite es 10"},
     *    }
     * )
     */
    public function lastForExternalAction( Request $request ){
        $encoders    = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer  = new Serializer($normalizers, $encoders);
        $result      = array();

        $size = $request->query->get('size');
        $request->request->set( "type", "published" );
        $request->request->set( "public", true );
        $request->request->set( "itemsInPage", $size );
        $response = $this->lastNewsAction( $request );

        $data = json_decode( $response->getContent(), true );
        foreach( $data["data"] as $a ){
            $b = array();
            $b["id"]        = $a["_source"]["id"];
            $b["slug"]      = $a["_source"]["slug"];
            $b["title"]     = $a["_source"]["title"];
            $c              = explode( "/", $a["_source"]["mainImage"] );
            if ( isset( $c[2] ) && isset( $c[3] ) && isset( $c[4] ) && isset( $c[5] )  ) {
                $e              = explode( ".", $c[5] );
                $d              = $c[2] . "/" . $c[3] . "/" . $c[4] . "/" . $e[0] . "_smartphone." . $e[1];;
                $b["pathimage"] = $d;
                $e              = explode( ".", $c[5] );
                $b["nameimage"] = $e[0] . "_smartphone." . $e[1];
            } else {
                $b["pathimage"] = "";
                $b["nameimage"] = "";
            }
            $b["src"] = "/uploads/" . $d;
            $result[] = $b;
        }

        $jsonContent = $serializer->serialize($result, 'json');
        $response    = new Response($jsonContent);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    private function _getCached($url)
    {

        $hash = md5(serialize($url));


        $expire = 86400; // $this->container->getParameter('ttl_cachevimeo'); Expiración invalidada (DSG 160318)

        $rutacachetv = $this->container->getParameter('rutacachetv');

        if($this->container->getParameter("kernel.environment")=="prod")
        {
            $file = $this->get('kernel')->getRootDir()."/../". $this->_cache_dir.'/'.$hash.'.cache';

        }
        elseif ($this->container->getParameter("kernel.environment")=="public")
        {
            $file = $this->get('kernel')->getRootDir()."/../../". $rutacachetv .'/'.$hash.'.cache';

        }



        /* if (file_exists($file)) {
             $last_modified = filemtime($file);
             if (substr($file, -6) == '.cache' && ($last_modified + $expire) < time()) {
                 unlink($file);
             }

        }*/

        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true);
        }

    }

    /**
     * Para acceder a este metodo, NO se require autorizacion(token)
     * @ApiDoc(
     *  section = "Search",
     *  description="Legacy news",
     *     requirements={
     *     {"name"="json",    "dataType"="array",   "required"=true,    "description"="Conditions: search, min_date, max_date, categoryId(str), author(str), (Only for tags->type:[''|'company']), portada(only for portadafolds). Don't use special chars and use just one word by criteria in no date type columns."},
     *     {"name"="type",    "dataType"="string",  "required"=true,    "default"="page", "description"="page"},
     *     {"name"="subtype", "dataType"="string",  "required"=true,    "default"="article | blogpost | tv | galeria | estatico | apunte", "description"="article | blogpost | tv | galeria | estatico | apunte"},
     *     {"name"="page",    "dataType"="int",     "required"="false", "default"="1",      "description"="Page"},
     *     {"name"="size",    "dataType"="int",     "required"="false", "default"="10",     "description"="Tamaño de la pagina, si se omite es 10"},
     *    },
     * )
     */
    public function legacyAction( Request $request ){

        $request->request->set( "public", true );

        $json                       = $request->get('json',"");
        $clientConditions           = json_decode($json);
        $publicConditions["status"] = "published";
        $publicConditions           = (object)$publicConditions;
        $conditions                 = (object)$clientConditions;
        $conditions                 = (object) array_merge((array) $clientConditions, (array) $publicConditions);
        $request->attributes->set('json', json_encode($conditions));

        $response = $this->forward(
            'BackendBundle:Search:legacy',
            array(
                'request'  => $request,
            )
        );

        return $response;
    }

}
