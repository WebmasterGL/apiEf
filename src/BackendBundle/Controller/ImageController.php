<?php
/**
 * Created by PhpStorm.
 * User: javiermorquecho
 * Date: 07/07/17
 * Time: 16:35
 */

namespace BackendBundle\Controller;

use BackendBundle\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Entity\Image;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Gedmo\Sluggable\Util\Urlizer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class ImageController extends Controller
{
    /**
     * Descripcion especifica de este metodo
     *
     * @ApiDoc(
     *  section = "Images",
     *  description="Add image. Image is mandatory.",
     *     requirements={
     *      {"name"="json", "dataType"="array", "required"=true, "description"="{'title':'el titulo','description':'la descripcion','slug':'el-slug','footnote':'pie de pagina','tags':'idtag1, idtag2, idtagn',['credito':'nombre_no_catalogado'|'sourcecat':'idauthor'],'type':['redsocial'|'thumb'|'vertical'|'especial'|NULL(mainimage,horizontal ó seo, son lo mismo)], 'generateVersions':['true'|'false'] }"},
     *      {"name"="image", "dataType"="file", "required"=true, "description"="Image File"},
     *    },
     *     headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function addAction(Request $request)
    {
        $helpers    = $this->get("app.helpers");
        $json       = $request->get("json", null);
        $slug_final = "";
        $contador   = 0;

        if ($json !== null) {
            $params        = json_decode( $json );
            $params        = $this->setParams( $params );
            $file          = $request->files->get('image' );
            $cdn           = $this->container->getParameter('cdn');

            if ( $file->getError() != 0 ){
                $msg      = $file->getErrorMessage();
                $data     = $helpers->responseData(400, $msg);
                $response = $helpers->responseHeaders(400, $data);

                return $response;
            }

            $file_uploaded = $helpers->upload( $cdn, $file, $params->generateVersions );

            if ( $file_uploaded["result"] ) {                                       //Is it uploaded?
                $slug_final = $params->title;                                        //set slug
                while ($this->imageSlugValidate($slug_final)) {
                    $slug_final = $params->title . ++$contador;
                }
                $em     = $this->getDoctrine()->getManager();               //update
                $source = $em->getRepository('BackendBundle:Author')->findOneById($params->sourcecat);
                $image  = new Image();
                $image->setCreatedAt(new \DateTime());
                $image->setUpdatedAt(new \DateTime());
                $image->setTitle($params->title);
                $image->setDescription($params->description);
                $image->setSlug($slug_final);
                $image->setImageName( $file_uploaded["name"] );
                $image->setImagePath( $this->getUrlFromLocalPath($file_uploaded["path"] . "/" . $file_uploaded["name"] ) );
                $tags_col = explode(",", $params->tags);                //set tags
                foreach ($tags_col as $val) {
                    $tag = $em->getRepository('BackendBundle:Tag')->findOneById($val);
                    $image->addTag($tag);
                }
                $image->setFootnote($params->footnote);
                $image->setCredito($params->credito);
                $image->setSourcecat($source);
                $image->setType($params->type);
                $image->setVersiones($params->generateVersions);

                $validator = $this->get('validator');
                $errors    = $validator->validate($image);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }
                if(count($errors)>0){
                    $data = $helpers->responseData(400, $messages);
                    $response = $helpers->responseHeaders(400, $data);
                }else{
                    $em->persist($image);
                    $em->flush();
                    $url         = $this->getUrlFromLocalPath($file_uploaded["path"] . "/" . $file_uploaded["name"] );
                    $data        = $helpers->responseData(200, "success");
                    $data["url"] = $url;
                    $data["id"]  = $image->getId();
                    $response    = $helpers->responseHeaders(200, $data);
                }
            }else{
                if ( $file_uploaded["error"] != "" ) {
                    $msg = $file_uploaded["error"];
                }else{
                    $msg = 'Image doesnt uploaded';
                }
                $data     = $helpers->responseData(400, $msg);
                $response = $helpers->responseHeaders(400, $data);
            }
        }else{
            $msg      = 'Image doesnt created, without data';
            $data     = $helpers->responseData(400, $msg);
            $response = $helpers->responseHeaders(400, $data);
        }

        return $response;
    }

    /**
     * Descripcion especifica de este metodo
     *
     * @ApiDoc(
     *     section = "Images",
     *     description="Get php info",
     *     requirements={
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     *
     */
    public function getInfoAction(Request $request){

        $helpers = $this->get("app.helpers");

        $data = phpinfo();

        $response    = $helpers->responseHeaders(200, $data);

        return $response;

    }

    /**
     * Descripcion especifica de este metodo
     *
     * @ApiDoc(
     *     section = "Images",
     *     description="Edit an image. Image isn't mandatory.",
     *     requirements={
     *      {"name"="id",   "dataType"="string",    "required"=true, "description"="id image"},
     *      {"name"="json", "dataType"="array", "required"=true, "description"="{'title':'el titulo','description':'la descripcion','slug':'el-slug','footnote':'pie de pagina','tags':'idtag1, idtag2, idtagn',['credito':'nombre_no_catalogado'|'sourcecat':'idauthor'],'type':['redsocial'|'thumb'|'vertical'|'especial'|NULL(mainimage,horizontal ó seo, son lo mismo)], 'generateVersions':['true'|'false'] }"},
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     *
     */
    public function editAction(Request $request, $id = null)
    {
        $helpers = $this->get("app.helpers");
        $json    = $request->get("json");
        $tags_db = null;

        if ($json !== null) {
            $params      = json_decode( $json );
            $params      = $this->setParams( $params );
            $file        = $request->files->get('image' );
            $em          = $this->getDoctrine()->getManager();
            $image       = $em->getRepository('BackendBundle:Image')->findOneById( $id );
            $url         = "";
            $cdn         = $this->container->getParameter('cdn');


            if ( count( $image ) > 0 ){
                $slug_final  = $params->title;                                       //set slug
                $contador    = 0;
                while( $this->imageSlugValidate( $slug_final ) ){
                    $slug_final = $params->slug . ++$contador;
                }
                $source = $em->getRepository('BackendBundle:Author')->findOneById( $params->sourcecat );
                if ( $file !== null ) {
                    $file_uploaded = $helpers->upload( $cdn, $file, $params->generateVersions );
                    if ( $file_uploaded["result"] ) {                  //Is it uploaded?
                        $image->setImageName( $file_uploaded["name"] );
                        $image->setImagePath( $this->getUrlFromLocalPath( $file_uploaded["path"] . "/" . $file_uploaded["name"] ) );
                    }else{
                        if ( $file_uploaded["error"] != "" ) {
                            $msg = $file_uploaded["error"];
                        }else{
                            $msg = 'Image doesnt uploaded';
                        }
                        $data     = $helpers->responseData(400, $msg);
                        $response = $helpers->responseHeaders(400, $data);

                        return $response;
                    }
                }
                $image->setUpdatedAt(new \DateTime());                //update
                if ( $params->title !== null )           $image->setTitle($params->title);
                if ( $params->description !== null )     $image->setDescription($params->description);
                if ( $slug_final !== null )      $image->setSlug($slug_final);
                if ( $params->tags !== null ){
                    $tags_db  = $image->getTag();                         //remove current tags
                    foreach( $tags_db as $val ){
                        $image->removeTag( $val );
                    }
                    $tags_col = explode( ",", $params->tags );           //add tags
                    foreach( $tags_col as $val ){
                        $tag = $em->getRepository('BackendBundle:Tag')->findOneById( $val );
                        $image->addTag( $tag );
                    }
                }
                if ( $params->footnote !== null )        $image->setFootnote($params->footnote);
                if ( $params->credito !== null )          $image->setCredito($params->credito);
                if ( $source !== null )          $image->setSourcecat($source);
                if ( $params->type !== null )            $image->setType($params->type);
                $em->persist($image);
                $em->flush();
                $data        = $helpers->responseData(200, "success");
                $data["url"] = $url;
                $data["id"]  = $image->getId();
                $response    = $helpers->responseHeaders(200, $data);
            } else {
                $msg      = 'Image not uploded, it doesn\'t exist';
                $data     = $helpers->responseData(404, $msg);
                $response = $helpers->responseHeaders(404, $data);
            }
        } else {
            $msg      = 'Image not updated, no args';
            $data     = $helpers->responseData(400, $msg);
            $response = $helpers->responseHeaders(400, $data);
        }

        return $response;
    }

    /**
     * @ApiDoc(
     *  section = "Images",
     *  description="Images list or just one based in id",
     *  requirements={
     *    {"name"="id",          "dataType"="integer",  "required"=false,                   "description"="id image"},
     *    {"name"="page",        "dataType"="string",   "required"=false,   "default"="1",  "description"="page number"},
     *    {"name"="itemsInPage", "dataType"="integer",  "required"="false", "default"="10", "description"="items by page"}
     *
     *  },
     *   headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     * )
     */
    public function imageAction( Request $request, $id=NULL )
    {
        $helpers = $this->get("app.helpers");
        $em      = $this->getDoctrine()->getManager();

        if ( $id == NULL || $id=='{id}' ){
            $page   = $request->query->get('page', 1);
            $size   = $request->query->get('itemsInPage', 10);
            $Images = $em->getRepository('BackendBundle:Image')->findBy( array(), array('createdAt' => 'DESC') );
            $Images = $this->formatOutput( $Images );
            $paginator         = $this->get("knp_paginator");
            $page              = $request->query->get('page',1);
            $pagination        = $paginator->paginate( $Images, $page, $size );
            $total_items_count = $pagination->getTotalItemCount();
            $list              = true;
            $data              = $helpers->responseData(
                200,
               null,
                $list,
                $pagination,
                $total_items_count,
                $page,
                $size
            );
        }else{
            $data = $em->getRepository('BackendBundle:Image')->findById( $id );
            $data = $this->formatOutput( $data );
        }

        return $helpers->json($data);
    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     *
     * @ApiDoc(
     *     section = "Images",
     *     description="Delete image",
     *     requirements={
     *      {"name"="id", "dataType"="string", "required"=true, "description"="id image"},
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     *
     */
    public function deleteAction( Request $request, $id )
    {
        $helpers  = $this->get("app.helpers", null);
        $em       = $this->getDoctrine()->getManager();
        $Images   = $em->getRepository('BackendBundle:Image')->find( $id );

        if ( $Images !== null ) {
            $em->remove( $Images );
            $em->flush();

            $msg      = 'Image deleted';
            $data     = $helpers->responseData(200, $msg);
            $response = $helpers->responseHeaders(200, $data);
        } else {
            $msg      = 'Error. Image not found in DB';
            $data     = $helpers->responseData(404, $msg);
            $response = $helpers->responseHeaders(404, $data);
        }
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Descripcion especifica de este metodo
     *
     * @ApiDoc(
     *  section = "Images",
     *  description="Create slug",
     *     requirements={
     *      {"name"="title", "dataType"="string", "required"=false, "description"="Slug of image"}
     *    },
     *     headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function slugAction( Request $request )
    {
        $helpers = $this->get("app.helpers");
        $title   = $request->get("title", null);
        if ( $title !== null && $title != "{title}" ) {
            $slug       = Urlizer::urlize( $title );
            $contador   = 0;
            $slug_final = $slug;
            while( $this->imageSlugValidate( $slug_final ) ){
                $slug_final = $slug . ++$contador;
            }
            $data["status"] = 'success';
            $data["code"]   = 200;
            $data["data"]   = $slug_final;
        } else {
            $msg = "Slug isn't generated!";
            $data = $helpers->responseData(400, $msg);
        }

        return $helpers->json($data);
    }

    private function imageSlugValidate($slug)
    {
        $em   = $this->getDoctrine()->getManager();
        $page = $em->getRepository('BackendBundle:Image')->findOneBySlug( $slug );
        return ( ( $page == NULL ) ? FALSE : TRUE );
    }

    private function delete( $file_path=null, $file_name=null ){
        $fs = new Filesystem();

        if ( $file_path !== null && $file_name !== null ) {
            return ( $fs->remove( $this->get('kernel')->getRootDir() . "/../web/" . $file_path . "/" . $file_name ) );
        }else{
            return false;
        }
    }

    /**
     * Descripcion especifica de este metodo
     *
     * @ApiDoc(
     *  section = "Images",
     *  description="It converts disc location to a reachable url",
     *     requirements={
     *      {"name"="file", "dataType"="array", "required"=true, "description"="file path"},
     *    },
     *     headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function getUrlAction( Request $request )
    {
        $helpers = $this->get("app.helpers");
        $path    = $request->get("file", null);

        $response = $this->getUrlFromLocalPath( $path );
        $response->headers->set('Content-Type', 'application/json');
        if ( $response !== null ){
            $data = array(
                "status" => "success",
                "code"   => 200,
                "path"   => $response
            );

            return $helpers->json($data);
        }else{
            $msg      = 'No image url';
            $data     = $helpers->responseData(400, $msg);
            $response = $helpers->responseHeaders(400, $data);

            return $response;
        }
    }

    /**
     * Transforms local path to an url reachable
     * */
    private function getUrlFromLocalPath( $path ){
        $result = "";

        if ( $path !== null ) {
            $path   = str_replace( '\\', "", $path );
            $cdn    = $this->container->getParameter('cdn');
            $result = str_replace( $cdn, "", $path );
            $result = "/uploads" . $result;

            return $result;
        }else{

            return null;
        }
    }

    /**
     * @param $Images array
     * @return array
     */
    private function formatOutput( $Images ){
        $result = array();

        foreach ( $Images as $key => $value ){
            $result = array(
                "id"          => $value->getId(),
                "createdAt"   => $value->getCreatedAt()->format('y-M-d H:m'),
                "updatedAt"   => $value->getUpdatedAt()->format('y-M-d H:m'),
                "slug"        => $value->getSlug(),
                "title"       => $value->getTitle(),
                "description" => $value->getDescription(),
                "sourceId"    => $value->getSourceId(),
                "imageName"   => $value->getImageName(),
                "credito"     => $value->getCredito(),
                "portalId"    => $value->getPortalId(),
                "gallery"     => $value->getGallery(),
                "tag"         => $value->getTag(),
                "footnote"    => $value->getFootnote(),
                "author"      => $value->getAuthor(),
                "sourcecat"   => $value->getSourcecat(),
                "imagePath"   => $value->getImagePath(),
                "type"        => $value->getType(),
            );
        }

        return $result;
    }

    private function setParams( $params ){
        $params->title       = ( isset( $params->title ) )       ? $params->title : null;
        $params->description = ( isset( $params->description ) ) ? $params->description : null;
        $params->slug        = ( isset( $params->slug ) )        ? $params->slug : null;
        $params->footnote    = ( isset( $params->footnote ) )    ? $params->footnote : null;
        $params->credito      = ( isset( $params->credito ) )      ? $params->credito : null;
        $params->sourcecat   = ( isset( $params->sourcecat ) )   ? $params->sourcecat : null;
        $params->tags        = ( isset( $params->tags ) )        ? $params->tags : null;
        $params->type        = ( isset( $params->type ) )        ? $params->type : null;
        $params->generateVersions = ( isset( $params->generateVersions ) )        ? $params->generateVersions : null;

        return $params;
    }
}
