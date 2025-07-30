<?php

namespace ApipublicaBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use BackendBundle\Controller\SearchController as BaseSearchController;
use Symfony\Component\Validator\Validation;
/*use Elastica\Query;
use Elastica\Query\QueryString;
use Elastica\Query\BoolQuery;*/
class PageController extends BaseSearchController
{

    /**
     * Para acceder a este metodo, no se require autorizacion(token)
     * @ApiDoc(
     *     section = "Notas",
     *     description="Recuperar una nota por su slug, si no existe se busca en Xalok antiguo",
     *     requirements={
     *      {"name"="slug", "dataType"="string", "required"=true, "description"="Slug"}
     *    }
     * )
     */

    public function getSlugAction(Request $request, $p_slug = null)
    {

        $helpers = $this->get("app.helpers");

        if ($p_slug == null)
            $slug = $request->get("slug");
        else
            $slug = $p_slug;

        $em = $this->getDoctrine()->getManager();

        //get client Ip
        $client_ip = $request->getClientIp();

        $find_key = '.html';
        $pos = strpos($slug, $find_key);

        if ($pos !== false) {
            return $this->formatSlugActual($slug, $client_ip);
        } else {

            /*$finder          = $this->get('fos_elastica.index.efredisenio.page');
            $query           = new Query();
            $q_slug = new Query\Match();
            $q_status = new Query\Match();
            $boolQuery       = new BoolQuery();

            $q_slug->setFieldQuery('slug', $slug);
            $boolQuery->addMust($q_slug);

            $q_status->setFieldQuery('status',"published");
            $boolQuery->addMust($q_status);


            $query->setQuery($boolQuery);
            $notaElastica = $finder->search($query, 1);

            $entity_es=null;
            foreach ($notaElastica as $laNota) {

                $entity_es = $laNota->getHit();

            }*/

            $entity = $em->getRepository('BackendBundle:Page')->findOneBy(array(
                'slug' => $slug,
                'status' => 'published'
            ));

            if (count($entity) > 0) {
                if ( $entity->getPortalId()!= 1) { //$entity->getPortalId()  $entity_es["_source"]["portalId"]
                    $data = array(
                        "status" => "success",
                        "origen" => "redisenio",
                        "data" => $helpers->jsonObjeto($entity, $flag = "page", $client_ip)
                    );
                } else {
                    $data = array(
                        "status" => "success",
                        "origen" => "xalok",
                        "data" => $this->formatPageXalok($entity, $flag = 'xalok_redisenio', $client_ip)
                    );
                }


                /*$response = new Response(); //respuesta http
                $response->setPublic();
                $response->setContent(json_encode($data));
                $response->headers->set("Content-Type", "application/json");
                return $response;*/

                return $helpers->json($data, true); //Aqui enviando data a serialize , lo que genera mas de 10 queries a la bd
            } else {
                //Si el primer piso es 'opinion'...
                $first_level = strstr($slug, '/', true);                            //Obtiene el string hasta antes del "/"
                $string_incomplete = strstr($slug, '/');                                        //Obtiene todos los caracteres despues del "/"
                $second_level = str_replace(['/', '.html'], '', $string_incomplete);            //Reemplaza el "/" y el ".html" a vacio
                if ($first_level == 'opinion') {
                    //Resultado de la busqueda en redisenio a traves del segundo nivel del slug de legacy
                    $page_filter = $this->broadPublic("page", "column", "{\"slug2\":\"" . $second_level . "\"}", 1, 10, true, 10);

                    //Ahora seguro encontró como máximo 100 resultados, en los cuales se busca el second_level exacto al 3er piso de cada item encontrado (DSG, 18/10/2018)

                    foreach ($page_filter as $pageTest)
                    {
                        $pisos_slug = explode("/",$pageTest->getSlug());
                        if (count($pisos_slug)==3 && $pisos_slug[2] == $second_level){
                                $page_filter[0] = $pageTest;
                                break;
                        }
                    }

                    if(!is_null($page_filter)){
                        if (count($page_filter) > 0) {

                            //Obtengo el slug de la nota encotrada por la busqueda
                            if ( gettype( $page_filter[0] ) == "object" ){
                                $slug_page_filter = $page_filter[0]->getSlug();
                            }else{
                                $slug_page_filter = "";
                            }
                        
                            //Divido en strings, el slug encontrado
                            $array_slug = preg_split('~[////]~', $slug_page_filter);
                            //Si existe
                            if (isset($array_slug[2])) {
    
                                $slug_modified_current = $array_slug[0] . "/" . $array_slug[2];
                                $slug_legacy = $first_level . "/" . $second_level;
                                //Si el slug legacy y el slug armado a traves de la descomposion de arriba son iguales
                                if ($slug_modified_current == $slug_legacy) {
                                    $entity = $em->getRepository('BackendBundle:Page')->findOneBy(array('slug' => $page_filter[0]->getSlug()));
                                    if (count($entity) > 0) {
    
                                        //Aqui el origen es xalok, porque tiene la estructura de xalok apesar de que este en redisenio
                                        $data = array(
                                            "status" => "success",
                                            "origen" => "xalok",
                                            "data" => $this->formatPageXalok($entity, $flag = "xalok_redisenio")
                                        );
    
                                        return $helpers->json($data, true);
                                    } else {
                                        $msg = 'Not Found in Redisenio';
                                        $data = $helpers->responseData($code = 404, $msg);
                                        $response = $helpers->responseHeaders($code = 404, $data);
    
                                        return $response;
                                    }
                                } else {
    
                                    $em2 = $this->getDoctrine()->getManager('efOld');
                                    $entity = $em2->getRepository('XalokBundle:Page')->findOneBy(array('slug' => $first_level . "/" . $second_level));
                                    if (count($entity) > 0) {
    
                                        $data = array(
                                            "status" => "success",
                                            "origen" => "xalok",
                                            "data" => $this->formatPageXalok($entity, $flag = "xalok")
                                        );
    
                                        return $helpers->json($data, true);
                                    } else {
                                        $msg = 'Not Found in Redisenio neither Xalok';
                                        $data = $helpers->responseData($code = 404, $msg);
                                        $response = $helpers->responseHeaders($code = 404, $data);
    
                                        return $response;
                                    }
                                }
                            }
    
                        } else {
                            $msg = 'Not Found in Redisenio';
                            $data = $helpers->responseData($code = 404, $msg);
                            $response = $helpers->responseHeaders($code = 404, $data);
    
                            return $response;
                        }    
                    }else{
                        $msg = 'Not Found in Redisenio';
                        $data = $helpers->responseData($code = 404, $msg);
                        $response = $helpers->responseHeaders($code = 404, $data);
    
                        return $response;
                    }
                    
                } elseif ($first_level == 'tv') {

                    $match_tv = strpos($slug, 'tv/videos');

                    //if have prefix 'tv/videos'
                    if ($match_tv !== false) {

                        $em2 = $this->getDoctrine()->getManager('efOld');
                        $entity = $em2->getRepository('XalokBundle:Page')->findOneBy(array(
                            'slug' => $slug,
                            'status' => 'published'
                        ));

                        if (count($entity) > 0) {

                            // Search if video is 'ooyala'
                            $found_oyala = strpos($entity->getContent(), 'ooyala');

                            // If video es 'ooyala', redirect a tv_home
                            if ($found_oyala !== false) {

                                $msg = 'Not Found in Xalok';
                                $data = $helpers->responseData(200, $msg);
                                $data['slugRedirect'] = 'tv';
                                $response = $helpers->responseHeaders(200, $data);

                            } else {
                                $data = array(
                                    "status" => "success",
                                    "origen" => "xalok",
                                    "data" => $this->formatPageXalok($entity, $flag = "xalok", $client_ip)
                                );

                                return $helpers->json($data, true);
                            }


                        } else {
                            $msg = 'Not Found in Redisenio neither Xalok';
                            $data = $helpers->responseData($code = 404, $msg);
                            $response = $helpers->responseHeaders($code = 404, $data);

                            return $response;
                        }


                    } else {

                        $em2 = $this->getDoctrine()->getManager('efOld');
                        $entity = $em2->getRepository('XalokBundle:Page')->findOneBy(array(
                            'slug' => 'tv/videos/' . $second_level,
                            'status' => 'published'
                        ));

                        if (count($entity) > 0) {

                            // Search if video is 'ooyala'
                            $found_oyala = strpos($entity->getContent(), 'ooyala');

                            // If video es 'ooyala', redirect a tv_home
                            if ($found_oyala !== false) {

                                $msg = 'Not Found in Xalok';
                                $data = $helpers->responseData(200, $msg);
                                $data['slugRedirect'] = 'tv';
                                $response = $helpers->responseHeaders(200, $data);

                            } else {
                                $data = array(
                                    "status" => "success",
                                    "origen" => "xalok",
                                    "data" => $this->formatPageXalok($entity, $flag = "xalok", $client_ip)
                                );

                                return $helpers->json($data, true);
                            }

                        } else {
                            $msg = 'Not Found in Redisenio neither Xalok';
                            $data = $helpers->responseData($code = 404, $msg);
                            $response = $helpers->responseHeaders($code = 404, $data);

                            return $response;
                        }


                    }

                } elseif ($first_level == 'video') {

                    $em2 = $this->getDoctrine()->getManager('efOld');
                    $entity = $em2->getRepository('XalokBundle:Page')->findOneBy(array(
                        'slug' => 'tv/videos/' . $second_level,
                        'status' => 'published'
                    ));

                    // Search if video is 'ooyala'
                    $found_oyala = strpos($entity->getContent(), 'ooyala');

                    // If video is 'ooyala', redirect a tv_home
                    if ($found_oyala !== false) {

                        $msg = 'Not Found in Xalok';
                        $data = $helpers->responseData(200, $msg);
                        $data['slugRedirect'] = 'tv';
                        $response = $helpers->responseHeaders(200, $data);

                    } else {
                        if (count($entity) > 0) {

                            $data = array(
                                "status" => "success",
                                "origen" => "xalok",
                                "data" => $this->formatPageXalok($entity, $flag = "xalok", $client_ip)
                            );

                            return $helpers->json($data, true);


                            /*$msg = 'Not Found in Xalok';
                            $data = $helpers->responseData($code = 200, $msg);
                            $data['status'] = 'success';
                            $data['origen'] = 'xalok';
                            $data['data'] = $this->formatPageXalok($entity, $flag = "xalok", $client_ip);
                            $response = $helpers->responseHeaders($code = 200, $data);

                            var_dump($response['content']);
                            die();

                            return $response;*/


                        } else {
                            $msg = 'Not Found in Redisenio neither Xalok';
                            $data = $helpers->responseData($code = 404, $msg);
                            $response = $helpers->responseHeaders($code = 404, $data);

                            return $response;
                        }
                    }

                } elseif ($first_level == 'pages') {
                    $clear_second_level = str_replace('docs', '', $second_level);

                    $em2 = $this->getDoctrine()->getManager('efOld');
                    $entity = $em2->getRepository('XalokBundle:Page')->findOneBy(array(
                        'slug' => $first_level . '/docs/' . $clear_second_level,
                        'status' => 'published'
                    ));

                    if (count($entity) > 0) {

                        $data = array(
                            "status" => "success",
                            "origen" => "xalok",
                            "data" => $this->formatPageXalok($entity, $flag = "xalok", $client_ip)
                        );

                        return $helpers->json($data, true);
                    } else {
                        $msg = 'Not Found in Xalok';
                        $data = $helpers->responseData($code = 404, $msg);
                        $response = $helpers->responseHeaders($code = 404, $data);

                        return $response;
                    }
                } else {
                    $em2 = $this->getDoctrine()->getManager('efOld');
                    $entity = $em2->getRepository('XalokBundle:Page')->findOneBy(array(
                        'slug' => $slug,
                        'status' => 'published'
                    ));


                    if (count($entity) > 0) {
                        $data = array(
                            "status" => "success",
                            "origen" => "xalok",
                            "data" => $this->formatPageXalok($entity, $flag = 'xalok', $client_ip)
                        );

                        return $helpers->json($data, true);

                    } else {
                        $entity = $em->getRepository('BackendBundle:Page')->findOneBy(array(
                            'slug' => $slug,
                        ));
                        if (count($entity) > 0) {
                            if ($entity->getStatus() == 'trash') {
                                $msg = 'Not Found in Redisenio neither Xalok';
                                $data = $helpers->responseData($code = 404, $msg);
                                $data['slugRedirect'] = ($entity->getSlugRedirect() != null) ? $entity->getSlugRedirect() : null;
                                $response = $helpers->responseHeaders($code = 404, $data);
                            }
                        } else {
                            $msg = 'Not Found in Redisenio neither Xalok';
                            $data = $helpers->responseData($code = 404, $msg);
                            $response = $helpers->responseHeaders($code = 404, $data);
                        }

                    }
                }

            }

            return $response;
        }
    }

