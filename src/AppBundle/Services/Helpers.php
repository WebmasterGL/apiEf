<?php
/**
 * Created by PhpStorm.
 * User: danielsolis
 * Date: 14/06/17
 * Time: 15:51
 */

namespace AppBundle\Services;


use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use BackendBundle\Entity\TopNews;
use BackendBundle\Entity\UserVideo;


/*use Elastica\Query;
use Elastica\Query\QueryString;
use Elastica\Query\BoolQuery;*/

class Helpers
{

    public $jwt_auth;
    private $rootDir;
    private $container;
    private $manager;
    protected $requestStack;

    private $imagePositions = [
        '100_desktop_large' => [1439, 810],
        '100_desktop_medium' => [1199, 675],
        'tablet' => [1007, 567],
        'tablet_retina' => [2014, 1134],
        'smartphone' => [384, 216],
        'smartphone_retina' => [767, 432],
        'standard_desktop_fullhd' => [888, 500],
        'standard_desktop_large' => [760, 428],
        'standard_desktop_medium' => [632, 356],
        'standard_desktop_medium_retina' => [1264, 712],
        'original_full' => [2560, 1441],
        'original_std' => [1776, 1000],
        'carrusel' => [286, 384],


    ];

    public function __construct($jwt_auth, $rootDir, $container, $manager)
    {

        $this->jwt_auth = $jwt_auth;

        $this->rootDir = $rootDir;

        $this->container = $container;

        $this->manager = $manager;


    }

    public function logActivity($usr, $message)
    {

        $fecha = date('Ymd-H');
        $tiempo = date('H:i:s');

        file_put_contents($this->rootDir . '/../log/log' . $fecha . '.txt', $tiempo . '|' . $usr . '|' . $message . "\n", FILE_APPEND);

        $nombreArchivo = $this->rootDir . '/../log/log' . $fecha . '.txt';
        if (posix_getpwuid(fileowner($nombreArchivo))["name"] == "root") {
            chmod($nombreArchivo, 0777);
        }
    }

    public function authCheck($hash, $getIdentity = false)
    {

        $jwt_auth = $this->jwt_auth;

        if ($hash != null) {

            if ($getIdentity == false) {
                $checkToken = $jwt_auth->checkToken($hash);

                if ($checkToken > 0) {  //true
                    $auth = true;
                } else {  //false ó -1
                    $auth = $checkToken; //puede ser false o -1
                }


            } else {
                $checkToken = $jwt_auth->checkToken($hash, true);

                $auth = $checkToken;


            }

        } else {
            $auth = false;
        }


        return $auth;

    }

    public function json($data, $isPublicHeader = false)
    {



        $normalizer = array(new GetSetMethodNormalizer());


        /* $normalizer = new ObjectNormalizer();

         $normalizer->setCircularReferenceLimit(2);


         $normalizer->setCircularReferenceHandler(function ($object) {
             return $object->getId();
         });

         $normalizers = array($normalizer);*/


        $encoders = array("json" => new JsonEncoder());


        $serializer = new Serializer($normalizer, $encoders);



        $json = $serializer->serialize($data, 'json');


        $response = new Response(); //respuesta http

        if ($isPublicHeader)
            $response->setPublic();

        $response->setContent($json);

        $response->headers->set("Content-Type", "application/json");



        return $response;

    }

