<?php

namespace BackendBundle\Controller;

use BackendBundle\Entity\Tipofolds;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


/**
 * Columna controller.
 *
 */
class TipofoldsController extends Controller
{
    /**
     * Descripcion especifica de este metodo
     *
     * @ApiDoc(
     *  section = "Tipofolds",
     *  description="Listado de tipofolds",
     *  requirements={
     *      {"name"="id",   "dataType"="int", "required"="false", "description"="id"},
     *      {"name"="page", "dataType"="int", "required"="true",  "default"="1",  "description"="numero de pagina, si se omite es 1"},
     *      {"name"="size", "dataType"="int", "required"="true",  "default"="10", "description"="numero de items, si se omite es 10"}
     *   },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function indexAction(Request $request, $id)
    {
        $helpers = $this->get("app.helpers");
        $em      = $this->getDoctrine()->getManager();

        if ($id == NULL || $id == '{id}') //2o caso para aceptar el parametro que le pone swagger
        {
            $page              = $request->get("page", 1);
            $size              = $request->get("size", 10);
            $types             = $em->getRepository('BackendBundle:Tipofolds')->findAll();
            $paginator         = $this->get("knp_paginator");
            $items_per_page    = $size;
            $pagination        = $paginator->paginate($types, $page, $items_per_page);
            $total_items_count = $pagination->getTotalItemCount();
            $list              = true;
            $data              = $helpers->responseData(200, $msg = null, $list, $pagination, $total_items_count, $page, $items_per_page);
        } else {
            $types = $em->getRepository('BackendBundle:Tipofolds')->findById( $id );
            if ( count( $types ) != 0) {
                $data = array(
                    "status" => "success",
                    "data"   => $types
                );
            } else {
                $msg      = "Tipofold not found in DB";
                $data     = $helpers->responseData(404, $msg);
                $response = $helpers->responseHeaders(404, $data);

                return $response;
            }
        }

        return $helpers->json($data);
    }

    /**
     * Descripcion especifica de este metodo
     *
     * @ApiDoc(
     *  section = "Tipofolds",
     *  description="Creación de Tipofolds",
     *  requirements={
     *     {"name"="descripcion","dataType"="string","required"="true","description"="Descripcion"},
     *   },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function newAction(Request $request)
    {
        $helpers     = $this->get("app.helpers");
        $em          = $this->getDoctrine()->getManager();
        $descripcion = $request->get('descripcion');

        $type      = new Tipofolds();
        $type->setDescripcion( $descripcion );
        $validator = $this->get('validator');
        $errors    = $validator->validate($type);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            $data     = $helpers->responseData(400, $messages);
            $response = $helpers->responseHeaders(400, $data);
        } else {
            $em->persist($type);
            $em->flush();
            $data     = $helpers->responseData(200, "Tipofold created");
            $response = $helpers->responseHeaders(200, $data);
        }

        return $response;
    }

    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  section = "Tipofolds",
     *  description="Edicion de Tipofolds",
     *  requirements={
     *     {"name"="id",         "dataType"="string",   "required"="true",  "description"="Id"},
     *     {"name"="descripcion","dataType"="string",   "required"="true",  "description"="Descripcion"},
     *   },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function editAction(Request $request, $id)
    {
        $helpers     = $this->get("app.helpers");
        $em          = $this->getDoctrine()->getManager();
        $id          = $request->get('id');
        $descripcion = $request->get('descripcion');
        $type        = $em->getRepository("BackendBundle:Tipofolds")->find( $id );

        $type->setDescripcion( $descripcion );
        $validator = $this->get('validator');
        $errors    = $validator->validate($type);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            $data     = $helpers->responseData(400, $messages);
            $response = $helpers->responseHeaders(400, $data);
        } else {
            $em->persist( $type );
            $em->flush();
            $data     = $helpers->responseData(200, "Tipofold updated");
            $response = $helpers->responseHeaders(200, $data);
        }

        return $response;

    }

    /**
     * Metodo para borrar un Fold
     * @ApiDoc(
     *     section = "Tipofolds",
     *  description="Metodo para eliminar un Tipofolds",
     *  requirements={
     *      {"name"="id", "dataType"="string", "required"=true, "description"="id"}
     *  },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function deleteAction(Request $request, $id)
    {
        $helpers = $this->get("app.helpers");
        $em      = $this->getDoctrine()->getManager();
        $fold    = $em->getRepository("BackendBundle:Tipofolds")->find($id);

        if ($fold !== null) {
            $em->remove($fold);
            $em->flush();
            $msg      = 'Tipofold Deleted.';
            $data     = $helpers->responseData(200, $msg);
            $response = $helpers->responseHeaders(200, $data);
        } else {
            $msg      = 'Tipofold not found in DB.';
            $data     = $helpers->responseData(404, $msg);
            $response = $helpers->responseHeaders(404, $data);
        }

        return $response;
    }
}