    /**
     * @ApiDoc(
     *     section = "Notas",
     *     description="Recupera los Breaking News",
     *     requirements={
     *   },
     * )
     */
    public function getBreakingNewsAction(Request $request)
    {
        $helpers = $this->get("app.helpers");

        //Por Elastik

        $data = $this->forward('ApipublicaBundle:Search:publicall', array(
            'json'  => '{"search":"*","isBreaking":"true", "status":"published"}',
            'type' => 'page',
            'page'=>1,
            'size'=>1
        ));



        $breaking_news_rows = $this->formatBreakingNewsES($data->getContent());

        /*Por DB:
        $em = $this->getDoctrine()->getManager();
        $breaking_news = $em->getRepository("BackendBundle:Page")->findBy(array(
            'isBreaking' => 1,
            'status' => 'published'
        ));
        $breaking_news_rows = $this->formatBreakingNews($breaking_news);*/


        if (count($breaking_news_rows) != 0) {

            $data = array(
                "code" => 200,
                "status" => "success",
                "data" => $breaking_news_rows
            );

            return $helpers->json($data, true);
        } else {
            $msg = "Breaking News not found in DB, Searched by ES";
            $data = $helpers->responseData(200, $msg);
            $response = $helpers->responseHeaders(200, $data);

            return $response;
        }


    }


