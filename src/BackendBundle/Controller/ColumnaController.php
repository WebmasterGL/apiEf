<?php

namespace BackendBundle\Controller;

use BackendBundle\Entity\Columna;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Gedmo\Sluggable\Util\Urlizer;

/**
 * Columna controller.
 *
 */
class ColumnaController extends Controller
{
    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  section = "Columna",
     *  description="Listado de Columnas de autores o x ID(es el Catálogo, NO son Textos publicados)",
     *  requirements={
     *      {"name"="page", "dataType"="int", "required"="true", "default"="1", "description"="numero de pagina, si se omite es 1"},
     *      {"name"="size", "dataType"="int", "required"="true", "default"="10", "description"="numero de items, si se omite es 10"}
     *   },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function indexAction(Request $request, $idcolumna)
    {

        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        if ($idcolumna == NULL || $idcolumna == '{idcolumna}') //2o caso para aceptar el parametro que le pone swagger
        {

            $page = $request->get("page", 1);
            $size = $request->get("size", 10);

            $columnas = $em->getRepository('BackendBundle:Columna')->findAll();

            $paginator = $this->get("knp_paginator");
            $items_per_page = $size;

            $pagination = $paginator->paginate($columnas, $page, $items_per_page);
            $total_items_count = $pagination->getTotalItemCount();

            $list = true;
            $data = $helpers->responseData(200, null, $list, $pagination, $total_items_count, $page, $items_per_page);
        } else {
            $columna = $em->getRepository('BackendBundle:Columna')->find($idcolumna);

            if (count($columna) != 0) {

                $data = array(
                    "status" => "success",
                    "data" => $columna
                );

            } else {
                $msg = "Columna not found";
                $data = $helpers->responseData(404, $msg);
                $response = $helpers->responseHeaders(404, $data);

                return $response;
            }

        }

        return $helpers->json($data);

    }

    private function assignDetails($columna, Request $request, $flag)
    {
        $helpers = $this->get('app.helpers');
        $em = $this->getDoctrine()->getManager();

        $columna->setNombre($request->get('nombre'));
        $columna->setNombreSistema($request->get('nombreSistema'));
        $columna->setSeo(json_decode($request->get('seo')));
        $columna->setSocial(json_decode($request->get('social')));

        if ($flag == 'create') {
            $columna->setCreatedAt(new \DateTime());
            $columna->setUpdatedAt(new \DateTime());
            if ($request->get('slug')) {
                $slug_db = $em->getRepository('BackendBundle:Columna')->findOneBySlug(Urlizer::urlize($request->get('slug')));
                if ($slug_db != null) {
                    $msg = "Slug exist in DB";
                    $data = $helpers->responseData(400, $msg);
                    $response = $helpers->responseHeaders(400, $data);
                    return $response;
                } else {
                    $slug_final = $this->createSlug($request->get('slug'));
                    $columna->setSlug($slug_final['value']);
                }
            }
        } else {
            $columna->setUpdatedAt(new \DateTime());
        }

        if($flag == 'update'){
            if($request->get('slug') && $columna->getActivatedAt() == null){
                $columna->setSlug($request->get('slug'));
            }
        }

        if ($request->get('active') == 'true') {
            $columna->setActive(1);
            $columna->setActivatedAt(new \DateTime());
        } else {
            $columna->setActive(0);
        }

        return $columna;

    }

    private function slugValidate($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('BackendBundle:Columna')->findOneBySlug($slug);
        return (($page == NULL) ? FALSE : TRUE);
    }