    public function jsonObjeto($entity, $flag, $client_ip = null)
    {
        //Si el "flag", proviene pinto regreso campos adicionales
        if ($flag == "page_version") {

            //Obtengo el campo many_to_many
            $many_to_many = $entity->getFieldsManytoMany();

            $subcategories_array = array();
            $authors_array = array();
            $images_array = array();
            $tags_array = array();

            //Recorro el campo, para obtener sus diferentes objetos del ORM
            foreach ($many_to_many as $key => $field) {
                if ($key == 'categories') {
                    foreach ($field as $value) {
                        array_push($subcategories_array, $category_db = $this->manager->getRepository('BackendBundle:Category')->find($value));
                    }
                } elseif ($key == 'authors') {
                    foreach ($field as $value) {
                        array_push($authors_array, $author_db = $this->manager->getRepository('BackendBundle:Author')->find($value));
                    }
                } elseif ($key == 'images') {
                    foreach ($field as $value) {
                        array_push($images_array, $image_db = $this->manager->getRepository('BackendBundle:Image')->find($value));
                    }
                } elseif ($key == 'tags') {
                    foreach ($field as $value) {
                        array_push($tags_array, $tag_db = $this->manager->getRepository('BackendBundle:Tag')->find($value));
                    }
                }
            }

            $array = array(
                "id" => $entity->getId(),
                "version" => $entity->getVersionNo(),
                "createdAt" => $entity->getCreatedAt(),
                "updatedAt" => $entity->getUpdatedAt(),
                "publishedAt" => $entity->getPublishedAt(),
                "nextPublishedAt" => $entity->getNextPublishedAt(),
                "createdAtPageOriginal" => $entity->getCreatedAtPage(),
                "updatedAtPageOriginal" => $entity->getUpdatedAtPage(),
                "publishedAtPageOriginal" => $entity->getPublishedAtPage(),
                "nextPublishedAtPageOriginal" => $entity->getNextPublishedAtPage(),
                "title" => $entity->getTitle(),
                "status" => $entity->getStatus(),
                "content" => (array)json_decode($entity->getContent(), true),
                "template" => $entity->getTemplate(),
                "settings" => $entity->getSettings(),
                "authors" => $authors_array,
                "category" => $entity->getCategoryId(),
                "pageType" => $entity->getPageType(),
                "subCategories" => $subcategories_array,
                "slug" => $entity->getSlug(),
                "seo" => $entity->getSeo(),
                "social" => $entity->getSocial(),
                "flag" => $entity->getFlag(),
                "tags" => $tags_array,
                "bullets" => $entity->getBullets(),
                "rss" => $entity->getRss(),
                "place" => $entity->getPlace(),
                "mostViewed" => $entity->getMostViewed(),
                "html" => $entity->getHtml(),
                "htmlSerialized" => $entity->getHtmlSerialize(),
                "newsletter" => $entity->getNewslatter(),
                "editingBy" => array(
                    "id" => ($entity->getEditingBy() != null) ? $entity->getEditingBy()->getId() : null,
                    "email" => ($entity->getEditingBy() != null) ? $entity->getEditingBy()->getEmail() : null,
                    "username" => ($entity->getEditingBy() != null) ? $entity->getEditingBy()->getUsername() : null,
                    "firstName" => ($entity->getEditingBy() != null) ? $entity->getEditingBy()->getFirstName() : null,
                    "aPaterno" => ($entity->getEditingBy() != null) ? $entity->getEditingBy()->getAPaterno() : null,
                    "aMaterno" => ($entity->getEditingBy() != null) ? $entity->getEditingBy()->getAMaterno() : null
                ),
                "creator" => array(
                    "id" => ($entity->getCreator() != null) ? $entity->getCreator()->getId() : null,
                    "email" => ($entity->getCreator() != null) ? $entity->getCreator()->getEmail() : null,
                    "username" => ($entity->getCreator() != null) ? $entity->getCreator()->getUsername() : null,
                    "firstName" => ($entity->getCreator() != null) ? $entity->getCreator()->getFirstName() : null,
                    "aPaterno" => ($entity->getCreator() != null) ? $entity->getCreator()->getAPaterno() : null,
                    "aMaterno" => ($entity->getCreator() != null) ? $entity->getCreator()->getAMaterno() : null,
                ),
                "columna" => $entity->getColumna(),
                "blog" => $entity->getBlog(),
                "mainElementHTML" => $entity->getElementHtml(),
                "mainElementSerialized" => $entity->getElementHtmlSerialized(),
                "isBreaking" => $entity->getIsBreaking(),
                "cartonesImages" => $images_array,
                "mainImage" => $entity->getMainImage(),
                "portalId" => $entity->getPortalId()
            );

        } else if ($flag == "page_xalok") {

            $array = array(
                "title" => $entity->getTitle(),
                "shortDescription" => $entity->getShortDescription(),
                "created" => $entity->getCreatedAt(),
                "updated" => $entity->getUpdatedAt(),
                "published" => $entity->getPublishedAt(),
                "content" => $entity->getContent(),
                "modules" => $entity->getModules(),
                "related" => $entity->getRelated()
            );
        } else {



            if ( $entity->getStatus() == 'published') { //$entity_es["_source"]["status"]
                $microdata = $this->microData($entity, "page");
                //$microdata = $this->microData($entity, "page", null,null,null,null,null,null,null,null);
            } else {
                $microdata = null;
            }

            /*$authors_array = array();
            foreach($entity_es["_source"]["author"] as $author){
                array_push($authors_array,   array("id"=>json_decode($author,TRUE)["id"],
                                                "name"=>json_decode($author,TRUE)["name"],
                                                "bio"=> json_decode($author,TRUE)["bio"],
                                                "twitter"=>json_decode($author,TRUE)["twitter"],
                                                "aMaterno"=>json_decode($author,TRUE)["aMaterno"],
                                                "aPaterno"=>json_decode($author,TRUE)["aPaterno"],
                                                "image"=> array(
                                                    "imagePath"=>json_decode($author,TRUE)["image"]
                                                ),
                                                "imageSmall"=> array(
                                                    "imagePath"=>json_decode($author,TRUE)["imageSmall"]
                                                )
                                        ));
            }


            $tags_array = array();
            foreach($entity_es["_source"]["tag"] as $tag){
                array_push($tags_array,   array("id"=>json_decode($tag,TRUE)["id"],
                    "title"=>json_decode($tag,TRUE)["title"],
                    "slug"=> json_decode($tag,TRUE)["slug"]
                ));
            }

            $l_mainimage = array("id"=> $entity->getMainImage()->getId(),
                                "imageName"=> $entity->getMainImage()->getImageName(),
                                "slug"=>$entity->getMainImage()->getSlug(),
                                "title"=>$entity->getMainImage()->getTitle(),
                                "description"=>$entity->getMainImage()->getDescription(),
                                "credito"=>$entity->getMainImage()->getCredito(),
                                "imagePath"=>$entity->getMainImage()->getImagePath(),
                                "type"=>$entity->getMainImage()->getType(),
                                );

            //Serializando lo necesario
            $created = $this->json($entity->getCreatedAt(), true);
            $updated = $this->json($entity->getUpdatedAt(), true);
            $published = $this->json($entity->getPublishedAt(), true);
            $nexpublished = $this->json($entity->getNextPublishedAt(), true);*/

            $array = array(
                "id" => $entity->getId(),
                "createdAt" => $entity->getCreatedAt(), //json_decode($created->getContent(),true),
                "updatedAt" =>  $entity->getUpdatedAt(), //json_decode($updated->getContent(),true),
                "publishedAt" =>  $entity->getPublishedAt(), //json_decode($published->getContent(), true),
                "nextPublishedAt" => $entity->getNextPublishedAt(), //json_decode($nexpublished->getContent(), true)
                "title" => $entity->getTitle(),
                "status" => $entity->getStatus(),
                "content" => (array)json_decode($entity->getContent(), true),
                "template" => $entity->getTemplate(),
                "settings" => $entity->getSettings(),
                "authors" => $entity->getAuthor(), //$authors_array, //
                "category" => $entity->getCategoryId(),
                /*array(
                     "slug" => json_decode($entity_es["_source"]["categoryId"],TRUE)["slug"],
                    "title"=>json_decode($entity_es["_source"]["categoryId"],TRUE)["title"]
                    ), */
                "subCategories" => $entity->getCategory(),
                "pageType" => $entity->getPageType(),
                "slug" => $entity->getSlug(),
                "seo" => $entity->getSeo(),
                "social" => $entity->getSocial(),
                "flag" => $entity->getFlag(),
                "tags" => $entity->getTag(), //$tags_array, //
                "bullets" => $entity->getBullets(),
                "rss" => $entity->getRss(),
                "place" => $entity->getPlace(),
                "mostViewed" => $entity->getMostViewed(),
                "html" => $entity->getHtml(),
                "htmlSerialized" => json_decode($entity->getHtmlSerialize(), true),
                "newsletter" => $entity->getNewslatter(),
                "editingBy" => array(
                    "id" => ($entity->getEditingBy() != null) ? $entity->getEditingBy()->getId() : null,
                    "email" => ($entity->getEditingBy() != null) ? $entity->getEditingBy()->getEmail() : null,
                    "username" => ($entity->getEditingBy() != null) ? $entity->getEditingBy()->getUsername() : null,
                    "firstName" => ($entity->getEditingBy() != null) ? $entity->getEditingBy()->getFirstName() : null,
                    "aPaterno" => ($entity->getEditingBy() != null) ? $entity->getEditingBy()->getAPaterno() : null,
                    "aMaterno" => ($entity->getEditingBy() != null) ? $entity->getEditingBy()->getAMaterno() : null
                ),
                "creator" => array(
                    "id" => ($entity->getCreator() != null) ? $entity->getCreator()->getId() : null,
                    "email" => ($entity->getCreator() != null) ? $entity->getCreator()->getEmail() : null,
                    "username" => ($entity->getCreator() != null) ? $entity->getCreator()->getUsername() : null,
                    "firstName" => ($entity->getCreator() != null) ? $entity->getCreator()->getFirstName() : null,
                    "aPaterno" => ($entity->getCreator() != null) ? $entity->getCreator()->getAPaterno() : null,
                    "aMaterno" => ($entity->getCreator() != null) ? $entity->getCreator()->getAMaterno() : null,
                ),
                "columna" => $entity->getColumna(),
                "blog" => $entity->getBlog(),
                "mainElementHTML" => $entity->getElementHtml(),
                "mainElementSerialized" => json_decode($entity->getElementHtmlSerialized(), true),
                "isBreaking" => $entity->getIsBreaking(),
                "cartonesImages" => $entity->getImage(),
                "mainImage" => $entity->getMainImage(), //,$l_mainimage,
                "portalId" => $entity->getPortalId(),
                "microdata" => $microdata
            );


            /*hasta aqui 3 queries a la bd page, image, author*/

        }

        return $array;
    }

    public function responseData($code = null, $msg = null, $list = false, $pagination = null, $total_items_count = null, $page = null, $items_per_page = null)
    {
        switch ($code) {
            case 200:
                if ($list == true) {
                    $datos = array(
                        'code' => 200,
                        'status' => 'Success',
                        'total_items_count' => $total_items_count,
                        "page_actual" => $page,
                        "items_per_page" => $items_per_page,
                        "data" => $pagination
                    );
                } else {
                    $datos = array(
                        'code' => 200,
                        'status' => 'Success',
                        'msg' => $msg
                    );
                }
                return $datos;
                break;
            case 204:
                $datos = array(
                    'code' => 204,
                    'status' => 'No Content',
                    'msg' => $msg
                );
                return $datos;
                break;
            case 400:
                $datos = array(
                    'code' => 400,
                    'status' => 'Bad Request',
                    'msg' => $msg
                );
                return $datos;
                break;
            case 401:
                $datos = array(
                    'code' => 401,
                    'status' => 'Unauthorized',
                    'msg' => $msg
                );
                return $datos;
                break;
            case 403:
                $datos = array(
                    'code' => 403,
                    'status' => 'Forbidden',
                    'msg' => $msg
                );
                return $datos;
                break;
            case 404:
                $datos = array(
                    'code' => 404,
                    'status' => 'Not Found',
                    'msg' => $msg
                );
                return $datos;
                break;
            case 410:
                $datos = array(
                    'code' => 410,
                    'status' => 'Session Gone',
                    'msg' => $msg
                );
                return $datos;
                break;


        }
    }

    public function responseHeaders($code = null, $data = null)
    {
        $response = new Response();

        $response->setStatusCode($code);
        $response->setContent("");
        $response->setContent(json_encode($data));
        $response->headers->set('Content-Type', 'text/json');
        $response->send();

        return new Response();
    }