    /**
     * @desc Format breaking news for redisenio
     * @param $$breaking_news
     * @return array
     */
    public
    function formatBreakingNews($breaking_news)
    {
        $salida = array();

        foreach ($breaking_news as $b_new) {
            array_push($salida,
                array(
                    "id" => $b_new->getId(),
                    "category" => $b_new->getCategoryId(),
                    "title" => $b_new->getTitle(),
                    "author" => $b_new->getAuthor(),
                    "html" => $b_new->getHtml(),
                    "isBreaking" => $b_new->getIsBreaking(),
                    "status" => $b_new->getStatus(),
                    "slug" => $b_new->getSlug()
                )
            );
        }

        return $salida;
    }

    private function formatBreakingNewsES($data){
        $items = array();

        $articles = json_decode($data,true);


        //if array of notes is not empty
        if(isset($articles['data']))
        {
            if ($articles['data'][1]) {
                $array_of_notes = $articles['data'][1];
                foreach ($array_of_notes as $article) {
                    $page_data = $article['_source'];


                    $item = array();
                    $item['id'] = $page_data['id'];
                    $item['category'] = $page_data['categoryId'];
                    $item['title'] = $page_data['title'];
                    $item['author'] = $page_data['author'];
                    $item['html'] = $page_data['html'];
                    $item['isBreaking'] = $page_data['isBreaking'];
                    $item['status'] = $page_data['status'];
                    $item['slug'] = $page_data['slug'];

                    $items[] = $item;
                }

            }
        }


        return $items;
    }


