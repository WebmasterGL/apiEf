<?php
/**
 * Created by PhpStorm.
 * User: danielsolis
 * Date: 07/08/17
 * Time: 11:14
 */

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Vimeo\Vimeo;


class VimeoController extends Controller
{
    private $_cache_dir = 'vimeocache';
    private $client_id;
    private $client_secret;
    private $token;

    private function setClientId($uri)
    {
        $this->client_id = $uri;
    }

    private function setClientSecret($uri)
    {
        $this->client_secret = $uri;
    }

    private function setToken($token)
    {
        $this->token = $token;
    }

    private function getClientId()
    {
        return $this->client_id;
    }

    private function getClientSecret()
    {
        return $this->client_secret;
    }

    private function getToken()
    {
        return $this->token;
    }

    private function loadParameters()
    {
        $this->setClientId($this->container->getParameter('vimeo_client_id'));
        $this->setClientSecret($this->container->getParameter('vimeo_client_secret'));
        $this->setToken($this->container->getParameter('apiTokenVimeo'));
    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "Vimeo",
     *     description="Busca los videos que coinciden con la cadena enviada",
     *     requirements={
     *      {"name"="query", "dataType"="string", "required"=true, "description"="Cadena a buscar en los videos de la plataforma de Vimeo"},
     *      {"name"="page", "dataType"="integer", "default"=1, "required"=true, "description"="Cadena a buscar en los videos de la plataforma de Vimeo"},
     *      {"name"="sizepage", "dataType"="integer", "default"=10, "required"=true, "description"="Tamañoo de la página, 100 como máximo"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function searchAction($query, $page, $sizepage)
    {
        $query = urlencode($query);

        $respuesta = $this->executeUrl('https://api.vimeo.com/me/videos?query=' . $query . '&page=' . $page . '&per_page=' . $sizepage);
        return $this->respuestaApi($respuesta);
    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "Vimeo",
     *     description="Obtiene listado de videos en Vimeo",
     *     requirements={
     *      {"name"="page", "dataType"="integer", "default"=1, "required"=true, "description"="Cadena a buscar en los videos de la plataforma de Vimeo"},
     *      {"name"="sizepage", "dataType"="integer", "default"=10, "required"=true, "description"="Tamañoo de la página, 100 como máximo"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function getListAction($page, $sizepage)
    {

        $respuesta = $this->executeUrl('https://api.vimeo.com/me/videos?page=' . $page . '&per_page=' . $sizepage);
        return $this->respuestaApi($respuesta);

    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "Vimeo",
     *     description="Obtiene 1 video x ID de Vimeo",
     *     requirements={
     *      {"name"="videoID", "dataType"="integer", "required"=true, "description"="ID de Vimeo"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function getVideoIdAction($videoID)
    {

        $respuesta = $this->executeUrl('https://api.vimeo.com/me/videos/' . $videoID);
        return $this->respuestaApi($respuesta);

    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "Vimeo",
     *     description="Upload video to Vimeo",
     *     requirements={
     *      {"name"="title", "dataType"="string", "required"=true, "description"="Title Video"},
     *      {"name"="description", "dataType"="string", "required"=true, "description"="Description Video"},
     *      {"name"="video", "dataType"="file", "required"=true, "description"="File"},
     *      {"name"="poster", "dataType"="file", "required"=true, "description"="Thumbnail Image"},
     *      {"name"="view", "dataType"="string", "required"=false, "description"="View Video. Example:public|private", "default"= "public"},
     *      {"name"="comment", "dataType"="string", "required"=false, "description"="Comments Video. Example:anybody|nobody", "default"= "anybody"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function uploadVideoAction(Request $request)
    {
        $helpers = $this->get("app.helpers");
        $this->loadParameters();

        $title = $request->get("title");
        $description = $request->get("description");
        $file = $request->files->get('video');
        $poster = $request->files->get('poster');
        $view = $request->get('view', 'anybody');
        $comment = $request->get('comment', 'anybody');

        if ($view == 'private') {
            $view = 'nobody';
        } elseif ($view == 'public') {
            $view = 'anybody';
        }

        if ($file != null) {
            $lib = new Vimeo($this->getClientId(), $this->getClientSecret());
            $lib->setToken($this->getToken());

            // upload video
            $response = $lib->upload($file, [
                'name' => $title,
                'description' => $description,
                'privacy' => [
                    'view' => $view,
                    'comments' => $comment
                ]
            ]);

            // upload thumbnail
            if ($poster) {
                $this->forward('BackendBundle:Vimeo:addThumbnail', array('resourceUri' => $response, 'poster' => $poster, 'flag' => 'upload'));
            }

            // bitacora video_studio
            $userId = $this->getUser()->getId();
            $videoId = str_replace('/videos/','', $response);
            if(!is_null($response)){
                $flag = 'vimeo';
                $helpers->bitacoraVS($userId, $videoId, $flag);
            }
        
            $data = array(
                "status" => "success",
                "url_video" => $response
            );

        } else {
            $msg = 'Missing File';
            $data = $helpers->responseData(400, $msg);
            $response = $helpers->responseHeaders(400, $data);
            return $response;
        }


        return $helpers->json($data);

    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "Vimeo",
     *     description="Add Thumbnail to Video",
     *     requirements={
     *      {"name"="resourceUri", "dataType"="string", "required"=true, "description"="URL image associated with the video and ID video. Example: /videos/280243550"},
     *      {"name"="poster", "dataType"="file", "required"=true, "description"="Thumbnail Image"},
     *      {"name"="flag", "dataType"="string", "required"=true, "description"="Flag. Puede venir directo desde el upload si es asi, su flag sera upload"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function addThumbnailAction(Request $request, $resourceUri = null, $poster = null, $flag = null)
    {

        $helpers = $this->get("app.helpers");
        $this->loadParameters();

        if ($resourceUri == null) {
            $resource_uri = $request->get("resourceUri");
        } else {
            $resource_uri = $resourceUri;
        }

        if ($poster == null) {
            $image_path = $request->files->get('poster');
        } else {
            $image_path = $poster;
        }

        $lib = new Vimeo($this->getClientId(), $this->getClientSecret());
        $lib->setToken($this->getToken());

        // Find the pictures URI. This is also the URI that you can query to view all pictures associated with this resource.
            $resource = $lib->request($resource_uri);
        if ($resource['status'] != 200) {

            $msg = 'Could not locate the requested resource uri [' . $resource_uri . ']';
            $data = $helpers->responseData($code = 404, $msg);
            $response = $helpers->responseHeaders($code = 404, $data);

            return $response;
        }
        if (empty($resource['body']['metadata']['connections']['pictures']['uri'])) {

            $msg = 'The resource you loaded does not have a pictures connection. This most likely means that picture uploads ' .
                'are not supported for this resource.';

            $data = $helpers->responseData($code = 400, $msg);
            $response = $helpers->responseHeaders($code = 400, $data);

            return $response;
        }
        // The third parameter dictates whether the picture should become the default, or just be part of the collection of
        // pictures
        $response = $lib->uploadImage($resource['body']['metadata']['connections']['pictures']['uri'], $image_path, true);

        // --Get videoId and pictureId
        $videoId = str_replace('/videos/', '', $resource['body']['uri']);
        $pictureId = str_replace('/videos/'. $videoId .'/pictures/', '', $response);

        // activate thumbnail
        if($flag == 'upload'){
            $response_activate_thumb = $this->activeThumbnail($videoId, $pictureId);

            return $response;
        }else{

            //activate thumbnail
            $response_activate_thumb = $this->activeThumbnail($videoId, $pictureId);

            $data = array(
                "status" => "success",
                "uri_thumbnail" => $response,
                "uri_video" => $resource['body']['uri']
            );

            return $helpers->json($data);

        }

    }

    public function activeThumbnail($videoID = null, $image_video_ID = null){
        $respuesta = $this->executeUrlPatch('https://api.vimeo.com/videos/' . $videoID . '/pictures/' . $image_video_ID );
        return $this->respuestaApi($respuesta);
    }

    private function respuestaApi($respuesta)
    {
        $response = new Response(); //respuesta http

        $response->setStatusCode($respuesta["http_code"]);

        $json = $respuesta["contenido"];
        $response->setContent($json);

        $response->headers->set("Content-Type", "application/json");

        return $response;
    }

    private function executeUrlPatch($url){

        // Returned cached value
        /*if (($respuesta = $this->_getCached($url))) {
            return $respuesta;
        }*/

        $data = array('active' => 'true');

        $headers = ['Authorization: Bearer ' . $this->container->getParameter('apiTokenVimeo')];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $content = curl_exec($ch);
        $header = curl_getinfo($ch);

        curl_close($ch);


        $header['content'] = $content;


        $respuesta = array(
            "http_code" => $header["http_code"],
            "contenido" => $content
        );


        //$cached = $this->_cache($url, $respuesta);

        return $respuesta;
    }

    private function executeUrl($url)
    {

        /*$options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_USERAGENT      => "spider", // who am i
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10
        );*/

        // Returned cached value
        if (($respuesta = $this->_getCached($url))) {
            return $respuesta;
        }


        $headers = ['Authorization: Bearer ' . $this->container->getParameter('apiTokenVimeo')];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $content = curl_exec($ch);
        $header = curl_getinfo($ch);

        curl_close($ch);


        $header['content'] = $content;


        $respuesta = array(
            "http_code" => $header["http_code"],
            "contenido" => $content
        );


        $cached = $this->_cache($url, $respuesta);

        return $respuesta;

    }


    private function _cache($url, $response)
    {
        $hash = md5(serialize($url));
        $response = json_encode($response);


        if ($this->container->getParameter("kernel.environment") == "prod") {

            $file = $this->get('kernel')->getRootDir() . "/../" . $this->_cache_dir . '/' . $hash . '.cache';

        } elseif ($this->container->getParameter("kernel.environment") == "public") {
            $file = $this->get('kernel')->getRootDir() . "/../../" . $this->_cache_dir . '/' . $hash . '.cache';
        }


        if (file_exists($file)) {
            unlink($file);
        }
        return file_put_contents($file, $response);

    }


    private function _getCached($url)
    {
        $hash = md5(serialize($url));

        $expire = $this->container->getParameter('ttl_cachevimeo');


        if ($this->container->getParameter("kernel.environment") == "prod") {
            $file = $this->get('kernel')->getRootDir() . "/../" . $this->_cache_dir . '/' . $hash . '.cache';

        } elseif ($this->container->getParameter("kernel.environment") == "public") {
            $file = $this->get('kernel')->getRootDir() . "/../../" . $this->_cache_dir . '/' . $hash . '.cache';

        }


        if (file_exists($file)) {
            $last_modified = filemtime($file);
            if (substr($file, -6) == '.cache' && ($last_modified + $expire) < time()) {
                unlink($file);
            }
        }

        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true);
        }


    }


}