    public function upload($cdn, $file = null, $versions = null)
    {
        $year_current = date("Y");
        $month_current = date("m");
        $day_current = date("d");
        $fs = new Filesystem();
        $path = "";
        $file_name = "";
        $error = "";
        $result = false;

        if (!empty($file) && $file != null) {
            $ext = $file->guessClientExtension();
            if ($ext == "jpeg" || $ext == "jpg" || $ext == "png" || $ext == "gif" || $ext == "svg") {
                $file_name = substr(md5(rand(1000, 9999)), 0, 10) . time() . "." . $ext;
                $path = $cdn . "/" . $year_current . "/" . $month_current . "/" . $day_current;
                if (!is_dir($path)) {
                    $fs->mkdir($path, 0777);
                }
                if ($file->move($path, $file_name)) {


                    if ($versions == "true") {

                        list($width, $height) = getimagesize($path . "/" . $file_name);
                        $partes_file = pathinfo($file_name);

                        if ($width < 890) { //sólo se redimensionan las menores, las mayores se copia la imagen original en cada version (DSG 171009)
                            foreach ($this->imagePositions as $key => $size) {
                                if ($size[0] < 890) {


                                    if($key=="standard_desktop_fullhd"){
                                        $this->container->get('image.handling')->open($path . '/' . $file_name)
                                            ->cropResize($size[0], $size[1])
                                            ->save($path . '/' . $partes_file['filename'] . "_" . $key . "." . $partes_file['extension'], $partes_file['extension'] ,50); //intentando bajar el peso de esta imagen
                                    }else{
                                        $this->container->get('image.handling')->open($path . '/' . $file_name)
                                            ->cropResize($size[0], $size[1])
                                            ->save($path . '/' . $partes_file['filename'] . "_" . $key . "." . $partes_file['extension']);
                                    }


                                    $this->imageTowebp($path,$partes_file['filename'] . "_" . $key, $partes_file['extension']);


                                } else {

                                    copy($path . '/' . $file_name, $path . '/' . $partes_file['filename'] . "_" . $key . "." . $partes_file['extension']);

                                    $this->imageTowebp($path,$partes_file['filename'] . "_" . $key, $partes_file['extension']);

                                }
                            }
                        } else { //redim de todas las versiones


                            foreach ($this->imagePositions as $key => $size) {

                                if($key=="standard_desktop_fullhd"){

                                    $this->container->get('image.handling')->open($path . '/' . $file_name)
                                        ->cropResize($size[0], $size[1])
                                        ->save($path . '/' . $partes_file['filename'] . "_" . $key . "." . $partes_file['extension'], $partes_file['extension'] ,50); //intentando bajar el peso de esta imagen

                                }else{

                                    $this->container->get('image.handling')->open($path . '/' . $file_name)
                                        ->cropResize($size[0], $size[1])
                                        ->save($path . '/' . $partes_file['filename'] . "_" . $key . "." . $partes_file['extension']);
                                }


                                $this->imageTowebp($path,$partes_file['filename'] . "_" . $key, $partes_file['extension']);
                            }
                        }

                    }

                    $result = true;
                }
            } else {
                $error = "Incorrect file extension";
            }
        } else {
            $error = "File upload failed";
        }

        return array(
            "path" => $path,
            "name" => $file_name,
            "result" => $result,
            "error" => $error,
        );
    }



    private function imageTowebp($ruta, $nombre,$ext){

        $img =null;

        switch ($ext){

            case "jpeg":

                $img = imagecreatefromjpeg($ruta."/".$nombre.".".$ext);
                break;
            case "jpg":

                $img = imagecreatefromjpeg($ruta."/".$nombre.".".$ext);
                break;
            case "png":

                $img = imagecreatefrompng($ruta."/".$nombre.".".$ext);
                break;
            case "gif":

                $img = imagecreatefromgif($ruta."/".$nombre.".".$ext);
                break;

        }



        imagewebp($img,$ruta."/".$nombre.".webp",70);


    }

    /**
     * Transforms local path to an url reachable
     * */
    public function getUrlFromLocalPath($cdn, $path)
    {
        $result = "";

        if ($path != null) {
            $path = str_replace('\\', "", $path);
            $result = str_replace($cdn, "", $path);
            $result = "/uploads" . $result;

            return $result;
        } else {

            return null;
        }
    }

    /**
     * @desc Unpublish other covers
     */
    public function unpublishOthers($idCategory, $idCover)
    {
        $query = $this->manager->getRepository("BackendBundle:Portada")->createQueryBuilder('p')
            ->where("p.status = 'published'")
            ->andWhere('p.category =' . $idCategory)
            ->andWhere('p.id !=' . $idCover)
            ->getQuery();

        $portadas_off = $query->getResult();

        foreach ($portadas_off as $portada_off) {
            $portada_off->setStatus("default");
            $this->manager->persist($portada_off);
        }

        $this->manager->flush();
    }

    public function jsonForceObject($data)
    {
        $normalizer = array(new GetSetMethodNormalizer());
        /* $normalizer = new ObjectNormalizer();
         $normalizer->setCircularReferenceLimit(2);
         $normalizer->setCircularReferenceHandler(function ($object) {
             return $object->getId();
         });

         $normalizers = array($normalizer);*/


        $encoders = array("json" => new JsonEncoder());
        $serializer = new Serializer($normalizer, $encoders);
        $json = $serializer->serialize($data, 'json');
        $response = new Response(); //respuesta http
        $response->setContent($json);
        $response->headers->set("Content-Type", "application/json");

        return $response;
    }

