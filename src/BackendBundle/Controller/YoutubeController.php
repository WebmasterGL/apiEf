<?php
/**
 * Created by PhpStorm.
 * User: jserrano
 * Date: 21/05/18
 * Time: 12:27
 */

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Google_Client;
use Google_Service_YouTube;
use Google_Service_YouTube_VideoSnippet;
use Google_Service_YouTube_Video;
use Google_Service_YouTube_VideoStatus;
use Google_Http_MediaFileUpload;

class YoutubeController extends Controller
{
    //base url EF
    private $client_id;
    private $client_secret;
    private $api_key;
    private $channel_id;
    private $redirect_uri;
    private $_cache_dir = 'youtubecache';

    private function setClientId($uri)
    {
        $this->client_id = $uri;
    }

    private function setClientSecret($uri)
    {
        $this->client_secret = $uri;
    }

    private function setApiKey($key)
    {
        $this->api_key = $key;
    }

    private function setChannelId($channel_id)
    {
        $this->channel_id = $channel_id;
    }

    private function setRedirectUri($redirect_uri)
    {
        $this->redirect_uri = $redirect_uri;
    }

    private function getClientId()
    {
        return $this->client_id;
    }

    private function getClientSecret()
    {
        return $this->client_secret;
    }

    private function getApiKey()
    {
        return $this->api_key;
    }

    private function getChannelId()
    {
        return $this->channel_id;
    }

    private function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    private function loadParameters()
    {
        $this->setClientId($this->container->getParameter('youtube_client_id'));
        $this->setClientSecret($this->container->getParameter('youtube_client_secret'));
        $this->setApiKey($this->container->getParameter('youtube_api_key'));
        $this->setChannelId($this->container->getParameter('youtube_channel_id'));
        $this->setRedirectUri($this->container->getParameter('youtube_redirect_uri'));
    }

    /**
     * Para acceder a este metodo require autorizacion
     * @ApiDoc(
     *     section = "YouTube",
     *     description="Recupera todos los videos del Canal de YouTube del EF",
     *     requirements={
     *      {"name"="maxResults", "dataType"="integer", "required"=true, "description"="Maximo de resultados", "default"=10},
     *      {"name"="pageToken", "dataType"="string", "required"=true, "description"="Page Token. Eg: nextPageToken|prevPageToken"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function getListAction($maxResults, $pageToken)
    {
        $this->loadParameters();
        /*$pageToken = $request->get("pageToken");
        $maxResults = $request->get("maxResults");*/

        if ($pageToken == "{pageToken}") {
            $respuesta = $this->executeUrl('https://www.googleapis.com/youtube/v3/search?key=' . $this->getApiKey() . '&channelId=' . $this->getChannelId() . '&part=snippet,id&order=date&maxResults=' . $maxResults);

        } else {
            $respuesta = $this->executeUrl('https://www.googleapis.com/youtube/v3/search?key=' . $this->getApiKey() . '&channelId=' . $this->getChannelId() . '&part=snippet,id&order=date&maxResults=' . $maxResults . '&pageToken=' . $pageToken);
        }

