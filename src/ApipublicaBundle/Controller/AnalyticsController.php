<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApipublicaBundle\Controller;

/**
 * Description of RssController
 *
 * @author victor.nombret
 */
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Elastica\Query;
use Elastica\Query\QueryString;

class AnalyticsController extends Controller {

    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Google Analytics",
     *     description="Feed de 20 articulos basados en Google Analytics en tiempo real (en los últimos 30 minutos de cada hora)"
     * )
     */
    public function indexAction(Request $request) {
        //data-rt.json for real time results

        $path = __DIR__ . '/../Resources/config/google/analytics/data-rt.json';
        //Check if that file exists
        if (file_exists($path)) {
            $data_from_ga = file_get_contents($path);

            return $this->returnResults(json_decode($data_from_ga));
        } else {
            throw new NotFoundHttpException('File not found');
        }
    }

    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Google Analytics",
     *     description="Feed de 20 articulos basados en Google Analytics en tiempo real en RSS (en los últimos 30 minutos de cada hora)"
     * )
     */
    public function index_rssAction(Request $request) {
        //data-rt.json for real time results

        $path = __DIR__ . '/../Resources/config/google/analytics/data-rt.json';
        //Check if that file exists
        if (file_exists($path)) {
            $data_from_ga = file_get_contents($path);

            return $this->returnResults(json_decode($data_from_ga),null,"xml");
        } else {
            throw new NotFoundHttpException('File not found');
        }

    }

    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Google Analytics",
     *     description="Feed de 20 articulos ó Columnas basados en Google Analytics en tiempo real (en los últimos 30 minutos de cada hora)"
     * )
     */
    public function extendedAction(Request $request) {
        //data-rt.json for real time results
        $path = __DIR__ . '/../Resources/config/google/analytics/data-rt.json';
        //Check if that file exists
        if (file_exists($path)) {
            $data_from_ga = json_decode(file_get_contents($path));

            $count = 0;
            $validas = 1;
            $results_respond = array();
            while ($count <= 49 && $validas<=8) {
                if(isset($data_from_ga[$count]) && $data_from_ga[$count][3]=="column" || $data_from_ga[$count][3]=="article"){
                    $results_respond[] = $data_from_ga[$count][4];
                    $validas++;
                }
                $count++;
            }
            return new JsonResponse($results_respond);
            //return $this->returnResults($data_from_ga, true);
        } else {
            throw new NotFoundHttpException('File not found');
        }
    }
    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Google Analytics",
     *     description="Feed de 20 articulos basados en Google Analytics por sección"
     * )
     */
    public function sectionAction(Request $request, $section) {
        //$em = $this->getDoctrine()->getManager();
        //Check if section exists

        $finder          = $this->get('fos_elastica.index.efredisenio.category');
        $query           = new Query();
        $q_slug = new QueryString();
        $q_slug->setQuery( $section );
        $q_slug->setFields( array( 'slug' ) );

        $query->setQuery($q_slug);
        $hybridResults = $finder->search($query, 1);

        $item = null;
        foreach ($hybridResults as $hybridResult) {
            $item = $hybridResult->getHit();
        }

        //$section_exists = $em->getRepository('BackendBundle:Category')->findBy(array('active' => 1, 'slug' => $section));
        if(count($item)>0) {  //(($section_exists) {
            $path = __DIR__ . '/../Resources/config/google/analytics/' . $section . '.json';
            if (file_exists($path)) {
                $data_from_ga = file_get_contents($path);
                return $this->returnResults(json_decode($data_from_ga));
            } else {
                return new JsonResponse( json_decode ("[]")); //Sustituyendo por el error de Not Found

                //throw new NotFoundHttpException('File not found');
            }
        } else {
            return new JsonResponse( json_decode ('{"Error":true}')); //Sustituyendo por el error de Not Found
            //throw new NotFoundHttpException('Section doesnt exist');
        }
    }

    private function returnResults($results, $extended=false, $formato=null) {

        //LIMIT_OF_FEED_PROCESSED
        $LIMIT = 50;
        $count = 0;
        $validas = 1;
        $tope = $extended ? 8 : 10;


        $results_respond = array();
        while ($count < $LIMIT && $validas<=$tope) {
            //Only articles found, no null values not repeat
            //Send slug and hits

            if (isset($results[$count])) {

                $pre_check=$this->formatElement($results[$count][1], $results[$count][2],$extended,$formato);

                if($pre_check!=false)
                {
                    $results_respond[] = $pre_check; //$this->formatElement($results[$count][1], $results[$count][2],$extended,$formato);


                    $bef_unique=count($results_respond);

                    $results_respond = $this->array_unique_hits_slug($results_respond);

                    $aft_unique=count($results_respond);

                    if($bef_unique==$aft_unique)
                        $validas++;
                }
            }
            //Breaks method if not exists notes
            //$count = count($results_respond);
            $count++;
        }

        if($formato=='xml'){
            $data_header = array(
                'title' => "RSS Most View El Financiero Bloomberg",
                'description' => "RSS from https://www.elfinanciero.com.mx/",
                'url' => $this->container->getParameter('host_uri'),
                'pubDate' => date("D, d M Y H:i:s T"),
            );


            return $this->render('@Apipublica/RSS/mostview.xml.twig', array(
                'data' => $data_header,
                'items' => $results_respond
            ));
        }
        return new JsonResponse($results_respond);
    }

    private function formatElement($slug_url, $hits, $extended=false, $formato=null) {
        $em = $this->getDoctrine()->getManager();
        //format pagePath to slug
        $slug_url = str_replace(".html", "", $slug_url);
        $slug_url = str_replace("https://www.elfinanciero.com.mx/", "", $slug_url); //FIX (13/09/18, DSG , A veces los urls llegan con todo el dominio incluido)
        $slug_url = substr($slug_url, 1);
        //$slug_url = strstr($slug_url, '?', true);
        //GEt record from database with slug
        if($extended)
        {
            $article = $em->getRepository('BackendBundle:Page')->findOneBy(array('slug' => $slug_url, 'status' => 'published', 'mostViewed' => '1', 'portalId' => '3', 'pageType' => array('article','column')));
        }else{
            $article = $em->getRepository('BackendBundle:Page')->findOneBy(array('slug' => $slug_url, 'status' => 'published', 'mostViewed' => '1', 'portalId' => '3', 'pageType' => 'article'));
        }
                if ($article) {
                    if($formato=='xml'){ //Solo los RSS llevan Body
                        //make element
                        return array(
                            "page_id" => $article->getId(),
                            "title" => $article->getTitle(),
                            "slug" => $article->getSlug(),
                            "seo_image" => $article->getMainImage()->getImagePath(),
                            "main_image" => $this->getMainImage($article),
                            "bullet" => ($article->getBullets()) ? $article->getBullets()[0] : NULL,
                            "hits" => $hits,
                            "kicker" => strtoupper($article->getCategoryId()->getTitle()),
                            "kickerSlug" => $article->getCategoryId()->getSlug(),
                            "publishedAt" => $article->getPublishedAt(),
                            "content"=>$article->getHtml()
                        );
                    }
                    else{
                        //make element
                        return array(
                            "page_id" => $article->getId(),
                            "title" => $article->getTitle(),
                            "slug" => $article->getSlug(),
                            "seo_image" => $article->getMainImage()->getImagePath(),
                            "main_image" => $this->getMainImage($article),
                            "bullet" => ($article->getBullets()) ? $article->getBullets()[0] : NULL,
                            "hits" => $hits,
                            "kicker" => strtoupper($article->getCategoryId()->getTitle()),
                            "kickerSlug" => $article->getCategoryId()->getSlug(),
                            "publishedAt" => $article->getPublishedAt()
                        );
                    }

        } else {
            return false;
        }
    }

    private function array_unique_hits_slug($input) {
        //iterate on array
        foreach ($input as $key1 => $item_to_compare){
            //iterate on same array
            foreach($input as $key2 => $item_to_delete){
                //Delete if slug is equals and differents keys 
                if($item_to_compare['slug'] == $item_to_delete['slug'] && $key1 != $key2 && $key2 > $key1){
                    unset($input[$key2]);
                }
            }
        }
        return $input;
    }
    
    

    private function getMainImage($article) {
        $json = json_decode($article->getElementHtmlSerialized(), true);
        if ($json['type'] == 'image' && $json['layout'] != "no-display") {
            return $json['data']['imagePath'];
        } else {
            return $article->getMainImage()->getImagePath();
        }
    }

}
