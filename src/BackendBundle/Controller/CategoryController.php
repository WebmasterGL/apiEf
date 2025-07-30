<?php
/**
 * Created by PhpStorm.
 * User: javiermorquecho
 * Date: 27/06/17
 * Time: 13:21
 */

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use BackendBundle\Entity\Category;
use BackendBundle\Entity\Image;
use Gedmo\Sluggable\Util\Urlizer;

class CategoryController extends Controller
{
    /**
     * Descripcion especifica de este metodo
     *
     * @ApiDoc(
     *     section = "Category",
     *     description="Show an specific category or all categories",
     *     requirements={
     *     {"name"="page", "dataType"="int", "required"="false", "default"="1", "description"="numero de pagina, si se omite es 1"},
     *     {"name"="size", "dataType"="int", "required"="false", "default"="10", "description"="Tamaño de la pagina, si se omite es 10"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function getAllAction(Request $request, $idcategory)
    {
        $helpers = $this->get('app.helpers');
        $users = $this->get('app.users');
        $em      = $this->getDoctrine()->getManager();


        if($idcategory==NULL || $idcategory=='{idcategory}') //2o caso para aceptar el parametro que le pone swagger
        {

            $page = $request->get("page", 1);
            $size = $request->get("size", 10);


            $data = $users->getCategoriesUserLogged(); //Servicio que regresa las categorias de un usuario



            if($data["fullAccess"]) {

                $cats = $em->getRepository('BackendBundle:Category')->findAll();
            }
            else{

                $cats_ids = array();

                foreach ($data["categories"] as $l_cat)
                {
                    array_push($cats_ids, $l_cat->getId());

                }


                $cats = $em->getRepository('BackendBundle:Category')->findById($cats_ids);
            }

            $paginator = $this->get("knp_paginator");
            $items_per_page = $size;
            $pagination = $paginator->paginate($cats, $page, $items_per_page);
            $total_items_count = $pagination->getTotalItemCount();
            $list = true;
            $data = $helpers->responseData( 200, null, $list, $pagination, $total_items_count, $page, $items_per_page);
        }
        else{



            $data = $users->getCategoriesUserLogged($idcategory);

            if($data["isValidCategory"] || $data["fullAccess"] ) {
                $cat = $em->getRepository('BackendBundle:Category')->findBy(array('id' => $idcategory));
                $data = array(
                    "status" => "success",
                    "total_items_count" => 1,
                    "page_actual" => 1,
                    "items_per_page" => 1,
                    "data" => $cat
                );
            }
            else{

                $data = $helpers->responseData( 401, "No tienes permiso para esta Seccion");
                $response = $helpers->responseHeaders( 401, $data);
                return $response;
            }
        }

        return $helpers->json($data);

    }

    /**
     * Descripcion especifica de este metodo
     *
     * @ApiDoc(
     *  section = "Category",
     *  description="Add new category",
     *     requirements={
     *      {"name"="parent",       "dataType"="integer", "required"=true, "description"="Parent id"},
     *      {"name"="title",        "dataType"="string", "required"=true, "description"="Title"},
     *      {"name"="slug",         "dataType"="string", "required"=true, "description"="Slug"},
     *      {"name"="portal_id",    "dataType"="integer", "required"=true, "description"="Portal Id"},
     *      {"name"="description",  "dataType"="string",  "required"=true, "description"="Metadata"},
     *      {"name"="image",        "dataType"="string",  "required"=true, "description"="Img id"},
     *      {"name"="active",       "dataType"="boolean", "required"=true, "description"="Is active? [true|false]"},
     *      {"name"="color",        "dataType"="string",  "required"=true, "description"="Color"},
     *      {"name"="svg",          "dataType"="string",  "required"=true, "description"="Svg"},
     *      {"name"="slugredirect", "dataType"="string",  "required"=true, "description"="Slugredirect"},
     *      {"name"="wallpaper",    "dataType"="string",  "required"=false, "description"="Wallpaper"},
     *    },
     *     headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function createAction(Request $request)
    {
        $helpers = $this->get('app.helpers');

        $parent        = $request->get("parent", null);
        $title         = $request->get("title", null);
        $portal_id     = $request->get("portal_id", null);
        $slug          = $request->get("slug", null);
        $description   = $request->get("description", null);
        $image         = $request->get('image',null );
        $active        = $request->get("active", null);
        $color         = $request->get("color", null);
        $svg           = $request->get("svg", null);
        $slugredirect  = $request->get("slugredirect", null);
        $wallpaper         = $request->get('wallpaper',null );

        $em       = $this->getDoctrine()->getManager();
        $img      = $em->getRepository("BackendBundle:Image")->findOneBy(
            array(
                "id" => $image
            )
        );

        $wallpaper_db      = $em->getRepository("BackendBundle:Image")->findOneBy(
            array(
                "id" => $wallpaper
            )
        );

        $category_exist_slug = $em->getRepository("BackendBundle:Category")->findOneBy(
            array(
                'slug' => $slug

            ));

        $category_exist = $em->getRepository("BackendBundle:Category")->findOneBy(
            array(
                'title' => $title,
                'parentId' => $parent
            ));

        if(count($category_exist_slug) > 0){
            $data     = $helpers->responseData(400, 'Category Exist with the same slug in DB');
            $response = $helpers->responseHeaders(400, $data);

        }elseif (count($category_exist) > 0){
            $data     = $helpers->responseData(400, 'Category Exist with the same title and parent Id in DB');
            $response = $helpers->responseHeaders(400, $data);
        }else{
            $response = $this->modifyEntity( null, $parent, $title, $slug, $portal_id, $description, $active, $img, $color, $svg, $slugredirect, $wallpaper_db );

        }

        return $response;

    }

    /**
     * Descripcion especifica de este metodo
     *
     * @ApiDoc(
     *  section = "Category",
     *  description="Update category",
     *     requirements={
     *      {"name"="id",           "dataType"="integer", "required"=true, "description"="Id"},
     *      {"name"="parent",       "dataType"="integer", "required"=true, "description"="Parent id"},
     *      {"name"="title",        "dataType"="string",  "required"=true, "description"="Title"},
     *      {"name"="slug",         "dataType"="string",  "required"=true, "description"="Slug"},
     *      {"name"="portal_id",    "dataType"="integer", "required"=true, "description"="Portal Id"},
     *      {"name"="description",  "dataType"="string",  "required"=true, "description"="Metadata"},
     *      {"name"="image",        "dataType"="string",  "required"=true, "description"="Img id"},
     *      {"name"="wallpaper",    "dataType"="string",  "required"=false, "description"="Wallpaper id"},
     *      {"name"="active",       "dataType"="boolean", "required"=true, "description"="Is active? [true|false]"},
     *      {"name"="color",        "dataType"="string",  "required"=true, "description"="Color"},
     *      {"name"="svg",          "dataType"="string",  "required"=true, "description"="Svg"},
     *      {"name"="slugredirect", "dataType"="string",  "required"=true, "description"="Slugredirect"},
     *    },
     *     headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function categoriesAction(Request $request, $id)
    {
        $helpers      = $this->get("app.helpers");
        $parent       = $request->get("parent", null);
        $title        = $request->get("title", null);
        $slug         = $request->get("slug", null);
        $description  = $request->get("description", null);
        $image        = $request->get('image',null );
        $wallpaper    = $request->get('wallpaper',null );
        $portal_id    = $request->get("portal_id", null);
        $active       = $request->get("active", null);
        $color        = $request->get("color", null);
        $svg          = $request->get("svg", null);
        $slugredirect = $request->get("slugredirect", null);

        $em       = $this->getDoctrine()->getManager();
        $category = $em->getRepository("BackendBundle:Category")->find($id);
        if ( count( $category ) > 0 ) {
            $img = $em->getRepository("BackendBundle:Image")->findOneBy(
                array(
                    "id" => $image
                )
            );
            $wallpaper_db = $em->getRepository("BackendBundle:Image")->findOneBy(
                array(
                    "id" => $wallpaper
                )
            );
            $response = $this->modifyEntity( $category, $parent, $title, $slug, $portal_id, $description, $active, $img, $color, $svg, $slugredirect, $wallpaper_db );
        } else {
            $msg      = 'Category not found in DB.';
            $data     = $helpers->responseData(404, $msg);
            $response = $helpers->responseHeaders(400, $data);
        }

        return $response;
    }

    /**
     * Descripcion especifica de este metodo
     *
     * @ApiDoc(
     *  section = "Category",
     *  description="Delete category",
     *     requirements={
     *      {"name"="id", "dataType"="integer", "required"=true, "description"="Id"},
     *    },
     *     headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function deleteAction($id)
    {
        $helpers = $this->get("app.helpers");

        $em       = $this->getDoctrine()->getManager();
        $category = $em->getRepository("BackendBundle:Category")->find($id);
        if ( $category !== null ) {
            $em = $this->getDoctrine()->getManager();
            $em->remove( $category );
            $em->flush();

            $msg = 'Category deleted.';
            $data = $helpers->responseData(200, $msg);
        } else {
            $msg = 'Category not found, in DB.';
            $data = $helpers->responseData(404, $msg);
        }

        return $helpers->json($data);
    }

    /**
     * Descripcion especifica de este metodo
     *
     * @ApiDoc(
     *  section = "Category",
     *  description="Create slug",
     *     requirements={
     *      {"name"="s1", "dataType"="string", "required"="false", "description"="title or parent", "default"=""},
     *      {"name"="s2", "dataType"="string", "required"="false", "description"="title of second level", "default"=""}
     *    },
     *     headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function slugAction( $s1, $s2 )
    {
        $helpers    = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();
        $title      = "";
        $slug_final = "";
        if ( $s1 == "{s1}" ){
            $s1 = "";
        }
        if ( $s2 == "{s2}" ){
            $s2 = "";
        }
        if ( $s2 != "" ){
            $slug = Urlizer::urlize( $s1 ) . "/" . Urlizer::urlize( $s2 );
            $slug_exist =  $em->getRepository('BackendBundle:Category')->findOneBy(
                array(
                    'slug' => $slug
                ));

            if(count($slug_exist) > 0){
                $data     = $helpers->responseData(400, "Slug exist in DB");
                $response = $helpers->responseHeaders(400, $data);
                return $response;
            }
            $slug_final = $slug;
        }else{
            $slug       = Urlizer::urlize( $s1 );

            $slug_exist =  $em->getRepository('BackendBundle:Category')->findOneBy(
                array(
                    'slug' => $slug
                ));
            if(count($slug_exist) > 0){
                $data     = $helpers->responseData(400, "Slug exist in DB");
                $response = $helpers->responseHeaders(400, $data);
                return $response;
            }
            $slug_final = $slug;
        }
        if ( $slug_final != "" ) {
            $data["status"] = 'success';
            $data["code"]   = 200;
            $data["data"]   = $slug_final;
        } else {
            $msg = "Slug isn't generated!";
            $data = $helpers->responseData(400, $msg);
        }

        return $helpers->json($data);
    }

    private function categorySlugValidate($slug)
    {
        $em   = $this->getDoctrine()->getManager();
        $page = $em->getRepository('BackendBundle:Category')->findOneBySlug( $slug );
        return ( ( $page == NULL ) ? FALSE : TRUE );
    }

    /**
     * @desc modify entity category for insert and update
     * @param $categorySent object
     * @param $parent integer
     * @param $title string
     * @param $slug string
     * @param $portal_id integer
     * @param $description string
     * @param $active integer[1|0]
     * @param $img object
     * @param $wallpaper object
     * @param $color string
     * @param $svg string
     * @param $slugredirect string
     * @return response
     */
    private function modifyEntity( $categorySent, $parent, $title, $slug, $portal_id, $description, $active, $img, $color, $svg, $slugredirect, $wallpaper ){
        $helpers  = $this->get("app.helpers");
        $em       = $this->getDoctrine()->getManager();

        $def_img      = $em->getRepository('BackendBundle:Image')->findOneBy(                  //select default image
            array(
                "title"       => "Foto Default de Categoria",
                "description" => "Foto Default de Categoria",
                "credito"     => "Especial"
            )
        );

        if ( $active == "true" ){
            $active = 1;
        }else{
            $active = 0;
        }

        if ( $categorySent === null ){
            $category = new Category();
            $category->setActive($active);
            if ( $active == "1" ){
                $category->setCreatedAt(new \DateTime());
            }else{
                $category->setCreatedAt(new \DateTime("0000-00-00 00:00:00"));
            }
            $category->setSlug( $slug );
        }else{
            $category = $categorySent;
            if ( $category->getActive() == 1 ){
                $category->setActive( $active );
                $category->setUpdatedAt( new \DateTime() );
                $em->persist($category);
                $em->flush();
                $data     = $helpers->responseData(200, "Success. Active field updated");
                $response = $helpers->responseHeaders(200, $data);

                return $response;
            }

            if ( $active == "1" ){
                $category->setCreatedAt(new \DateTime());
            }
            $category->setActive( $active );
        }
        $category->setParentId( $parent );
        $category->setTitle( $title );
        $category->setUpdatedAt( new \DateTime() );
        $category->setLft(0);
        $category->setRgt(0);
        $category->setLvl(0);
        $category->setPortalId( $portal_id );
        $category->setDescription( $description );
        $category->setColor( $color );
        $category->setSvg( $svg );
        $category->setSlugRedirect( $slugredirect );
        if (count($img) > 0){
            $category->setImage( $img );
        }else{
            $category->setImage($def_img);
        }
        if(count($wallpaper) > 0){
            $category->setWallpaper( $wallpaper );
        }
        $validator = $this->get('validator');
        $errors    = $validator->validate($category);
        foreach ($errors as $error) {
            $messages[] = $error->getMessage();
        }
        if(count($errors)>0){
            $data     = $helpers->responseData(400, $messages);
            $response = $helpers->responseHeaders(400, $data);
        }else{
            $em->persist($category);
            $em->flush();
            $data     = $helpers->responseData(200, "success");
            $response = $helpers->responseHeaders(200, $data);

            //purge programacion tv
            $res = $helpers->Purga("tv/programacion");
        }

        return $response;
    }
}