<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use BackendBundle\Entity\Blog;

/**
 * Columna controller.
 *
 */
class BlogController extends Controller
{
    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  section = "Blog",
     *  description="Listado de Blog de autores o x ID(es el Catálogo, NO son Textos publicados)",
     *  requirements={
     *      {"name"="page", "dataType"="int", "required"="true", "default"="1", "description"="numero de pagina, si se omite es 1"},
     *      {"name"="size", "dataType"="int", "required"="true", "default"="10", "description"="numero de items, si se omite es 10"}
     *   },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function indexAction(Request $request, $idblog)
    {

        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        if ($idblog == NULL || $idblog == '{idblog}') //2o caso para aceptar el parametro que le pone swagger
        {

            $page = $request->get("page", 1);
            $size = $request->get("size", 10);

            $blogs = $em->getRepository('BackendBundle:Blog')->findAll();

            $paginator = $this->get("knp_paginator");
            $items_per_page = $size;

            $pagination = $paginator->paginate($blogs, $page, $items_per_page);
            $total_items_count = $pagination->getTotalItemCount();

            $list = true;
            $data = $helpers->responseData($code = 200, $msg = null, $list, $pagination, $total_items_count, $page, $items_per_page);
        } else {
            $blog = $em->getRepository('BackendBundle:Blog')->find($idblog);

            if (count($blog) != 0) {
                $data = array(
                    "status" => "success",
                    "data" => $blog
                );

            } else {
                $msg = "Blog not found in DB";
                $data = $helpers->responseData($code = 404, $msg);
                $response = $helpers->responseHeaders($code = 404, $data);

                return $response;
            }

        }

        return $helpers->json($data);

    }

    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  section = "Blog",
     *  description="Creación de Blog de autor( NO es un texto de blogpost )",
     *  requirements={
     *     {"name"="title", "dataType"="string", "required"="true",  "description"="Title Blog"},
     *     {"name"="description", "dataType"="string", "required"="true",  "description"="Description blog"},
     *     {"name"="indentidad", "dataType"="string", "required"="false",  "description"="Identidad"},
     *     {"name"="slug", "dataType"="string", "required"="false",  "description"="Slug"},
     *     {"name"="imagen", "dataType"="string", "required"="true",  "description"="Ruta con el nombre de la imagen"},
     *     {"name"="metadatos", "dataType"="string", "required"="true",  "description"="Metadatos"},
     *     {"name"="autores", "dataType"="string", "required"="true",  "description"="Arreglo de ID de autores"},
     *     {"name"="active", "dataType"="string", "required"="true",  "description"="Activa (true o false)"}
     *   },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function newAction(Request $request)
    {

        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        $title = $request->get('title');
        $description = $request->get('description');
        $identidad = $request->get('identidad');
        $metadatos = $request->get('metadatos');
        $file = $request->files->get('imagen');
        $autores = $request->get('autores');
        $active = $request->get('active');
        $slug = $request->get('slug');

        $blog = new Blog();

        $blog->setTitle($title);
        $blog->setDescription($description);
        $blog->setMetadatos($metadatos);
        $blog->setActive($active);
        $blog->setIdentidad($identidad);
        $blog->setSlug($slug);
        $blog->setCreatedAt(new \DateTime());
        $blog->setUpdatedAt(new \DateTime());

        if ($autores) {
            $array_autores = json_decode($autores);
            foreach ($array_autores as $idauthor) {
                //Si el Id es igual a -1, significa que escribio texto simple.
                if ($idauthor != -1) {
                    $elauthor = $em->getRepository('BackendBundle:Author')->find($idauthor);
                    if ($elauthor != null) {
                        $blog->addAuthor($elauthor);
                    }
                }
            }
        }

        $cdn = $this->container->getParameter('cdn');
        $file_uploaded = $helpers->upload($cdn, $file);
        if ($file_uploaded["result"]) {                            //Is it uploaded?
            $url = $helpers->getUrlFromLocalPath($cdn, $file_uploaded["path"] . "/" . $file_uploaded["name"]);
            $blog->setImageName($url);
        }

        $validator = $this->get('validator');
        $errors = $validator->validate($blog);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            $data = $helpers->responseData($code = 400, $messages);
            $response = $helpers->responseHeaders($code = 400, $data);
        } else {

            $em = $this->getDoctrine()->getManager();
            $em->persist($blog);
            $em->flush();

            $data = $helpers->responseData($code = 200, "Blog created");
            $response = $helpers->responseHeaders($code = 200, $data);
        }

