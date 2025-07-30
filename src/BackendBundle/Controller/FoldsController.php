<?php

namespace BackendBundle\Controller;

use BackendBundle\Entity\Portada;
use BackendBundle\Entity\PortadaFolds;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use BackendBundle\Entity\Folds;


/**
 * Columna controller.
 *
 */
class FoldsController extends Controller
{
    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  section = "Folds",
     *  description="Listado de Folds",
     *  requirements={
     *      {"name"="id",   "dataType"="int", "required"="false", "description"="Id Sction"},
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
        $em = $this->getDoctrine()->getManager();

        $page = $request->get("page", 1);
        $size = $request->get("size", 10);

        if ($id == NULL || $id == '{id}') //2o caso para aceptar el parametro que le pone swagger
        {

            $folds = $em->getRepository('BackendBundle:Folds')->findAll();

            $folds_format = $this->formatOutput($folds);
            $paginator = $this->get("knp_paginator");
            $items_per_page = $size;
            $pagination = $paginator->paginate($folds_format, $page, $items_per_page);
            $total_items_count = $pagination->getTotalItemCount();
            $list = true;
            $data = $helpers->responseData(200, $msg = null, $list, $pagination, $total_items_count, $page, $items_per_page);
        } else {
            $folds = $em->getRepository('BackendBundle:Folds')->findByCategory($id);
            $folds = $this->formatOutput($folds);
            if (count($folds) != 0) {
                $paginator = $this->get("knp_paginator");
                $items_per_page = $size;
                $pagination = $paginator->paginate($folds, $page, $items_per_page);
                $total_items_count = $pagination->getTotalItemCount();
                $list = true;
                $data = $helpers->responseData(200, $msg = null, $list, $pagination, $total_items_count, $page, $items_per_page);
            } else {
                $msg = "Fold not found in DB";
                $data = $helpers->responseData(404, $msg);
                $response = $helpers->responseHeaders(404, $data);

                return $response;
            }
        }

        return $helpers->json($data);
    }

    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  section = "Folds",
     *  description="Creación de Folds",
     *  requirements={
     *     {"name"="description", "dataType"="string", "required"="true",  "description"="Description"},
     *     {"name"="tipo_id",    "dataType"="integer",  "required"="false",   "description"="Id Tipo de Fold"},
     *     {"name"="category_id",   "dataType"="integer",  "required"="false",  "default"="", "description"="Category Id"}
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
        $description = $request->get('description');
        $tipo_id = $request->get('tipo_id');
        $category_id = $request->get('category_id');

        $fold = new Folds();
        $tipo_fold = $em->getRepository("BackendBundle:Tipofolds")->find($tipo_id);
        $category = $em->getRepository("BackendBundle:Category")->find($category_id);
        $fold->setIdtipo($tipo_fold);
        $fold->setCategory($category);
        $fold->setDescripcion($description);

        $validator = $this->get('validator');
        $errors = $validator->validate($fold);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            $data = $helpers->responseData(400, $messages);
            $response = $helpers->responseHeaders(400, $data);
        } else {
            $em->persist($fold);
            $em->flush();
            $data = $helpers->responseData(200, "Fold created");
            $response = $helpers->responseHeaders(200, $data);
        }

        return $response;
    }

    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  section = "Folds",
     *  description="Edicion de Folds",
     *  requirements={
     *     {"name"="id", "dataType"="string", "required"="true",  "description"="Id Fold"},
     *     {"name"="description", "dataType"="string", "required"="true",  "description"="Description"},
     *     {"name"="tipo_id",    "dataType"="integer",  "required"="false",   "description"="Id Tipo de Fold"},
     *     {"name"="category_id",   "dataType"="integer",  "required"="false",  "default"="", "description"="Category Id"}
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
        $description = $request->get('description');
        $tipo_id = $request->get('tipo_id');
        $category_id = $request->get('category_id');

        $fold = $em->getRepository('BackendBundle:Folds')->find($id);
        if ($fold != null) {
            if ($category_id) {
                $category = $em->getRepository("BackendBundle:Category")->find($category_id);
                $fold->setCategory($category);
            }
            if ($tipo_id) {
                $tipo_fold = $em->getRepository("BackendBundle:Tipofolds")->find($tipo_id);
                $fold->setIdtipo($tipo_fold);
            }
            if ($description) {
                $fold->setDescripcion($description);
            }
            $em->persist($fold);
            $em->flush();

            $data = $helpers->responseData(200, "Fold updated");
            $response = $helpers->responseHeaders(200, $data);
        } else {
            $data = $helpers->responseData(404, "Fold not found in DB");
            $response = $helpers->responseHeaders(404, $data);
        }

        return $response;

    }

    /**
     * Metodo para borrar un Fold
     * @ApiDoc(
     *     section = "Folds",
     *  description="Metodo para eliminar un fold de portada",
     *  requirements={
     *      {"name"="id", "dataType"="string", "required"=true, "description"="id fold"}
     *  },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function deleteAction(Request $request, $id)
    {
        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();
        $fold = $em->getRepository("BackendBundle:Folds")->find($id);

        if ($fold !== null) {
            $em->remove($fold);
            $em->flush();
            $msg = 'Fold Deleted.';
            $data = $helpers->responseData(200, $msg);
            $response = $helpers->responseHeaders(200, $data);
        } else {
            $msg = 'Fold not found in DB.';
            $data = $helpers->responseData(404, $msg);
            $response = $helpers->responseHeaders(404, $data);
        }

        return $response;
    }

    /**
     * @param $Folds array
     * @return array
     */
    private function formatOutput($folds)
    {
        $result = array();

        foreach ($folds as $key => $value) {
            $result[] = array(
                "id" => $value->getId(),
                "name" => $value->getDescripcion(),
                "type" => array(
                    "id" => $value->getIdTipo()->getId(),
                    "name" => $value->getIdTipo()->getDescripcion(),
                )
            );
        }

        return $result;
    }

    /**
     * Metodo para obtener un Fold de una Portada
     * @ApiDoc(
     *  section = "Portada Fold",
     *  description="Metodo para obtener un Fold de una Portada",
     *  requirements={
     *      {"name"="id", "dataType"="string", "required"=true, "description"="id Portada Fold"},
     *  },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function getPortadaFoldUpdateAction(Request $request, $id)
    {

        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        $user_id = $this->getUser()->getId();

        $portada_fold_db = $em->getRepository('BackendBundle:PortadaFolds')->find($id);

        //¿Si Esta duplicado el Fold en la BD?

        //No esta duplicado en la BD
        $portada_fold = new PortadaFolds();
        $portada_fold->setStatus('default');
        $portada_fold->setOrden($portada_fold_db->getOrden());
        $portada_fold->setIdportada($portada_fold_db->getIdPortada());
        $portada_fold->setIdfold($portada_fold_db->getIdFold());
        $portada_fold->setCreatedAt($portada_fold_db->getCreatedAt());
        $portada_fold->setUpdatedAt($portada_fold_db->getUpdatedAt());
        $portada_fold->setContent($portada_fold_db->getContent());
        $portada_fold->setNextPublishedAt($portada_fold_db->getNextPublishedAt());
        $portada_fold->setPublishedAt($portada_fold_db->getPublishedAt());
        $user = $em->getRepository('BackendBundle:WfUser')->find($user_id);
        $portada_fold->setUpdatedBy($user);
        $portada_fold->setCode($portada_fold_db->getCode());
        $em->persist($portada_fold);
        $em->flush();

        $data = array(
            "status" => "success",
            "data" => $portada_fold,
        );

        return $helpers->json($data);

    }
}