    /**
     * @desc Format page xalok
     * @param $entity , $flag
     * @return array
     */
    public function formatPageXalok($entity, $flag, $client_ip="")
    {
        $helpers = $this->get("app.helpers");

        if ($flag == 'xalok_redisenio') {
            $em = $this->getDoctrine()->getManager();
        } else {
            $em = $this->getDoctrine()->getManager('efOld');
        }

        $assets = (array)json_decode($entity->getContent(), true);

        if ($flag == 'xalok_redisenio') {
            $array_autores = array();
            foreach ($entity->getAuthor() as $author) {
                array_push($array_autores,
                    array(
                        "id" => $author->getId(),
                        "firstName" => $author->getName(),
                        "lastName" => $author->getApaterno() . " " . $author->getAmaterno(),
                        "slug" => $author->getSlug(),
                        "facebook" => $author->getFacebook(),
                        "twitter" => $author->getTwitter(),
                        "image" => ($author->getImage()->getImagePath() != null) ? $author->getImage()->getImagePath() : null,
                        "imageSmall" => ($author->getImageSmall()->getImagePath() != null) ? $author->getImageSmall()->getImagePath() : null
                    ));
            }
        } elseif ($flag == 'xalok') {
            //Call service get author legacy
            $author_legacy = $this->getLegacyAuthor((isset($assets)) ? $assets : null);
            $array_autores = array();
            if ($author_legacy != null && $author_legacy != 'Introduzca el texto aquí') {
                array_push($array_autores,
                    array(
                        "id" => null,
                        "firstName" => $author_legacy,
                        "lastName" => null,
                        "slug" => null,
                        "facebook" => null,
                        "twitter" => null
                    ));
            } else {
                $array_autores = array();
                foreach ($entity->getAuthor() as $author) {
                    array_push($array_autores,
                        array(
                            "id" => $author->getId(),
                            "firstName" => $author->getFirstName(),
                            "lastName" => $author->getLastName(),
                            "slug" => $author->getSlug(),
                            "facebook" => $author->getFacebook(),
                            "twitter" => $author->getTwitter()
                        ));
                }
            }

        } else {
            $array_autores = array();
            foreach ($entity->getAuthor() as $author) {
                array_push($array_autores,
                    array(
                        "id" => $author->getId(),
                        "firstName" => $author->getFirstName(),
                        "lastName" => $author->getLastName(),
                        "slug" => $author->getSlug(),
                        "facebook" => $author->getFacebook(),
                        "twitter" => $author->getTwitter()
                    ));
            }
        }

        if ($flag == "xalok") {
            $microdata = null;
        } else {
            $microdata = $helpers->microData($entity, $from = "xalok_redisenio");
            ($microdata ? $microdata : null);
        }

        $isVideo = $entity->getTemplate() == 'video' ? true : false;

        $category = $entity->getCategoryId();
        if ($flag == 'xalok_redisenio') {
            $category = $em->getRepository('BackendBundle:Category')->find($category);
        } else {
            $category = $em->getRepository('XalokBundle:Category')->find($category);
        }

        if (isset($entity->getContent()[".article-paragraphs"])) {

            $paragraphs = preg_replace('/(.*?)\/files\/article_main(.*?)/', "$1https://www.elfinanciero.com.mx$2", $entity->getContent()[".article-paragraphs"]);
            $paragraphs = preg_replace('/(.*?)\/view(.*?)/', "$1$2", $paragraphs);
        } else {
            $paragraphs = preg_replace('/(.*?)\/files\/article_main(.*?)/', "$1$2", $assets[".article-paragraphs"]);
            $paragraphs = preg_replace('/(.*?)\/view(.*?)/', "$1$2", $paragraphs);
        }

        if (isset($entity->getContent()[".image-holder"])) {
            $imageholder = preg_replace('/(.*?)\/files\/article_main(.*?)/', "$1https://www.elfinanciero.com.mx/files/article_main$2", $entity->getContent()[".image-holder"]);
        } else {
            $imageholder = $assets[".image-holder"][0];
        }

        if (isset($entity->getContent()[".photogallery"])) {
            $photogallery = preg_replace('/(.*?)\/files\/image_gallery(.*?)/', "$1https://www.elfinanciero.com.mx$2", $entity->getContent()[".photogallery"]);
        }

        if (isset($entity->getContent()[".details-box .important"])) {
            $detail_box = $entity->getContent()[".details-box .important"];
        } else {
            $detail_box = $assets[".details-box .important"][0];
        }

        $array_category = array();
        array_push($array_category,
            array(
                "id" => $category->getId(),
                "title" => $category->getTitle(),
                "description" => $category->getDescription(),
                "active" => $category->getActive(),
                "slug" => $category->getSlug(),
                "portalId" => $category->getPortalId()
            ));

        $salida = array(
            "title" => $entity->getTitle(),
            "slug" => $entity->getSlug(),
            "shortDescription" => $entity->getShortDescription(),
            "created" => $entity->getCreatedAt(),
            "updated" => $entity->getUpdatedAt(),
            "published" => $entity->getPublishedAt(),
            "modules" => $entity->getModules(),
            "related" => $entity->getRelated(),
            "authors" => $array_autores,
            "category" => $array_category,
            "portalId" => $entity->getPortalId(),
            "microdata" => $microdata
        );

        if ($flag == 'xalok_redisenio') {
            $salida["pageType"] = $entity->getPageType();
            $salida["template"] = $entity->getTemplate();
            $salida["columna"] = $entity->getColumna();

        } else {
            $salida["pageType"] = $entity->getPageType();
            $salida["template"] = $entity->getTemplate();
        }

        if (isset($paragraphs) && isset($imageholder)) {
            $salida["content"] = array(
                ".title" => array($assets[".title"][0]), //$entity->getContent()[".title"],
                ".sub-title" => array($assets[".sub-title"][0]), //$entity->getContent()[".sub-title"],
                ".article-paragraphs" => array($paragraphs),
                ".image-holder" => array($imageholder),
                ".details-box .important" => array("")
            );
            if (isset($detail_box)) {
                $salida["content"][".details-box .important"] = $detail_box;
            }

        } elseif (isset($photogallery)) {
            $salida["content"] = array(
                ".photogallery" => $photogallery,
                ".section-wrapper" => $entity->getContent()[".section-wrapper"],
                ".sub-title" => $entity->getContent()[".sub-title"]
            );
        } elseif ($isVideo) {

            $modules = json_encode($entity->getModules());

            $ruta_video = preg_replace('/\"video\":\"(.*?)uploads(.*?).mp4/', "\"video\":\"https:\/\/www.elfinanciero.com.mx\/uploads$2.mp4", $modules);

            preg_match('/http(.*?).mp4/', $ruta_video, $match);

            $html = "<div class=\"video-holder is-16-9\"><div class=\"video-player\"><video id=\"\" class=\"video-js vjs-default-skin\" controls preload=\"metadata\" data-setup=\"{}\"><source src=\"REPLACEME\" type=\"video/mp4\"><p>Video Playback Not Supported</p></video></div></div>";

            if (!empty($match)) {
                $pathClean = str_replace("\\", "", "http" . $match[1] . ".mp4");
                $video_final = str_replace("REPLACEME", $pathClean, $html);

                $salida["content"] = array(
                    ".article-paragraphs" => $video_final
                );

            } else {

                $salida["content"] = array(
                    '.title' => (isset($entity->getContent()['.title'])) ? $entity->getContent()['.title'] : null,
                    '.sub-title' => $entity->getContent()['.sub-title'],
                    '.section-wrapper' => $entity->getContent()['.section-wrapper'],
                    '.article-paragraphs' => null
                );
            }

        } else {
            if ($flag == 'xalok_redisenio') {
                $salida["content"] = (array)json_decode($entity->getContent(), true);
            } else {
                $salida["content"] = (array)json_decode($entity->getContent(), true);
                if (isset($salida["content"][".article-paragraphs"])) {
                    $salida["content"][".article-paragraphs"] = preg_replace('/(.*?)\/view(.*?)/', "$1$2", $salida["content"][".article-paragraphs"]);
                }
            }

        }

        return $salida;
    }