    private function createSlug($slug_front)
    {
        if ($slug_front != NULL) {

            $slug = Urlizer::urlize($slug_front);
            $contador = 0;
            $slug_final = $slug;
            while ($this->slugValidate($slug_final)) {
                $slug_final = $slug . ++$contador;
            }
            return array(
                'slug' => TRUE,
                'value' => $slug_final,
            );
        }
        return array(
            'slug' => FALSE,
            'value' => ''
        );

    }

    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  section = "Columna",
     *  description="Creación de Columna de autor( NO es un texto de columna )",
     *  requirements={
     *      {"name"="nombre", "dataType"="string", "required"="false",  "description"="Nombre Opcional de la columna"},
     *      {"name"="nombreSistema", "dataType"="string", "required"="true",  "description"="Nombre Obligatorio de la columna, puede ser el nombre del autor"},
     *      {"name"="slug", "dataType"="string", "required"="true",  "description"="Slug de la columna"},
     *      {"name"="seo", "dataType"="json", "required"=true, "description"="SEO JSON"},
     *      {"name"="social", "dataType"="json", "required"=true, "description"="Social JSON"},
     *      {"name"="authors", "dataType"="string", "required"="true",  "description"="Arreglo de ID de autores"},
     *      {"name"="image", "dataType"="string", "required"="true",  "description"="ID IMAGE"},
     *      {"name"="active", "dataType"="string", "required"="true",  "description"="Activa (true o false)"}
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

        $autores = $request->get('authors');
        $imagen = $request->get('image');
        $slug = $request->get('slug');

        $columna = new Columna();

        $columna = $this->assignDetails($columna, $request, $flag = "create");

        if ($autores) {
            $array_autores = json_decode($autores);

            foreach ($array_autores as $idauthor) {
                $elauthor = $em->getRepository('BackendBundle:Author')->find($idauthor);
                if ($elauthor != null) {
                    $columna->addAuthors($elauthor);
                }
            }
        }
        if ($imagen != null) {
            $image = $em->getRepository('BackendBundle:Image')->find($imagen);
            $columna->setImage($image);
        } else {
            $def_img = $em->getRepository('BackendBundle:Image')->findOneBy( //select default image
                array(
                    "title" => "Foto Default de Columna",
                    "description" => "Foto Default de Columna",
                    "credito" => "Especial"
                )
            );
            $columna->setImage($def_img);
        }

        $validator = $this->get('validator');
        $errors = $validator->validate($columna);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            $data = $helpers->responseData($code = 400, $messages);
            $response = $helpers->responseHeaders($code = 400, $data);
        } else {

            $em = $this->getDoctrine()->getManager();
            $em->persist($columna);
            $em->flush();

            $msg = "Column created";
            $data = $helpers->responseData($code = 200, $msg);
            $data['column_id'] = $columna->getId();

            $response = $helpers->responseHeaders(200, $data);
        }


        return $response;

    }


    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  section = "Columna",
     *  description="Edición de una Columna de autor( NO es un texto de columna )",
     *  requirements={
     *      {"name"="nombre", "dataType"="string", "required"="false",  "description"="Nombre Opcional de la columna"},
     *      {"name"="nombreSistema", "dataType"="string", "required"="true",  "description"="Nombre Obligatorio de la columna, puede ser el nombre del autor"},
     *      {"name"="slug", "dataType"="string", "required"="true",  "description"="Slug de la columna"},
     *      {"name"="seo", "dataType"="json", "required"=true, "description"="SEO JSON"},
     *      {"name"="social", "dataType"="json", "required"=true, "description"="Social JSON"},
     *      {"name"="authors", "dataType"="string", "required"="true",  "description"="Arreglo de ID de autores"},
     *      {"name"="image", "dataType"="string", "required"="true",  "description"="ID IMAGE"},
     *      {"name"="active", "dataType"="string", "required"="true",  "description"="Activa (true o false)"}
     *   },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function editAction(Request $request, $id)
    {

        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        $imagen = $request->get('image');

        $columna = $em->getRepository("BackendBundle:Columna")->findOneBy(array(
            "id" => $id
        ));

        if ($columna != null) {

            $columna = $this->assignDetails($columna, $request, $flag = "update");

            $authors_db = $columna->getAuthors();
            foreach ($authors_db as $val) {
                $columna->removeAuthors($val);
            }

            $array_autores = json_decode($request->get('authors'));

            foreach ($array_autores as $idauthor) {
                $elauthor = $em->getRepository('BackendBundle:Author')->find($idauthor);
                if ($elauthor != null) {
                    $columna->addAuthors($elauthor);
                }
            }

            if ($imagen != null) {
                $image = $em->getRepository('BackendBundle:Image')->find($imagen);
                $columna->setImage($image);
            } else {
                $def_img = $em->getRepository('BackendBundle:Image')->findOneBy( //select default image
                    array(
                        "title" => "Foto Default de Columna",
                        "description" => "Foto Default de Columna",
                        "credito" => "Especial"
                    )
                );
                $columna->setImage($def_img);
            }

            $validator = $this->get('validator');

            $errors = $validator->validate($columna);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }
                $data = $helpers->responseData(400, $messages);
                $response = $helpers->responseHeaders(400, $data);
            } else {

                $em = $this->getDoctrine()->getManager();
                $em->persist($columna);
                $em->flush();

                $msg = "Column updated";
                $data = $helpers->responseData($code = 200, $msg);
                $data['column_id'] = $columna->getId();
                $response = $helpers->responseHeaders(200, $data);
            }

        } else {
            $msg = 'La Columna no existe';
            $data = $helpers->responseData(404, $msg);
            $response = $helpers->responseHeaders(404, $data);
        }


        return $response;

    }

    /**
     * Deletes a columna entity.
     *
     */
    public function deleteAction(Request $request, Columna $columna)
    {
        $form = $this->createDeleteForm($columna);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($columna);
            $em->flush();
        }

        return $this->redirectToRoute('columna_index');
    }

    /**
     * Creates a form to delete a columna entity.
     *
     * @param Columna $columna The columna entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Columna $columna)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('columna_delete', array('id' => $columna->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