    /**
     * @desc get Microdata for Portada or Note
     * @param $entity, $from, $folds_published_seccion , $last_notes_section, $notes_sections_canales, $notes_channels_tv, $notes_cartoon, $data_most_viewed, $slugs_pages_programas, $videos_destacados
     * @return array
     */
    public function microData($entity, $from, $folds_published_seccion = null, $last_notes_section = null, $notes_sections_canales = null, $notes_channels = null, $notes_cartoon = null, $data_most_viewed =  null, $slugs_pages_programas = null, $videos_destacados = null)
    {
        $articleSectionSlug="";
        $article_body="";
        $image_description="";
        $bullet="";
        $tags_array=null;




        // Get paramter host_name
        if ($this->container->hasParameter('host_name') == true) {
            $host = $this->container->getParameter('host_name');
        } else {
            $host = null;
        }
        $image_final="";

        //get tags for microdata
        if ($from != 'redisenio_portada') {

            if ($entity->getTag() != null) { //  count($entity_es["_source"]["tag"])>0
                $tags = $entity->getTag(); //$entity_es["_source"]["tag"]; //
                $tags_array = array();
                if ($tags != null) {
                    foreach ($tags as $tag) {
                        array_push($tags_array, $tag->getTitle()); //json_decode($tag,true)["title"]); //
                    }
                }
            }
        } else {
            $tags_array = null;
        }

        if ($from == 'xalok_redisenio') {
            $bullet = ($entity->getShortDescription() != null) ? $entity->getShortDescription() : null;
            $article_body = null;

        } elseif ($from == 'redisenio_portada') {
            $bullet = ($entity->getDescription() != null) ? $entity->getDescription() : null;

        } else {
            // Si la nota es type 'carton'
            if ( $entity->getPageType() == 'carton') { //$entity_es["_source"]["pageType"]
                $seo = $entity->getSeo(); //$entity->getSeo();
                // algunas veces el seo['description'] lo guardan con '', por eso tambien se valida
                $bullet =  (isset($seo['description']) && $seo['description'] != '' ) ? $seo['description'] : null;
            } else {
                //get main bullet, first position

                if ($entity->getBullets() != null) { //$entity
                    $bullets = $entity->getBullets();
                    $main_bullet = reset($bullets);
                    $bullet = $main_bullet != false ? $main_bullet : null;
                }
            }

            $html_serialized = $entity->getHtmlSerialize();


            $article_body = $this->getArticleBody($html_serialized);

        }


        if ($from == 'redisenio_portada') {
            $authors_array = "El financiero";
            ($entity->getImage() != null ? $image_final = $entity->getImage()->getimagePath() : null);
            if ($entity->getImage() != null) {

                if(substr($entity->getImage()->getimagePath(),0,1)=="/")
                    list($width, $height) = getimagesize(ltrim( $entity->getImage()->getimagePath(), "/"));
                else
                    list($width, $height) = getimagesize($entity->getImage()->getimagePath()); //era con $host.

            } else {
                $width = null;
                $height = null;
            }
            $articleSection = ($entity->getTitle() != null) ? $entity->getTitle() : null;
            $articleSectionId = ($entity->getId() != null) ? $entity->getId() : null;
            $date_published = ($entity->getUpdatedAt() != null) ? $entity->getUpdatedAt()->format("c") : null;//esperando se corrija la hora de publicacion en Gsearch, ya no más Y-m-d H:i:s
            $date_modified = ($entity->getUpdatedAt() != null) ? $entity->getUpdatedAt()->format("c") : null;//esperando se corrija la hora de publicacion en Gsearch, ya no más Y-m-d H:i:s
            $date_created = ($entity->getCreatedAt() != null) ? $entity->getCreatedAt()->format("c") : null;//esperando se corrija la hora de publicacion en Gsearch, ya no más Y-m-d H:i:s
            $headline = "El Financiero " . ($entity->getTitle() != null) ? $entity->getTitle() : null;

            if ($entity->getSlug() != null) {
                $slug = ($entity->getSlug() != 'home') ? $entity->getSlug() : $slug = '';
                $section_slug = $slug;
            } else {
                $slug = null;
            }
            $host_amp = null;

            // -- unicamente si es subseccion de TV
            if($entity->getParentId() == 81){
                $itemListElement = $this->getNotesSubSecTv($slugs_pages_programas, $videos_destacados, $host, $section_slug);
                //$itemListElement = null;
            }else{
                // -- le paso los folds de la portada , las 10 ultimas notas de seccion, array de las 3 ultimas notas por seccion de canal
                // -- array de las 3 ultimas notas por channel de portada TV, array de las notas de cartoon, el ID de la categoria y el host
                $itemListElement = $this->getNotesByFold($folds_published_seccion, $last_notes_section, $notes_sections_canales, $notes_channels, $notes_cartoon, $data_most_viewed, $articleSectionId, $host, $section_slug);
                
                // --$entity == es el objeto de una categoria
                /*if($entity != null){
                    if($entity->getId() == 1){
                        $itemListElement = null;
                    }else{
                        // -- le paso los folds de la portada , las 10 ultimas notas de seccion, array de las 3 ultimas notas por seccion de canal
                        // -- array de las 3 ultimas notas por channel de portada TV, array de las notas de cartoon, el ID de la categoria y el host
                        $itemListElement = $this->getNotesByFold($folds_published_seccion, $last_notes_section, $notes_sections_canales, $notes_channels, $notes_cartoon, $data_most_viewed, $articleSectionId, $host);
                    }
                }*/
            }
        } else {


            if ($entity->getAuthor() != null){  // count($entity_es["_source"]["author"])>0){}
                $authors = $entity->getAuthor(); //$entity_es["_source"]["author"]; //
                $authors_array = array();
                if ($authors != null) {
                    foreach ($authors as $author) {
                        array_push($authors_array, $author->getName() . " " . $author->getAPaterno());
                        //array_push($authors_array, json_decode($author,true)["name"] . " " . json_decode($author,true)["aPaterno"]);
                    }
                }
            } else {
                $authors_array = null;
            }

            ($entity->getMainImage() != null ? $image_final = $entity->getMainImage()->getimagePath() : null);

            /*Genera un Query a la bd para ir por la imagen*/
            //($entity->getMainImage() != null ? $image_final = $entity_es["_source"]["mainImage"] : null); //$entity->getMainImage()->getimagePath()
            ($entity->getMainImage() != null ? $image_description = $entity->getMainImage()->getDescription() : null);


            if ($entity->getMainImage() != null) {
                if(substr($entity->getMainImage()->getimagePath(),0,1)=="/")
                    list($width, $height) = getimagesize(ltrim( $entity->getMainImage()->getimagePath(), "/"));
                else
                    list($width, $height) = getimagesize($entity->getMainImage()->getimagePath()); //era con $host.
            } else {
                $width = null;
                $height = null;
            }

            $articleSection = ($entity->getCategoryId() != null) ? $entity->getCategoryId()->getTitle() : null;
            $articleSectionSlug = ($entity->getCategoryId() != null) ? $entity->getCategoryId()->getSlug() : null;


            //$articleSection = ($entity->getCategoryId() != null) ? json_decode($entity_es["_source"]["categoryId"],true)["title"] : null; //$entity->getCategoryId()->getTitle()

            //$articleSectionSlug = ($entity->getCategoryId() != null) ? json_decode($entity_es["_source"]["categoryId"],true)["slug"] : null;//$entity->getCategoryId()->getSlug()

            $date_published = ($entity->getPublishedAt() != null) ? $entity->getPublishedAt()->format("c") : null;  //esperando se corrija la hora de publicacion en Gsearch, ya no más Y-m-d H:i:s

            $date_modified = ($entity->getUpdatedAt() != null) ? $entity->getUpdatedAt()->format("c") : null;//esperando se corrija la hora de publicacion en Gsearch, ya no más Y-m-d H:i:s
            $date_created = ($entity->getCreatedAt() != null) ? $entity->getCreatedAt()->format("c") : null;//esperando se corrija la hora de publicacion en Gsearch , ya no más Y-m-d H:i:s
            $headline = ($entity->getTitle() != null) ? $entity->getTitle() : null;
            $slug = ($entity->getSlug() != null) ? $entity->getSlug() : null;
            $host_amp = str_replace(['//www.elfinanciero', '//elfinanciero'], '//amp.elfinanciero', $host);


        }


        // @TODO microdata Organization
        $microdata_organization = array(
            "@type" => 'Organization',
            "@context" => "https://schema.org",
            "name" => "El Financiero",
            "url" => $host,
            "logo" => array(
                "@type" => "ImageObject",
                "url" => $host . "/images/logo24.png",
                "width" => 600,
                "height" => 60
            ), 
            //$host . "/images/logo24.png",
            "sameAS" => array(
                "0" => "https://www.facebook.com/ElFinancieroMx",
                "1" => "https://twitter.com/ElFinanciero_Mx",
                "2" => "https://plus.google.com/+elfinanciero",
                "3" => "https://www.youtube.com/user/FinancieroMexico",
            )
        );

        // @TODO microdata
        $microdata_news_article = array(
            "@type" => "NewsArticle",
            "@id" => $host . "/" . $articleSectionSlug,
            "mainEntityOfPage" => $host . "/" . $slug,
            "@context" => "https://schema.org",
            "headline" => ($headline != null) ? $headline : null, //titulo de la nota
            "description" => $bullet, //balazo ó description
            "datePublished" => ($date_published != null) ? $date_published : null,
            "dateModified" => ($date_modified != null) ? $date_modified : null,
            "articleBody" => $article_body,
            "publisher" => $microdata_organization,
            "author" => array(          //igual, con el financiero
                "@type" => "Organization",
                "name" => "El Financiero"
            ),
            "image" => array(
                "@type" => "ImageObject",
                "@id" => "??",
                "representativeOfPage" => "https://schema.org/True",
                "url" => ($image_final != null) ? $host . str_replace('.', '_standard_desktop_fullhd.', $image_final) : null,
                "contentUrl" => ($image_final != null) ? $host . str_replace('.', '_standard_desktop_fullhd.', $image_final) : null,
                "description" => $image_description,
                "width" => $width,
                "height" => $height
            ),
            "dateCreated" => ($date_created != null) ? $date_created : null,
            "articleSection" => ($articleSection != null) ? $articleSection : null,               //categoria principal
            "creator" => ($authors_array != null) ? $authors_array : null,                //autores
            "keywords" => ($tags_array != null) ? $tags_array : null        //tags
        );

        // @TODO microdata WebPage
        $microdata_web_page = array(
            "@type" => 'WebPage',
            "@context" => "https://schema.org"
        );
        if($entity != null){
            if($entity->getId() == 1){
                $microdata_web_page['@id'] = $host;
            }else{
                $microdata_web_page['@id'] = $host . "/" . $slug;    
            }
        }


        // @TODO microdata schema
        if($from == 'redisenio_portada'){
            $microdata_schema = array(
                "WebPage" => $microdata_web_page,
                "Organization" => $microdata_organization
            );
            if($entity != null){

                $microdata_schema['ItemList'] = $itemListElement;

            }
        }else if($from == 'amp'){
            $microdata_schema = array(
                "@context" => "https://schema.org",
                "@type" => "NewsArticle",
                "mainEntityOfPage" => $microdata_web_page,
                //"@id" => $host . "/" . $articleSectionSlug,
                "headline" => ($headline != null) ? $headline : null, //titulo de la nota
                "description" => $bullet, //balazo ó description
                "datePublished" => ($date_published != null) ? $date_published : null,
                "dateModified" => ($date_modified != null) ? $date_modified : null,
                "articleBody" => $article_body,
                "publisher" => $microdata_organization,
                "author" => array(          //igual, con el financiero
                    "@type" => "Organization",
                    "name" => "El Financiero"
                ),
                "image" => array(
                    "@type" => "ImageObject",
                    "@id" => "??",
                    "representativeOfPage" => "https://schema.org/True",
                    "url" => ($image_final != null) ? $host . str_replace('.', '_standard_desktop_fullhd.', $image_final) : null,
                    "contentUrl" => ($image_final != null) ? $host . str_replace('.', '_standard_desktop_fullhd.', $image_final) : null,
                    "description" => $image_description,
                    "width" => $width,
                    "height" => $height
                ),
                "dateCreated" => ($date_created != null) ? $date_created : null,
                "articleSection" => ($articleSection != null) ? $articleSection : null,               //categoria principal
                "creator" => ($authors_array != null) ? $authors_array : null,                //autores
                "keywords" => ($tags_array != null) ? $tags_array : null        //tags
               // "Organization" => $microdata_organization
            );
        }else{
            $microdata_schema = array(
                "WebPage" => $microdata_web_page,
                "Organization" => $microdata_organization,
                "NewsArticle" => $microdata_news_article
            );
        }
        
        // @TODO microdata meta
        $microdata_meta = array(
            'description' => $bullet, //balazo ó description
            'image' => ($image_final != null) ? $host . str_replace('.', '_standard_desktop_fullhd.', $image_final) : null
        );



        // @TODO microdata twitter
        $microdata_twitter = array(
            "twitter:creator"=> "@ElFinanciero_Mx",
            "twitter:url" => $host . "/" . $slug,
            "twitter:card" => "summary_large_image",
            "twitter:site" => "@ElFinanciero_Mx",
            "twitter:title" => ($headline != null) ? $headline : null,
            "twitter:description" => $bullet,
            "twitter:image:src" => ($image_final != null) ? $host . str_replace('.', '_standard_desktop_fullhd.', $image_final) : null,
            "twitter:image:width" => $width,
            "twitter:image:height" => $height
        );



        // @TODO microdata facebook
        $microdata_fb = array(
            "og:site_name" => "El Financiero",
            "og:url" => $host . "/" . $slug,
            "og:type" => "article",
            "og:title" => ($headline != null) ? $headline : null,
            "og:description" => $bullet,
            "og:image" => ($image_final != null) ? $host . str_replace('.', '_standard_desktop_fullhd.', $image_final) : null,
            "og:image:width" => $width,
            "og:image:height" => $height,
            "og:image:url" => ($image_final != null) ? $host . str_replace('.', '_standard_desktop_fullhd.', $image_final) : null,
            "fb:app_id" => 1063568587023463,
            "fb:pages" => 16250043914
        );

        $microdata_object = array(
            "schema" => $microdata_schema,
            "meta" => $microdata_meta,
            "twitter" => $microdata_twitter,
            "og" => $microdata_fb,
            "amphtml" => $host_amp . "/" . $slug
        );

        /*Hasta aqui 2 Queryes selecta a la bd (page e image)*/

        return $microdata_object;
    }