    /**
     * @desc format slug redisenio, for get page xalok or residenio
     * @param $slug
     * @return array
     */
    public function formatSlugActual($slug, $client_ip)
    {
        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        $first_level = strstr($slug, '/', true);                            //Obtiene el string hasta antes del "/"
        $string_incomplete = strstr($slug, '/');                                        //Obtiene todos los caracteres despues del "/"
        $second_level = str_replace(['/', '.html'], '', $string_incomplete);            //Reemplaza el "/" y el ".html" a vacio

        //si el primer nivel del slug legacy es 'opinion'
        if ($first_level == 'opinion') {
            //Resultado de la busqueda en redisenio a traves del segundo nivel del slug de legacy
            $page_filter = $this->broadPublic("page", "column", "{\"slug2\":\"" . $second_level . "\"}", 1, 10, true, 10);
            //Si encontro un resultado
            if (count($page_filter) > 0) {

                //Obtengo el slug de la nota encotrada por la busqueda
                $slug_page_filter = $page_filter[0]->getSlug();
                //Divido en strings, el slug encontrado
                $array_slug = preg_split('~[////]~', $slug_page_filter);
                //Si existe
                if (isset($array_slug[2])) {

                    $slug_modified_current = $array_slug[0] . "/" . $array_slug[2];
                    $slug_legacy = $first_level . "/" . $second_level;
                    //Si el slug legacy y el slug armado a traves de la descomposion de arriba son iguales
                    if ($slug_modified_current == $slug_legacy) {
                        $entity = $em->getRepository('BackendBundle:Page')->findOneBy(array(
                            'slug' => $page_filter[0]->getSlug(),
                            'status' => 'published'
                        ));
                        if (count($entity) > 0) {

                            //Aqui el origen es xalok, porque tiene la estructura de xalok apesar de que este en redisenio
                            $data = array(
                                "status" => "success",
                                "origen" => "xalok",
                                "data" => $this->formatPageXalok($entity, $flag = "xalok_redisenio")
                            );

                            return $helpers->json($data, true);
                        } else {
                            $msg = 'Not Found in Redisenio';
                            $data = $helpers->responseData($code = 404, $msg);
                            $response = $helpers->responseHeaders($code = 404, $data);

                            return $response;
                        }
                    } else {
                        $em2 = $this->getDoctrine()->getManager('efOld');
                        $entity = $em2->getRepository('XalokBundle:Page')->findOneBy(array(
                            'slug' => $first_level . "/" . $second_level,
                            'status' => 'published'
                        ));
                        if (count($entity) > 0) {

                            $data = array(
                                "status" => "success",
                                "origen" => "xalok",
                                "data" => $this->formatPageXalok($entity, $flag = "xalok")
                            );

                            return $helpers->json($data, true);
                        } else {
                            $msg = 'Not Found in Redisenio neither Xalok';
                            $data = $helpers->responseData($code = 404, $msg);
                            $response = $helpers->responseHeaders($code = 404, $data);

                            return $response;
                        }
                    }
                }

            } else {
                $msg = 'Not Found in Redisenio';
                $data = $helpers->responseData($code = 404, $msg);
                $response = $helpers->responseHeaders($code = 404, $data);

                return $response;
            }


        } elseif ($first_level == 'tv') {
            $match_tv = strpos($slug, 'tv/videos');

            //if have prefix 'tv/videos'
            if ($match_tv !== false) {
                $slug_not_html = str_replace('.html', '', $slug);            //Reemplaza el ".html" a vacio
                $em2 = $this->getDoctrine()->getManager('efOld');
                $entity = $em2->getRepository('XalokBundle:Page')->findOneBy(array(
                    'slug' => $slug_not_html,
                    'status' => 'published'
                ));

                // Search if video is 'ooyala'
                $found_oyala = strpos($entity->getContent(), 'ooyala');

                // If video es 'ooyala', redirect a tv_home
                if ($found_oyala !== false) {

                    $msg = 'Not Found in Xalok';
                    $data = $helpers->responseData(200, $msg);
                    $data['slugRedirect'] = 'tv';
                    $response = $helpers->responseHeaders(200, $data);

                    return $response;

                } else {
                    if (count($entity) > 0) {

                        $data = array(
                            "status" => "success",
                            "origen" => "xalok",
                            "data" => $this->formatPageXalok($entity, $flag = "xalok", $client_ip)
                        );

                        return $helpers->json($data, true);
                    } else {
                        $msg = 'Not Found in Redisenio neither Xalok';
                        $data = $helpers->responseData($code = 404, $msg);
                        $response = $helpers->responseHeaders($code = 404, $data);

                        return $response;
                    }
                }


            } else {
                $em2 = $this->getDoctrine()->getManager('efOld');
                $entity = $em2->getRepository('XalokBundle:Page')->findOneBy(array(
                    'slug' => 'tv/videos/' . $second_level,
                    'status' => 'published'
                ));

                // Search if video is 'ooyala'
                $found_oyala = strpos($entity->getContent(), 'ooyala');

                // If video es 'ooyala', redirect a tv_home
                if ($found_oyala !== false) {

                    $msg = 'Not Found in Xalok';
                    $data = $helpers->responseData(200, $msg);
                    $data['slugRedirect'] = 'tv';
                    $response = $helpers->responseHeaders(200, $data);

                    return $response;

                } else {
                    if (count($entity) > 0) {

                        $data = array(
                            "status" => "success",
                            "origen" => "xalok",
                            "data" => $this->formatPageXalok($entity, $flag = "xalok", $client_ip)
                        );

                        return $helpers->json($data, true);
                    } else {
                        $msg = 'Not Found in Redisenio neither Xalok';
                        $data = $helpers->responseData($code = 404, $msg);
                        $response = $helpers->responseHeaders($code = 404, $data);

                        return $response;
                    }
                }


            }
        } elseif ($first_level == 'video') {
            $em2 = $this->getDoctrine()->getManager('efOld');
            $entity = $em2->getRepository('XalokBundle:Page')->findOneBy(array(
                'slug' => 'tv/videos/' . $second_level,
                'status' => 'published'
            ));

            // Search if video is 'ooyala'
            $found_oyala = strpos($entity->getContent(), 'ooyala');

            // If video is 'ooyala', redirect a tv_home
            if ($found_oyala !== false) {

                $msg = 'Not Found in Xalok';
                $data = $helpers->responseData(200, $msg);
                $data['slugRedirect'] = 'tv';
                $response = $helpers->responseHeaders(200, $data);

                return $response;

            } else {
                if (count($entity) > 0) {

                    $data = array(
                        "status" => "success",
                        "origen" => "xalok",
                        "data" => $this->formatPageXalok($entity, $flag = "xalok", $client_ip)
                    );

                    return $helpers->json($data, true);
                } else {
                    $msg = 'Not Found in Redisenio neither Xalok';
                    $data = $helpers->responseData($code = 404, $msg);
                    $response = $helpers->responseHeaders($code = 404, $data);

                    return $response;
                }
            }

        } elseif ($first_level == 'mercados') {
            //del slug actual, elimino el '.html'
            $slug_mercados = str_replace(['.html'], '', $slug);

            $em2 = $this->getDoctrine()->getManager('efOld');
            $entity = $em2->getRepository('XalokBundle:Page')->findOneBy(array(
                'slug' => $slug_mercados,
                'status' => 'published'
            ));

            if (count($entity) > 0) {

                $data = array(
                    "status" => "success",
                    "origen" => "xalok",
                    "data" => $this->formatPageXalok($entity, $flag = "xalok", $client_ip)
                );

                return $helpers->json($data, true);
            } else {
                $msg = 'Not Found in Xalok';
                $data = $helpers->responseData($code = 404, $msg);
                $response = $helpers->responseHeaders($code = 404, $data);

                return $response;
            }

        } elseif ($first_level == 'pages') {
            $clear_second_level = str_replace('docs', '', $second_level);

            $em2 = $this->getDoctrine()->getManager('efOld');
            $entity = $em2->getRepository('XalokBundle:Page')->findOneBy(array(
                'slug' => $first_level . '/docs/' . $clear_second_level,
                'status' => 'published'
            ));

            if (count($entity) > 0) {

                $data = array(
                    "status" => "success",
                    "origen" => "xalok",
                    "data" => $this->formatPageXalok($entity, $flag = "xalok", $client_ip)
                );

                return $helpers->json($data, true);
            } else {
                $msg = 'Not Found in Xalok';
                $data = $helpers->responseData($code = 404, $msg);
                $response = $helpers->responseHeaders($code = 404, $data);

                return $response;
            }
        } elseif ($first_level == 'blogs') {
            //del slug actual, elimino el '.html'
            $slug_blogs = str_replace(['.html'], '', $slug);

            $em2 = $this->getDoctrine()->getManager('efOld');
            $entity = $em2->getRepository('XalokBundle:Page')->findOneBy(array(
                'slug' => $slug_blogs,
                'status' => 'published'
            ));

            if (count($entity) > 0) {

                $data = array(
                    "status" => "success",
                    "origen" => "xalok",
                    "data" => $this->formatPageXalok($entity, $flag = "xalok", $client_ip)
                );

                return $helpers->json($data, true);
            } else {
                $msg = 'Not Found in Xalok';
                $data = $helpers->responseData($code = 404, $msg);
                $response = $helpers->responseHeaders($code = 404, $data);

                return $response;
            }
        } else {
            $em2 = $this->getDoctrine()->getManager('efOld');
            $entity = $em2->getRepository('XalokBundle:Page')->findOneBy(array(
                'slug' => $first_level . "/" . $second_level,
                'status' => 'published'
            ));
            if (count($entity) > 0) {

                $data = array(
                    "status" => "success",
                    "origen" => "xalok",
                    "data" => $this->formatPageXalok($entity, $flag = "xalok")
                );

                return $helpers->json($data, true);
            } else {
                $msg = 'Not Found in Xalok';
                $data = $helpers->responseData($code = 404, $msg);
                $response = $helpers->responseHeaders($code = 404, $data);

                return $response;
            }
        }
    }

    /**
     * Get Legacy Author on node content
     * @param $content json String
     * @return null|string author or null
     */
    public function getLegacyAuthor($content)
    {
        $legacyAuthor = null;

        if (isset($content['.details-box .important']) && $content['.details-box .important'][0] != '') {
            $legacyAuthor = strip_tags($content['.details-box .important'][0]);
        }

        return $legacyAuthor;
    }

}


