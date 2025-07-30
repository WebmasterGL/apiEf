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
use Symfony\Component\HttpFoundation\Request;

class RssController extends Controller {

    //base url EF
    private $host_ef;
    private $uri_api_publica;
    private $host_api;
       
    private function setHostEF($uri){
        $this->host_ef = $uri;
    }

    private function setApiEF($uri){
        $this->host_api = $uri;
    }
    
    private function setApiPublica($uri){
        $this->uri_api_publica = $uri;
    }
    
    private function getHostEF(){
        return $this->host_ef;
    }

    private function getApiEF(){
        return $this->host_api;
    }
    
    private function getApiPublica(){
        return $this->uri_api_publica;
    }
    
    private function loadParameters(){
        $this->setHostEF($this->container->getParameter('host_uri'));
        $this->setApiEF($this->container->getParameter('api_uri'));
        $this->setApiPublica($this->container->getParameter('api_publica_uri'));
    }

    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Feed RSS",
     *     description="Genera el feed de articulos del día"
     * )
     */
    public function indexAction(Request $request) {
        /*$this->loadParameters();
        $data = $this->getData($this->getApiEF().$this->getApiPublica()
                . '/public/search/typed/'
                . '?_format=json&'
                . 'json={"search":"*",'
                . '"min_date":"' . date('d-m-Y') . '",'
                . '"max_date":"' . date('d-m-Y') . '",'
                . '"portalId":"3"}'
                . '&type=page&subtype=article&'
                . 'page=1&size=500');*/

        $data = $this->forward('ApipublicaBundle:Search:publicall', array(
            'json'  => '{"search":"*", "min_date":"' . date('d-m-Y') . '", "max_date":"' . date('d-m-Y') . '", "portalId":"3" }',
            'type' => 'page',
            'subtype' => 'article',
            'page'=>1,
            'size'=>500
        ));

        $json_data = json_decode($data->getContent(), true);
        $items = $this->createItemsJSonRSS($json_data);
        $data_header = array(
            'title' => "RSS El Financiero Bloomberg",
            'description' => "RSS from https://www.elfinanciero.com.mx/",
            'url' => $this-> getHostEF(),
            'pubDate' => date("D, d M Y H:i:s T"),
        );
        //Get and transform data as items for feed
        return $this->render('@Apipublica/RSS/index.xml.twig', array(
                    'data' => $data_header,
                    'items' => $items
        ));
    }

    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Feed RSS",
     *     description="Genera el feed de articulos AEF del día"
     * )
     */
    public function aefAction(Request $request) {
        /*$this->loadParameters();

        $data = $this->getData($this->getApiEF().$this->getApiPublica()
                . '/public/search/typed/'
                . '?_format=json&'
                . 'json={"search":"*","rss":"AEF",'
                . '"min_date":"' . date('d-m-Y') . '",'
                . '"max_date":"' . date('d-m-Y') . '",'
                . '"portalId":"3"}'
                . '&type=page&subtype=article&'
                . 'page=1&size=500');*/


        $data = $this->forward('ApipublicaBundle:Search:publicall', array(
            'json'  => '{"search":"*","rss":"AEF", "min_date":"' . date('d-m-Y') . '", "max_date":"' . date('d-m-Y') . '", "portalId":"3" }',
            'type' => 'page',
            'subtype' => 'article',
            'page'=>1,
            'size'=>500
        ));



        $json_data = json_decode($data->getContent(), true);
        $data_header = array(
            'title' => "RSS AEF El Financiero Bloomberg",
            'description' => "RSS from https://www.elfinanciero.com.mx/",
            'url' => $this->getHostEF(),
            'pubDate' => date("D, d M Y H:i:s T"),
        );
        $items = $this->createItemsJSonRSS($json_data);
        return $this->render('@Apipublica/RSS/index.xml.twig', array(
                    'data' => $data_header,
                    'items' => $items
        ));
    }

    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Feed RSS",
     *     description="Genera el feed de articulos TTV del día"
     * )
     */
    public function ttvAction(Request $request) {
        /*$this->loadParameters();
        $data = $this->getData($this->getApiEF().$this->getApiPublica()
                . '/public/search/typed/'
                . '?_format=json&'
                . 'json={"search":"*","rss":"TTV",'
                . '"min_date":"' . date('d-m-Y') . '",'
                . '"max_date":"' . date('d-m-Y') . '",'
                . '"portalId":"3"}'
                . '&type=page&subtype=article&'
                . 'page=1&size=500');*/

        $data = $this->forward('ApipublicaBundle:Search:publicall', array(
            'json'  => '{"search":"*","rss":"TTV", "min_date":"' . date('d-m-Y') . '", "max_date":"' . date('d-m-Y') . '", "portalId":"3" }',
            'type' => 'page',
            'subtype' => 'article',
            'page'=>1,
            'size'=>500
        ));


        $json_data = json_decode($data->getContent(), true);
        $data_header = array(
            'title' => "RSS TTV El Financiero Bloomberg",
            'description' => "RSS from https://www.elfinanciero.com.mx/",
            'url' => $this-> getHostEF(),
            'pubDate' => date("D, d M Y H:i:s T"),
        );
        $items = $this->createItemsJSonRSS($json_data);
        return $this->render('@Apipublica/RSS/index.xml.twig', array(
                    'data' => $data_header,
                    'items' => $items
        ));
    }

    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Feed RSS",
     *     description="Genera el feed de articulos Opinión del día"
     * )
     */
    public function opinionAction(Request $request) {

        /*$this->loadParameters();
        $data = $this->getData($this->getApiEF().$this->getApiPublica()
                . '/public/search/typed/'
                . '?_format=json&'
                . 'json={"search":"*",'
                . '"min_date":"' . date('d-m-Y') . '",'
                . '"max_date":"' . date('d-m-Y') . '",'
                . '"portalId":"3"}'
                . '&type=page&subtype=column&'
                . 'page=1&size=500');*/

        $data = $this->forward('ApipublicaBundle:Search:publicall', array(
            'json'  => '{"search":"*","min_date":"' . date('d-m-Y') . '", "max_date":"' . date('d-m-Y') . '", "portalId":"3" }',
            'type' => 'page',
            'subtype' => 'column',
            'page'=>1,
            'size'=>500
        ));

        $json_data = json_decode($data->getContent(), true);
        $data_header = array(
            'title' => "RSS AEF El Financiero Bloomberg",
            'description' => "RSS from https://www.elfinanciero.com.mx/",
            'url' => $this-> getHostEF(),
            'pubDate' => date("D, d M Y H:i:s T"),
        );
        $items = $this->createItemsJSonRSS($json_data);
        return $this->render('@Apipublica/RSS/index.xml.twig', array(
                    'data' => $data_header,
                    'items' => $items
        ));
    }
    //Get results from endpoint search. 
    private function getData($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);

        curl_close($ch);
        return $result;
    }

    private function createItemsJSonRSS($articles) {
        $items = array();
        //if array of notes is not empty 
        if ($articles['data'][1]) {
            $array_of_notes = $articles['data'][1];
            foreach ($array_of_notes as $article) {
                $page_data = $article['_source'];
                $item = array();
                $item['title'] = $page_data['title'];
                $item['description'] = $page_data['shortDescription'];
                $item['pubDate'] = date('r', strtotime($page_data['updatedAt']));
                $item['guid'] = $this->getHostEF() . $page_data['slug'];
                $item['link'] = $this->getHostEF() . $page_data['slug'];
                $item['enclosure'] = $this->getHostEF() . str_replace('/uploads/', 'uploads/', $page_data['mainImage']);
                $item['category'] = $page_data['categoryId']['title'];
                $item['categorySlug'] = $page_data['categoryId']['slug'];
                $item['articleSlug'] = $page_data['slug'];
                $item['content'] = html_entity_decode($page_data['html']);
                $item['author'] = $page_data['author'][0]['name'];
                $items[] = $item;
            }
            return $items;
        }
    }

    private function getAuthors($author, $detail_author) {
        if (!is_array($detail_author)) {
            $mod_authors = json_decode($detail_author, true)['authorsModified'];
        } else {
            $mod_authors = $detail_author['authorsModified'];
        }
        //If editorial is true set 'Redacción'
        if (isset($detail_author[0]['editorial'])) {
            $auth_name = "Redacción";
        } else {
            //If enviado is true set 'Enviado' before name author
            $em = $this->getDoctrine()->getManager();
            $author = $em->getRepository('BackendBundle:Author')->find($author[0]);
            if ($mod_authors[0]['enviado']) {
                $auth_name = "Enviado " . $author->getName() . " " . $author->getAPaterno();
            } else {
                $auth_name = $author->getName() . " " . $author->getAPaterno();
            }
        }
        return $auth_name;
    }

    private function getSizeImage($path_image) {
        return filesize($path_image);
    }

    private function getTypeImage($path_image) {
        if (exif_imagetype($path_image) == IMAGETYPE_JPEG) {
            return "image/jpeg";
        } elseif (exif_imagetype($path_image) == IMAGETYPE_PNG) {
            return "image/png";
        } else {
            return "image/jpeg";
        }
    }

}