    public function microDataColumna($columna, $notas)
    {


        /*$listItem = array("url"=>"miurl",
            "@type"=>"ItemList",
            "@context"=>"https://schema.org");*/

        //return array(10,array($notas));
        //return array(34, $listItem, 36);




        // Get paramter host_name
        if ($this->container->hasParameter('host_name') == true) {
            $host = $this->container->getParameter('host_name');
        } else {
            $host = null;
        }


        $listItem = [];
        $i=0;
        foreach ($notas as $nota){
            array_push($listItem, array("url"=> $host."/".$nota["_source"]["slug"],
                                        "@type"=> "ListItem",
                                        "position"=>$i)
                    );
            $i++;
        }
        $microdata_object = array(
            "schema" => array(
                        "WebPage"=>array("@type"=>"WebPage",
                                        "@context"=> "https://schema.org",
                                         "@id"=> $host."/opinion/".$columna->getSlug()),
                        "Organization" => array(
                                "@type"=> "Organization",
                                "@context"=> "https://schema.org",
                                "name"=> "El Financiero",
                                "url"=> $host,
                                "logo" => array(
                                    "@type"=> "ImageObject",
                                    "url"=> $host."/images/logo24.png",
                                    "width"=> 600,
                                    "height"=> 60
                                ),
                                "sameAS" => array(
                                    "0" => "https://www.facebook.com/ElFinancieroMx",
                                    "1" => "https://twitter.com/ElFinanciero_Mx",
                                    "2" => "https://plus.google.com/+elfinanciero",
                                    "3" => "https://www.youtube.com/user/FinancieroMexico",
                                )
                            ),
                        "ItemList"=>array(
                                    "url"=>$host."/opinion/".$columna->getSlug(),
                                    "itemListElement"=>array(
                                        "@type"=> "ListItem",
                                        "position"=> 1,
                                        "item"=> array(
                                                "url"=>$host."/opinion/".$columna->getSlug(),
                                                "itemListElement"=>array($listItem),
                                                "@type"=> "ItemList",
                                                "@context"=> "https://schema.org"
                                            )
                                    ),
                                    "@type"=> "ItemList",
                                    "@context"=> "https://schema.org"
                                )
                            ),
            "meta" => array("description"=>$columna->getAuthors()[0]->getBio(),"image"=>$host.$columna->getAuthors()[0]->getImage()->getImagePath()),
            "twitter" => array(
                "twitter:creator"=> "@ElFinanciero_Mx",
                "twitter:url" => $host."/opinion/".$columna->getSlug(),
                "twitter:card"=>"summary_large_image",
                "twitter:site" => "@ElFinanciero_Mx",
                "twitter:title" => $columna->getNombreSistema(),
                "twitter:description" => $columna->getAuthors()[0]->getBio(),
                "twitter:image:src" => $host.$columna->getAuthors()[0]->getImage()->getImagePath(),
                "twitter:image:width" => 1023,
                "twitter:image:height" => 630
                                ),
            "og" => array(
                "og:site_name" => "El Financiero",
                "og:url"=>$host."/opinion/".$columna->getSlug(),
                "og:type" => "article",
                "og:title"=> $columna->getNombreSistema(),
                "og:description"=> $columna->getAuthors()[0]->getBio(),
                "og:image"=>$host.$columna->getAuthors()[0]->getImage()->getImagePath(),
                "og:image:width"=> null,
                "og:image:height"=> null,
                "og:image:url"=>$host.$columna->getAuthors()[0]->getImage()->getImagePath(),
                "fb:app_id"=>1063568587023463,
                "fb:pages"=>16250043914
                        ),
            "amphtml"=> "/opinion/".$columna->getSlug()
        );

        return $microdata_object;

    }

    public function getArticleBody($html_serialized = null)
    {
        $article_body = '';
        $mi_format = json_decode($html_serialized, true);

        foreach ($mi_format as $key => $value) {
            if($value['type'] == 'text'){
                foreach ($value['data'] as $y => $val) {
                    foreach ($val as $x => $data) {
                        if($x == 'nodes'){
                            foreach ($data as $z => $datos) {
                                $nodos = $datos['nodes'];
                                foreach ($nodos as $key => $nodo) {

                                    if(isset($nodo['ranges'])){
                                        $ranges = $nodo['ranges'];
                                        foreach ($ranges as $key => $range) {
                                            $article_body .= $range['text'];
                                        }
                                    }

                                }
                            }
                        }
                    }
                }
            }
        }

        return $article_body;
    }