        return $response;

    }

    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  section = "Blog",
     *  description="Edicion del Blog de autor( NO es un texto de blogpost )",
     *  requirements={
     *     {"name"="id", "dataType"="string", "required"="true",  "description"="Id Blog"},
     *     {"name"="title", "dataType"="string", "required"="true",  "description"="Title Blog"},
     *     {"name"="description", "dataType"="string", "required"="true",  "description"="Description blog"},
     *     {"name"="indentidad", "dataType"="string", "required"="false",  "description"="Identidad"},
     *     {"name"="slug", "dataType"="string", "required"="false",  "description"="Slug"},
     *     {"name"="imagen", "dataType"="string", "required"="true",  "description"="Ruta con el nombre de la imagen"},
     *     {"name"="metadatos", "dataType"="string", "required"="true",  "description"="Metadatos"},
     *     {"name"="autores", "dataType"="string", "required"="true",  "description"="Arreglo de ID de autores"},
     *     {"name"="active", "dataType"="string", "required"="true",  "description"="Activa (true o false)"}
     *   },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function editAction(Request $request,$id)
    {
        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        $title = $request->get('title');
        $description = $request->get('description');
        $identidad = $request->get('identidad');
        $metadatos = $request->get('metadatos');
        $file = $request->files->get('imagen');
        $autores = $request->get('autores');
        $active = $request->get('active');
        $slug = $request->get('slug');

        $blog = $em->getRepository("BackendBundle:Blog")->find($id);

        $blog->setTitle($title);
        $blog->setDescription($description);
        $blog->setMetadatos($metadatos);
        if($active != null){
            if($active == 'true'){
                $blog->setActive(1);
            }else{
                $blog->setActive(0);
            }

        }
        $blog->setIdentidad($identidad);
        $blog->setSlug($slug);
        $blog->setUpdatedAt(new \DateTime());

        $authors_db = $blog->getAuthor();
        foreach ($authors_db as $val) {
            $blog->removeAuthor($val);
        }

        if ($autores) {
            $array_autores = json_decode($autores);
            foreach ($array_autores as $idauthor) {
                //Si el Id es igual a -1, significa que escribio texto simple.
                if ($idauthor != -1) {
                    $elauthor = $em->getRepository('BackendBundle:Author')->find($idauthor);
                    if ($elauthor != null) {
                        $blog->addAuthor($elauthor);
                    }
                }
            }
        }

        $cdn = $this->container->getParameter('cdn');
        $file_uploaded = $helpers->upload($cdn, $file);
        //Si subio la imagen, entonces actualizo la ruta de la imaagen
        if ($file_uploaded["result"]) {                            //Is it uploaded?
            $url = $helpers->getUrlFromLocalPath($cdn, $file_uploaded["path"] . "/" . $file_uploaded["name"]);
            $blog->setImageName($url);
        }

        $validator = $this->get('validator');
        $errors = $validator->validate($blog);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            $data = $helpers->responseData($code = 400, $messages);
            $response = $helpers->responseHeaders($code = 400, $data);
        } else {

            $em = $this->getDoctrine()->getManager();
            $em->persist($blog);
            $em->flush();

            $data = $helpers->responseData($code = 200, "Blog updated");
            $response = $helpers->responseHeaders($code = 200, $data);
        }

        return $response;

    }

}