        return $this->respuestaApi($respuesta);

    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "YouTube",
     *     description="Busca los videos que coinciden con la cadena enviada",
     *     requirements={
     *      {"name"="query", "dataType"="string", "required"=true, "description"="Cadena a buscar en los videos de la plataforma de Vimeo"},
     *      {"name"="maxResults", "dataType"="integer", "default"=10, "required"=true, "description"="Tamaño de la página, 100 como máximo"},
     *      {"name"="pageToken", "dataType"="string", "required"=true, "description"="Page Token. Eg: nextPageToken|prevPageToken"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function searchVideoAction(Request $request)
    {
        $this->loadParameters();
        $pageToken = $request->get("pageToken");
        $query = $request->get("query");
        $maxResults = $request->get("maxResults");

        $full_query = str_replace(' ', '+', $query);

        if ($pageToken != null) {
            $respuesta = $this->executeUrl('https://www.googleapis.com/youtube/v3/search?key=' . $this->getApiKey() . '&channelId=' . $this->getChannelId() . '&q=' . $full_query . '&part=snippet,id&order=date&maxResults=' . $maxResults . '&pageToken=' . $pageToken);
        } else {
            $respuesta = $this->executeUrl('https://www.googleapis.com/youtube/v3/search?key=' . $this->getApiKey() . '&channelId=' . $this->getChannelId() . '&q=' . $full_query . '&part=snippet,id&order=date&maxResults=' . $maxResults);
        }

        return $this->respuestaApi($respuesta);

    }

    /**
     * Para acceder a este metodo require autorizacion
     * @ApiDoc(
     *     section = "YouTube",
     *     description="Sube un video a la plataforma de Youtube",
     *     requirements={
     *      {"name"="title", "dataType"="string", "required"=true, "description"="Title Video"},
     *      {"name"="description", "dataType"="string", "required"=true, "description"="Description Video"},
     *      {"name"="view", "dataType"="string", "required"=true, "description"="Privacy View. eg: public|private", "default"="public"},
     *      {"name"="video", "dataType"="file", "required"=true, "description"="File"},
     *      {"name"="poster", "dataType"="file", "required"=true, "description"="Thumbnail Image"},
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function uploadVideoAction(Request $request)
    {
        $key = file_get_contents($this->get('kernel')->getRootDir() . '/../token_youtube.txt');
        $helpers = $this->get("app.helpers");
        $this->loadParameters();

        $videoTitle = $request->get("title");
        $videoDescription = $request->get("description");
        $videoPath = $request->files->get('video');
        $imagePath = $request->files->get('poster');
        $privacyView = $request->get("view");

        $application_name = 'El Financiero';
        $scope = array('https://www.googleapis.com/auth/youtube.upload', 'https://www.googleapis.com/auth/youtube', 'https://www.googleapis.com/auth/youtubepartner');

        $videoCategory = "22";
        // fix dynamic
        $videoTags = array("youtube", "tutorial");

        if ($videoPath != null) {

            if ($videoTitle && $videoDescription && $videoPath) {
                try {
                    // Client init
                    $client = new Google_Client();
                    $client->setApplicationName($application_name);
                    $client->setClientId($this->getClientId());
                    $client->setAccessType('offline');
                    $client->setAccessToken($key);
                    $client->setScopes($scope);
                    $client->setClientSecret($this->getClientSecret());

                    if ($client->getAccessToken()) {

                        /**
                         * Check to see if our access token has expired. If so, get a new one and save it to file for future use.
                         */
                        if ($client->isAccessTokenExpired()) {
                            $newToken = $client->getAccessToken();
                            $client->refreshToken($newToken['refresh_token']);
                            $newToken = $client->getAccessToken();

                            file_put_contents($this->get('kernel')->getRootDir() . '/../token_youtube.txt', json_encode($newToken, JSON_PRETTY_PRINT));
                        }

                        $youtube = new Google_Service_YouTube($client);

                        // Create a snipet with title, description, tags and category id
                        $snippet = new Google_Service_YouTube_VideoSnippet();
                        $snippet->setTitle($videoTitle);
                        $snippet->setDescription($videoDescription);
                        $snippet->setCategoryId($videoCategory);
                        $snippet->setTags($videoTags);

                        // Create a video status with privacy status. Options are "public", "private" and "unlisted".
                        $status = new Google_Service_YouTube_VideoStatus();
                        $status->setPrivacyStatus($privacyView);

                        // Create a YouTube video with snippet and status
                        $video = new Google_Service_YouTube_Video();
                        $video->setSnippet($snippet);
                        $video->setStatus($status);

                        // Size of each chunk of data in bytes. Setting it higher leads faster upload (less chunks,
                        // for reliable connections). Setting it lower leads better recovery (fine-grained chunks)
                        $chunkSizeBytes = 1 * 1024 * 1024;

                        // Setting the defer flag to true tells the client to return a request which can be called
                        // with ->execute(); instead of making the API call immediately.
                        $client->setDefer(true);

                        // Create a request for the API's videos.insert method to create and upload the video.
                        $insertRequest = $youtube->videos->insert("status,snippet", $video);

                        // Create a MediaFileUpload object for resumable uploads.
                        $media = new Google_Http_MediaFileUpload(
                            $client,
                            $insertRequest,
                            'video/*',
                            null,
                            true,
                            $chunkSizeBytes
                        );
                        $media->setFileSize(filesize($videoPath));


                        // Read the media file and upload it chunk by chunk.
                        $status = false;
                        $handle = fopen($videoPath, "rb");

                        while (!$status && !feof($handle)) {
                            $chunk = fread($handle, $chunkSizeBytes);
                            $status = $media->nextChunk($chunk);
                        }

                        fclose($handle);

                        /**
                         * Video has successfully been upload, now lets perform some cleanup functions for this video
                         */
                        if ($status->status['uploadStatus'] == 'uploaded') {
                            $data = array(
                                "status" => "success",
                                "data" => array(
                                    'msg' => 'video subido exitosamente',
                                    'videoId' => $status['id'],
                                    'uploadStatus' => $status['status']['uploadStatus']
                                )
                            );

                            //Upload Thumbnail
                            /*if ($imagePath) {
                                $this->forward('BackendBundle:Youtube:uploadThumbnail', array('videoId' => $status['id'], 'poster' => $imagePath, 'flag' => 'upload'));
                            }*/

                            // bitacora video_studio
                            $userId = $this->getUser()->getId();
                            $videoId = $status['id'];
                            $flag = 'youtube';
                            $helpers->bitacoraVS($userId, $videoId, $flag);
                        }

                        // If you want to make other calls after the file upload, set setDefer back to false
                        $client->setDefer(true);

                    } else {
                        // @TODO Log error
                        $msg = "Problems creating the client";
                        $data = $helpers->responseData(400, $msg);
                        $response = $helpers->responseHeaders(400, $data);

                        return $response;
                    }

                } catch (Google_Service_Exception $e) {
                    print "Caught Google service Exception " . $e->getCode() . " message is " . $e->getMessage();
                    print "Stack trace is " . $e->getTraceAsString();
                } catch (Exception $e) {
                    print "Caught Google service Exception " . $e->getCode() . " message is " . $e->getMessage();
                    print "Stack trace is " . $e->getTraceAsString();
                }
            } else {
                $msg = "Missing params";
                $data = $helpers->responseData(400, $msg);
                $response = $helpers->responseHeaders(400, $data);

                return $response;
            }
        } else {
            $msg = 'Missing File';
            $data = $helpers->responseData(400, $msg);
            $response = $helpers->responseHeaders(400, $data);
            return $response;
        }

        return $helpers->json($data);

    }

    /**
     * Para acceder a este metodo require autorizacion
     * @ApiDoc(
     *     section = "YouTube",
     *     description="Sube un Thumbnail para un video de Youtube",
     *     requirements={
     *      {"name"="videoId", "dataType"="integer", "required"=true, "description"="Video Id"},
     *      {"name"="poster", "dataType"="file", "required"=true, "description"="Thumbnail Image"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function uploadThumbnailAction(Request $request, $poster = null, $videoId = null, $flag = null)
    {

        $key = file_get_contents($this->get('kernel')->getRootDir() . '/../token_youtube.txt');
        $helpers = $this->get("app.helpers");
        $this->loadParameters();

        if ($videoId == null) {
            $video_id = $request->get("videoId");
        } else {
            $video_id = $videoId;
        }

        if ($poster == null) {
            $imagePath = $request->files->get('poster');
        } else {
            $imagePath = $poster;
        }

        if ($video_id && $imagePath) {
            try {
                // Client init
                $client = new Google_Client();
                $client->setClientId($this->getClientId());
                $client->setAccessType('offline');
                $client->setAccessToken($key);
                $client->setScopes('https://www.googleapis.com/auth/youtube');
                $client->setClientSecret($this->getClientSecret());

                if ($client->getAccessToken()) {

                    /**
                     * Check to see if our access token has expired. If so, get a new one and save it to file for future use.
                     */
                    if ($client->isAccessTokenExpired()) {
                        $newToken = $client->getAccessToken();
                        $client->refreshToken($newToken['refresh_token']);
                        $newToken = $client->getAccessToken();

                        file_put_contents($this->get('kernel')->getRootDir() . '/../token_youtube.txt', json_encode($newToken, JSON_PRETTY_PRINT));
                    }

                    // Define an object that will be used to make all API requests.
                    $youtube = new Google_Service_YouTube($client);

                    // Specify the size of each chunk of data, in bytes. Set a higher value for
                    // reliable connection as fewer chunks lead to faster uploads. Set a lower
                    // value for better recovery on less reliable connections.
                    $chunkSizeBytes = 1 * 1024 * 1024;

                    // Setting the defer flag to true tells the client to return a request which can be called
                    // with ->execute(); instead of making the API call immediately.
                    $client->setDefer(true);

                    // Create a request for the API's thumbnails.set method to upload the image and associate
                    // it with the appropriate video.
                    $setRequest = $youtube->thumbnails->set($video_id);

                    // Create a MediaFileUpload object for resumable uploads.
                    $media = new Google_Http_MediaFileUpload(
                        $client,
                        $setRequest,
                        'image/png',
                        null,
                        true,
                        $chunkSizeBytes
                    );
                    $media->setFileSize(filesize($imagePath));

                    // Read the media file and upload it chunk by chunk.
                    $status = false;
                    $handle = fopen($imagePath, "rb");
                    while (!$status && !feof($handle)) {
                        $chunk = fread($handle, $chunkSizeBytes);
                        $status = $media->nextChunk($chunk);
                    }

                    fclose($handle);

                    // If you want to make other calls after the file upload, set setDefer back to false
                    $client->setDefer(false);

                    $thumbnailUrl = $status['items'][0]['default']['url'];

                    /**
                     * Thumbnail has successfully been upload
                     */

                    if ($flag != 'upload') {
                        $data = array(
                            "status" => "success",
                            "data" => array(
                                'msg' => 'Thumbnail Uploaded',
                                'videoId' => $video_id,
                                'thumbnailUrl' => $thumbnailUrl
                            )
                        );
                    } else {
                        return $thumbnailUrl;
                    }


                } else {
                    // @TODO Log error
                    $msg = "Problems creating the client";
                    $data = $helpers->responseData(400, $msg);
                    $response = $helpers->responseHeaders(400, $data);

                    return $response;
                }

            } catch (Google_Service_Exception $e) {
                print "Caught Google service Exception " . $e->getCode() . " message is " . $e->getMessage();
                print "Stack trace is " . $e->getTraceAsString();
            } catch (Exception $e) {
                print "Caught Google service Exception " . $e->getCode() . " message is " . $e->getMessage();
                print "Stack trace is " . $e->getTraceAsString();
            }
        } else {
            $msg = "Missing params";
            $data = $helpers->responseData(400, $msg);
            $response = $helpers->responseHeaders(400, $data);

            return $response;
        }


        return $helpers->json($data);
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

    private function executeUrl($url)
    {
        // Returned cached value
        if (($respuesta = $this->_getCached($url))) {
            return $respuesta;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