    /**
     * @desc get TopNews
     */
    public function topNews($portada_id)
    {
        $em = $this->manager;
        $connection = $em->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL('top_news', true /* whether to cascade */));

        //Barrido de Folds
        $foldsDSG = $em->getRepository('BackendBundle:PortadaFolds')->findBy(
            array(
                "idportada" => $portada_id,
                "status" => 'published'
            ),
            array('orden' => 'ASC')
        );

        $flag_conteo = 0;
        foreach ($foldsDSG as $fold) {
            $micontent = json_decode($fold->getContent(), true);
            foreach ($micontent as $elcontent) {
                if ($flag_conteo >= 20) {
                    break 2;
                }
                if (isset($elcontent["content"]["pageType"]) && $elcontent["content"]["pageType"] == "article") {

                    $top_new_exist = $em->getRepository('BackendBundle:TopNews')->findBy(
                        array(
                            "slug" => $elcontent["content"]["slug"]
                        )
                    );

                    //add if slug not exist in top_news
                    if (count($top_new_exist) == 0) {
                        $top_news = new TopNews();
                        $flag_conteo++;

                        $top_news->setSlug($elcontent["content"]["slug"]);
                        $em->persist($top_news);
                        $em->flush();
                    }
                }
            }
        }

        //$this->dataTopNews($flag_conteo);
        $data = array(
            'conteo' => $flag_conteo,
            'idPortada' => $portada_id
        );

        return $data;
    }

    /**
     * @desc get Item List (notes)
     * @param $folds_published_seccion , $last_notes_section, $notes_sections_canales, $notes_channels_tv, $notes_cartoon, $articleSectionId , $host
     * @return array
     */
    public function getNotesByFold($folds_published_seccion, $last_notes_section, $notes_sections_canales, $notes_channels_tv, $notes_cartoon, $data_most_viewed, $articleSectionId, $host, $section_slug)
    {
        // Si la seccion es 'Home'
        if ($articleSectionId == 1) {
            return $final_item_list = $this->getNotesHomeByFolds($folds_published_seccion, $notes_sections_canales, $data_most_viewed, $host, $section_slug);
            // Si la seccion es 'TV'
        } elseif($articleSectionId == 81){
            return $final_item_list = $this->getNotesTvByFolds($folds_published_seccion, $notes_channels_tv, $host, $section_slug);
            // Si la seccion es 'Opinion'
        } elseif($articleSectionId == 17){
            return $final_item_list = $this->getNotesOpinionFolds($folds_published_seccion, $notes_cartoon, $host, $section_slug);
        } else {
            return $final_item_list = $this->getNotesCommonByFolds($folds_published_seccion, $last_notes_section, $data_most_viewed, $host, $section_slug);
        }

    }

    /**
     * @desc get Slug notes for Portada Subsecciones TV
     * @param $slugs_pages_programas , $videos_destacados, $host
     * @return array
     */
    public function getNotesSubSecTv($slugs_pages_programas = null, $videos_destacados = null, $host = null, $section_slug = null)
    {
        $listItem = [];

        if ($slugs_pages_programas != null) {                            
            $data = array(
                '@type' => 'ListItem',
                'position' => 0,
                'item' => array(
                    'url' => $host . "/" . $section_slug,
                    'itemListElement' => $slugs_pages_programas,
                    '@type' => 'ItemList',
                    '@context' => 'https://schema.org'
            ));

            array_push($listItem, $data);
        }

        if ($videos_destacados != null) {                            
            $data = array(
                '@type' => 'ListItem',
                'position' => 1,
                'item' => array(
                    'url' => $host . "/" . $section_slug,
                    'itemListElement' => $videos_destacados,
                    '@type' => 'ItemList',
                    '@context' => 'https://schema.org'
            ));

            array_push($listItem, $data);
        }

        foreach ($listItem as $key => $value) {
            if (is_null($value) || $value == '')
                unset($listItem[$key]);
        }

        $final_item_list = array(
            'url' => $host . "/" . $section_slug,
            'itemListElement' => $listItem,
            '@type' => 'ItemList',
            '@context' => 'https://schema.org'
        );

        return $final_item_list;
    }

    /**
     * @desc get Slug notes for Portada Home
     * @param $folds_published_seccion , $notes_sections_canales, $data_most_viewed
     * @return array
     */
    public function getNotesHomeByFolds($folds_published_seccion , $notes_sections_canales = null, $data_most_viewed = null , $host = null, $section_slug = null)
    {
        $listItem = [];

        // loop full folds
        foreach ($folds_published_seccion[1] as $x => $folds) {

            $item_list = [];

            // loop each fold
            foreach ($folds as $llave => $fold) {

                // si el key es _source 
                if ($llave == '_source') {

                    // si existe el nodo 'idfold' y ademas la descripcion de dicho nodo es diferente a Mercados
                    if (isset($fold['idfold']) && $fold['idfold']['descripcion'] != 'Barra de Mercados') {

                        // si existe el nodo content
                        if (isset($fold['content'])) {

                            // convierto a array el string del content
                            $mi_content = json_decode($fold['content'], true);

                            $item_list_sub = [];

                            // loop contenido del content
                            foreach ($mi_content as $y => $content) {
                                // si existe el nodo 'content' y el nodo 'related'
                                if( isset($content['content']) && isset($content['related']) ){
                                    
                                    foreach ($content as $key => $value) {
                                        if($key == 'content' || $key == 'related'){

                                                if($key == 'content'){
                                                    if(!empty($value)){
                                                       // si existe 'customLink' 
                                                       $slug = isset($value["customLink"]) ? $value["customLink"] : $value["slug"];
                                                        
                                                        array_push($item_list_sub,
                                                            array(
                                                                'url' => $host . "/" . $slug,
                                                                '@type' => 'ListItem',
                                                                'position' => $y
                                                        ));
                                                    }
                                                }
                                                if($key == 'related'){
                                                    if(!empty($value)){
                                                        foreach ($value as $z => $data) {
                                                            if($data["slug"] != null){
                                                                array_push($item_list_sub,
                                                                    array(
                                                                        'url' => $host . "/" . $data["slug"],
                                                                        '@type' => 'ListItem',
                                                                        'position' => $y
                                                                ));
                                                            }  
                                                        }
                                                    }
                                                }
                                        }
                                    }
                                }
                            }
                        }

                    }
                }

            }

            // Merge notas de canales con las demas notas de folds
            if ($folds["_source"]["idfold"]["descripcion"] == 'Canales') {                            
                array_push($listItem, array(
                    '@type' => 'ListItem',
                    'position' => $x,
                    'item' => array(
                        'url' => $host,
                        'itemListElement' => $notes_sections_canales,
                        '@type' => 'ItemList',
                        '@context' => 'https://schema.org'
                    )
                ));
            }

            // Push only if not is Barra de Mercados or Canales
            if ($folds["_source"]["idfold"]["descripcion"] == 'Barra de Mercados' || $folds["_source"]["idfold"]["descripcion"] == 'Canales' || $folds["_source"]["idfold"]["descripcion"] == 'Apertura a todo lo ancho HTML') {
                //code
            } else {
                array_push($listItem, array(
                    '@type' => 'ListItem',
                    'position' => $x,
                    'item' => array(
                        'url' => $host,
                        'itemListElement' => $item_list_sub,
                        '@type' => 'ItemList',
                        '@context' => 'https://schema.org'
                    )
                ));
                
            }
        }

        // --merge notes most viewed
        if($data_most_viewed != null && !empty($data_most_viewed)){
            array_push($listItem, array(
                '@type' => 'ListItem',
                'position' => $x + 1,
                'item' => array(
                    'url' => $host,
                    'itemListElement' => $data_most_viewed,
                    '@type' => 'ItemList',
                    '@context' => 'https://schema.org'
                )
            ));
        }

        foreach ($listItem as $key => $value) {
            if (is_null($value) || $value == '')
                unset($listItem[$key]);
        }

        $final_item_list = array(
            'url' => $host,
            'itemListElement' => $listItem,
            '@type' => 'ItemList',
            '@context' => 'https://schema.org'
        );


        return $final_item_list;
    }

    /**
     * @desc get Slug notes for Common Portada
     * @param $folds_published_seccion , $last_notes_section, $data_most_viewed
     * @return array
     */
    public function getNotesCommonByFolds($folds_published_seccion, $last_notes_section  = null, $data_most_viewed = null, $host = null, $section_slug = null)
    {

        $listItem = [];

        //-- loop full folds --
        foreach ($folds_published_seccion[1] as $x => $folds) {

            $item_list = [];

            // loop each fold
            foreach ($folds as $llave => $fold) {

                // si el key es _source
                if ($llave == '_source') {

                    // si existe el nodo content
                    if (isset($fold['content'])) {

                        // convierto a array el string del content
                        $mi_content = json_decode($fold['content'], true);

                        // loop contenido del content
                        foreach ($mi_content as $y => $content) {
                            $item_list_sub = [];

                            //- Fold prime
                            if ($y == 'prime') {
                                if (isset($content["content"]["_source"]) && isset($content["related"])) {

                                    if (!empty($content["content"]["_source"])) {
                                        array_push($item_list_sub,
                                            array(
                                                'url' => $host . "/" . $content["content"]["_source"]["slug"],
                                                '@type' => 'ListItem',
                                                'position' => 0
                                            ));
                                    }

                                    if (!empty($content["related"])) {
                                        foreach ($content["related"] as $position => $nodo) {
                                            if (!empty($nodo)) {
                                                array_push($item_list_sub,
                                                    array(
                                                        'url' => $host . "/" . $nodo["_source"]["slug"],
                                                        '@type' => 'ListItem',
                                                        'position' => $position + 1
                                                    ));
                                            }
                                        }
                                    }
                                }

                                // Only prints if nodo 'content' and 'related' is not empty
                                if (!empty($content["content"]["_source"]) && !empty($content["related"])) {
                                    array_push($listItem, array(
                                        '@type' => 'ListItem',
                                        'position' => 0,
                                        'item' => array(
                                            'url' => $host . "/" . $section_slug,
                                            'itemListElement' => $item_list_sub,
                                            '@type' => 'ItemList',
                                            '@context' => 'https://schema.org'
                                        )
                                    ));
                                }

                            }

                            //- Fold destacadas
                            if ($y == 'destacadas') {
                                foreach ($content as $position => $nodo) {
                                    if (!empty($nodo)) {
                                        array_push($item_list,
                                            array(
                                                'url' => $host . "/" . $nodo["content"]["_source"]["slug"],
                                                '@type' => 'ListItem',
                                                'position' => $position
                                            ));
                                    }
                                }

                                array_push($listItem, array(
                                    '@type' => 'ListItem',
                                    'position' => 1,
                                    'item' => array(
                                        'url' => $host . "/" . $section_slug,
                                        'itemListElement' => $item_list,
                                        '@type' => 'ItemList',
                                        '@context' => 'https://schema.org'
                                    )
                                ));
                            }

                        }
                    }

                }
            }

        }

        if($folds['_source']['idfold']['category']['slug'] != 'mercados'){
            //-- loop last 10 notes
            $item_list_last = [];

            foreach ($last_notes_section[1] as $key => $value){
                array_push($item_list_last,
                    array(
                        'url' => $host . "/" . $value["_source"]["slug"],
                        '@type' => 'ListItem',
                        'position' => $key
                    ));
            }

            array_push($listItem, array(
                '@type' => 'ListItem',
                'position' => 2,
                'item' => array(
                    'url' => $host . "/" . $section_slug,
                    'itemListElement' => $item_list_last,
                    '@type' => 'ItemList',
                    '@context' => 'https://schema.org'
                )
            ));

        }

        // --merge notes most viewed
        if($data_most_viewed != null && !empty($data_most_viewed)){
            array_push($listItem, array(
                '@type' => 'ListItem',
                'position' => 3,
                 'item' => array(
                    'url' => $host . "/" . $section_slug,
                    'itemListElement' => $data_most_viewed,
                    '@type' => 'ItemList',
                    '@context' => 'https://schema.org'
                )
            ));
        }

        foreach ($listItem as $key => $value) {
            if (is_null($value) || $value == '')
                unset($listItem[$key]);
        }


        $final_item_list = array(
            'url' => $host . "/" . $section_slug,
            'itemListElement' => $listItem,
            '@type' => 'ItemList',
            '@context' => 'https://schema.org'
        );


        return $final_item_list;
    }

    /**
     * @desc get Slug notes for TV Portada
     * @param $portada_tv , $notes_channels, $host
     * @return array
     */
    public function getNotesTvByFolds($portada_tv, $notes_channels = null, $host = null, $section_slug = null){
        
        $listItem = [];
        $item_list = [];

        foreach($portada_tv[1] as $key => $data){
           $content = $data["_source"]["content"];
           // convierto a array el string del content
           $mi_content = json_decode($content, true);

           foreach ($mi_content as $llave => $value) {
               
               if($llave == 'header'){
                   $data_full = $value['data'];
                    array_push($item_list,
                        array(
                            'url' => $host . "/" . $data_full["_source"]["slug"],
                            '@type' => 'ListItem',
                            'position' => 0
                        ));   
               }

               if($llave == 'featured'){
                   foreach ($value as $z => $feature) {
                       $data_full = $feature["data"];
                       array_push($item_list,
                            array(
                                'url' => $host . "/" . $data_full["_source"]["slug"],
                                '@type' => 'ListItem',
                                'position' => $z + 1
                            ));  
                   }
               }
           }

           array_push($listItem, array(
                '@type' => 'ListItem',
                'position' => $key,
                'item' => array(
                    'url' => $host . "/" . $section_slug,
                    'itemListElement' => $item_list,
                    '@type' => 'ItemList',
                    '@context' => 'https://schema.org'
            )));


            // Merge notas de channels tv con las demas notas de folds
           if ($notes_channels != null) {                            
                $listItemChannels = array(
                    '@type' => 'ListItem',
                    'position' => $key + 1,
                    'item' => array(
                        'url' => $host . "/" . $section_slug,
                        'itemListElement' => $notes_channels,
                        '@type' => 'ItemList',
                        '@context' => 'https://schema.org'
                ));

                array_push($listItem, $listItemChannels);
            }
        }

        foreach ($listItem as $key => $value) {
            if (is_null($value) || $value == '')
                unset($listItem[$key]);
        }

        $final_item_list = array(
            'url' => $host . "/" . $section_slug,
            'itemListElement' => $listItem,
            '@type' => 'ItemList',
            '@context' => 'https://schema.org'
        );

        return $final_item_list;
    }
    /**
     * @desc get Slug notes for Opinion Portada
     * @param $folds_published_seccion , $notes_cartoon, $host
     * @return array
     */
    public function getNotesOpinionFolds($folds_published_seccion, $notes_cartoon = null, $host = null, $section_slug = null)
    {
        $listItem = [];
        $item_list_featured = [];
        $item_list_normal = [];
        
        foreach($folds_published_seccion[1] as $key => $data){

           $content = $data["_source"]["content"];
           // convierto a array el string del content
           $mi_content = json_decode($content, true);

           foreach ($mi_content as $llave => $value) {
            
               if($llave == 'featured'){
                    foreach ($value as $z => $val) {
                        array_push($item_list_featured,
                            array(
                                'url' => $host . "/" . $val["slugColumna"],
                                '@type' => 'ListItem',
                                'position' => $z
                            ));   
                    }
               }

               if($llave == 'normal'){
                   foreach ($value as $z => $val) {
                       array_push($item_list_normal,
                            array(
                                'url' => $host . "/" . $val["slugColumna"],
                                '@type' => 'ListItem',
                                'position' => $z
                            ));  
                   }
               }
           }

           array_push($listItem, array(
                '@type' => 'ListItem',
                'position' => 0,
                'item' => array(
                    'url' => $host . "/" . $section_slug,
                    'itemListElement' => $item_list_featured,
                    '@type' => 'ItemList',
                    '@context' => 'https://schema.org'
                )));

            // Merge notas de cartoon 
            if($notes_cartoon != null){
                $listItemCartoon = array(
                    '@type' => 'ListItem',
                    'position' => 1,
                    'item' => array(
                        'url' => $host . "/" . $section_slug,
                        'itemListElement' => $notes_cartoon,
                        '@type' => 'ItemList',
                        '@context' => 'https://schema.org'
                ));

                array_push($listItem, $listItemCartoon);
            }

            // Merge notas de opinion 'normal' con las demas notas de folds
           if ($item_list_normal != null) {                            
                $listItemNormal = array(
                    '@type' => 'ListItem',
                    'position' => 2,
                    'item' => array(
                        'url' => $host . "/" . $section_slug,
                        'itemListElement' => $item_list_normal,
                        '@type' => 'ItemList',
                        '@context' => 'https://schema.org'
                ));

                array_push($listItem, $listItemNormal);
            }

        }

        foreach ($listItem as $key => $value) {
            if (is_null($value) || $value == '')
                unset($listItem[$key]);
        }

        $final_item_list = array(
            'url' => $host . "/" . $section_slug,
            'itemListElement' => $listItem,
            '@type' => 'ItemList',
            '@context' => 'https://schema.org'
        );

        return $final_item_list;
    }

    /**
     * @desc get Channels ID's for TV Portada
     * @param $portada_tv
     * @return array
     */
    public function getChannelsPortada($portada_tv){

        $channels_id = [];
        
        $bandera = 0;
        foreach($portada_tv[1] as $key => $data){
            $content = $data["_source"]["content"];
            // convierto a array el string del content
            $mi_content = json_decode($content, true);
            
            foreach ($mi_content as $llave => $value) {
                if($llave == 'channels'){
                    foreach($value as $x => $z){

                        // only prints las primeras 5 posiciones del arreglo de channels ids
                        if ($bandera >= 5) {
                            break 3;
                        }
                        $bandera++;
                        array_push($channels_id, $z);
                    }
                }
            }
        }

        return $channels_id;
    }

    /**
     * @desc get the slugs of the sections of the fold canales of the home portada
     * @param $folds_published_seccion
     * @return array
     */
    public function getSectionsCanales($folds_published_seccion){

        $sections_array = [];

        //-- loop full folds --
        foreach ($folds_published_seccion[1] as $x => $folds) {

            // loop each fold
            foreach ($folds as $llave => $fold) {
                // si el key es _source
                if ($llave == '_source') {

                     // si existe el nodo 'idfold' y ademas la descripcion de dicho nodo es Canales
                     if (isset($fold['idfold']) && $fold['idfold']['descripcion'] == 'Canales') {

                        // si existe el nodo content
                        if (isset($fold['content'])) {

                            // convierto a array el string del content
                            $mi_content = json_decode($fold['content'], true);

                            // loop contenido del content
                            $bandera = 0;
                            foreach ($mi_content as $y => $content) {
                                if($bandera >= 4){
                                    break;
                                }
                                if($content['content']['value'] != '-'){
                                    $seccion = str_replace(' ', '-', $content['content']['value']);
                                    $seccion = $this->eliminar_tildes($seccion);
                                    array_push($sections_array, strtolower($seccion));

                                    $bandera++;
                                }
                            }
                        }
                    }

                }
            }
        }
        
        return $sections_array; 
    }

    /**
     * @desc get list item slugs of the pages
     * @param $search_last_notes_section
     * @return array
     */
    public function getNotesSearch($search_last_notes_section, $host = null)
    {
        $item_list_last = [];
        foreach ($search_last_notes_section[1] as $key => $value){

            // if folds is not type 'page'
            if( isset($value["_source"]["idportada"]) ){
                $mi_content = json_decode($value["_source"]["content"], true);

                foreach ($mi_content as $nodos) {
                    foreach ($nodos as $z => $val) {
                        array_push($item_list_last,
                            array(
                                'url' => $host . "/" . $val["slugColumna"],
                                '@type' => 'ListItem',
                                'position' => $z
                            ));
                    }
                }
            }else{
                array_push($item_list_last,
                    array(
                        'url' => $host . "/" . $value["_source"]["slug"],
                        '@type' => 'ListItem',
                        'position' => $key
                    ));
            }
            
        }

        return $item_list_last;
        
    }

    public function getNotesSearchSpecial($search_last_notes_section, $host = null)
    {
        $item_list_last = [];
        foreach ($search_last_notes_section[1] as $key => $value){

            array_push($item_list_last,
                array(
                    'url' => $host . "/" . $value["_source"]["slug"],
                    '@type' => 'ListItem',
                    'position' => $key
            ));
        }

        return $item_list_last;
        
    }

    public function getNotesMostViewed($notes, $host = null)
    {
        $item_list = [];
        foreach ($notes as $key => $value) {
            array_push($item_list,
                array(
                        'url' => $host . "/" . $value["slug"],
                        '@type' => 'ListItem',
                        'position' => $key
                ));
        }

        return $item_list;
    }

    public function dataTopNews($flag_conteo)
    {
        $data = array(
            "conteo" => $flag_conteo
        );

        return $data;
    }

    public function Purga($slug)
    {
        $cacheServers = $this->container->getParameter('cacheserver');
        $archivo_purga = $this->container->getParameter('archivopurga');

        $answer = array();

        foreach ($cacheServers as $dominio) {
            $answer[$dominio] = $this->execCommand("sh " . $archivo_purga . " https://" . $dominio . "/" . $slug);
        }

        if ($this->container->get('security.token_storage')->getToken() != NULL)
            $this->logActivity($this->container->get('security.token_storage')->getToken()->getUser()->getEmail(), json_encode($answer));
        else
            $this->logActivity("crontab@crontab.com", json_encode($answer));


        return $answer;

    }

    public function purgaPublica($slug)
    {
        $api_uri = $this->container->getParameter('api_uri');
        $archivo_purga = $this->container->getParameter('archivopurga');

        $answer = "";

        $answer = $this->execCommand("sh " . $archivo_purga . " https://" . $api_uri . "/" . $slug);

        if ($this->container->get('security.token_storage')->getToken() != NULL)
            $this->logActivity($this->container->get('security.token_storage')->getToken()->getUser()->getEmail(), json_encode($answer));
        else
            $this->logActivity("crontab@crontab.com", json_encode($answer));

        return $answer;

    }

    private function execCommand($comando)
    {


        $res_exec = exec(escapeshellcmd($comando), $salida, $ret);


        /*if ($ret == 0) //($salida == NULL || strlen($salida)==0)
        {*/
        $respuesta = array(
            "ret" => $ret,
            "contenido" => $salida,
            "exec" => $res_exec
        );
        //}

        return $respuesta;

    }


    public function ejecutaPurgaTools($slug)
    {


        $archivo_purga = $this->container->getParameter('archivopurga');

        $res_exec = $this->execCommand("sh " . $archivo_purga . " https://www.elfinanciero.com.mx/" . $slug );

        //$res_exec = exec("curl \"http://tools.elfinanciero.online/purga/purga.php?website=ef&url=https://www.elfinanciero.com.mx/". $slug . "\" ", $salida, $ret);

        $respuesta = array(
            "exec" => $res_exec
        );


        return $respuesta;

    }

    public function eliminar_tildes($cadena){
        $originales =  'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝ
                        ßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
        $modificadas = 'aaaaaaaceeeeiiiidnoooooouuuuy
                        bsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
        $cadena = utf8_decode($cadena);
        $cadena = strtr($cadena, utf8_decode($originales), $modificadas);
        $cadena = strtolower($cadena);
        return utf8_encode($cadena);
    }

    public function bitacoraVS($userId, $videoId, $flag)
    {
        $user = $this->manager->getRepository("BackendBundle:WfUser")->find($userId);

        if($userId != null && $videoId != null && $flag != null){
            $userVideo = new UserVideo();

            $userVideo->setPublisher($user);
            if($flag == 'vimeo'){
                $userVideo->setVimeoVId($videoId);
            }else{
                $userVideo->setYoutubeVId($videoId);
            }
            $userVideo->setCreatedAt(new \DateTime());

            $this->manager->persist($userVideo);
            $this->manager->flush();
        }
        
    }


}