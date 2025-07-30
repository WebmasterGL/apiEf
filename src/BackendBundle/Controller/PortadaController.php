<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use BackendBundle\Entity\Portada;
use BackendBundle\Entity\PortadaFolds;
use BackendBundle\Entity\TopNews;

use BackendBundle\Controller\SearchController as BaseSearchController;

/**
 * Columna controller.
 *
 */
class PortadaController extends BaseSearchController
{
    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  section = "Portada",
     *  description="Listado de Portadas",
     *  requirements={
     *      {"name"="page",     "dataType"="int",       "required"="true",  "default"="1",      "description"="numero de pagina, si se omite es 1"},
     *      {"name"="size",     "dataType"="int",       "required"="true",  "default"="10",     "description"="numero de items, si se omite es 10"},
     *      {"name"="action",   "dataType"="string",    "required"="false", "default"="get",    "description"="[ default:get | update ]"}
     *   },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function indexAction(Request $request, $idportada)
    {

        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        if ($idportada == NULL || $idportada == '{idportada}') //2o caso para aceptar el parametro que le pone swagger
        {
            $page = $request->get("page", 1);
            $size = $request->get("size", 10);
            $portadas = $em->getRepository('BackendBundle:Portada')->findAll();

            $paginator = $this->get("knp_paginator");
            $items_per_page = $size;

            $pagination = $paginator->paginate($portadas, $page, $items_per_page);
            $total_items_count = $pagination->getTotalItemCount();

            $list = true;
            $data = $helpers->responseData(200, $msg = null, $list, $pagination, $total_items_count, $page, $items_per_page);
        } else {
            $action = $request->get('action', "get");
            if ($action == "get") {
                $portada_result = $em->getRepository('BackendBundle:Portada')->getPortada($idportada);
                if (count($portada_result) > 0) {
                    $portada_status = $portada_result[0]->getIdportada()->getStatus();
                    $portada_code = $portada_result[0]->getIdportada()->getCode();
                } else {
                    $portada_result = array();
                    $portada_status = array();
                    $portada_code = array();
                }
            } else {
                $info_user = "";
                $portada = $em->getRepository('BackendBundle:Portada')->find($idportada);
                if (count($portada) > 0) {
                    if ($portada->getStatus() == "editing") {
                        if (is_object($portada->getEditingById())) {
                            $info_user = "Cover is in use by " . $portada->getEditingById()->getUserName();
                        } else {
                            $info_user = "Cover is in use";
                        }
                    }
                    if ($portada->getStatus() == "default") {
                        $code = substr(md5(rand(1000, 9999)), 0, 2) . date('His', time());
                        $this_user = $this->getUser();
                        $user = $em->getRepository('BackendBundle:WfUser')->find($this_user->getId());
                        $portada->setStatus("editing");
                        $portada->setCode($code);
                        $portada->setEditingById($user);
                        $em->persist($portada);
                        $em->flush();
                    }
                    if ($portada->getStatus() == "scheduled") {
                        $portada->setStatus("default");
                        $em->persist($portada);
                        $em->flush();
                    }
                    $portada_id = $portada->getId();
                    $portada_code = $portada->getCode();
                    $portada_status = $portada->getStatus();
                    $portada_result = $em->getRepository('BackendBundle:Portada')->getPortada($portada_id);
                } else {
                    $portada_result = array();
                }
            }

            $portada_row = $this->formatPortada($portada_result);
            if (count($portada_row) != 0) {
                $data = array(
                    "status" => "success",
                    "data" => $portada_row,
                    "idportada" => isset($portada_id) ? $portada_id : $idportada,
                    "codeportada" => isset($portada_code) ? $portada_code : "",
                    "statusportada" => isset($portada_status) ? $portada_status : "",
                    "warning" => isset($info_user) ? $info_user : ""
                );
            } else {
                $msg = "Portada not found in DB";
                $data = $helpers->responseData(404, $msg);
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
     *  section = "Portada",
     *  description="Creación de Portada",
     *  requirements={
     *     {"name"="nombre",        "dataType"="string",    "required"="true",  "description"="* Name Portada"},
     *     {"name"="observaciones", "dataType"="string",    "required"="false", "description"="Observaciones"},
     *     {"name"="image_id",      "dataType"="integer",   "required"="true",  "description"="Id Image"},
     *     {"name"="idseccion",     "dataType"="integer",   "required"="true",  "description"="* Id Category"},
     *     {"name"="folds",         "dataType"="json",      "required"="true",  "description"="* [{id:1,index:1,visible:[0|1]},{id:2,index:2,visible:[0|1]}]"},
     *     {"name"="status",        "dataType"="string",    "required"="false", "description"="[ default:default | editing ]"},
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
        $portada_id = 0;
        $image = array();

        $name = $request->get('nombre');
        $observaciones = $request->get('observaciones');
        $imagen = $request->get('image_id');
        $category = $request->get('idseccion');
        $status = $request->get('status', "default");
        $content = $request->get('folds');
        $user_id = $this->getUser()->getId();

        $portada = new Portada();
        $portada->setName($name);
        $portada->setObservaciones($observaciones);
        $portada->setStatus($status);
        $portada->setCreatedAt(new \DateTime());
        $portada->setUpdatedAt(new \DateTime());

        if ($imagen) {
            $image = $em->getRepository('BackendBundle:Image')->find($imagen);
        }

        $def_img = $em->getRepository('BackendBundle:Image')->findOneBy(                  //select default image
            array(
                "title" => "Foto Default de Portada",
                "description" => "Foto Default de Portada",
                "credito" => "Especial"
            )
        );

        if (count($image) > 0) {
            $portada->setImage($image);
        } else {
            $portada->setImage($def_img);
        }

        if ($category) {
            $category = $em->getRepository('BackendBundle:Category')->find($category);
            $portada->setCategory($category);
        }

        if ($user_id) {
            $user = $em->getRepository('BackendBundle:WfUser')->find($user_id);
            $portada->setCreatedBy($user);
            $portada->setUpdatedBy($user);
        }

        $code = substr(md5(rand(1000, 9999)), 0, 2) . date('His', time());
        $portada->setCode($code);

        $validator = $this->get('validator');
        $errors = $validator->validate($portada);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            $data = $helpers->responseData(400, $messages);
            $response = $helpers->responseHeaders(400, $data);
        } else {
            $em->persist($portada);
            $em->flush();
            $portada_id = $portada->getId();
            $portada_code = $portada->getCode();
            $folds = json_decode($content);
            if (count($folds) > 0) {
                $this->setFolds($helpers, $em, $portada, $folds, $user);
                $portada_result = $em->getRepository('BackendBundle:Portada')->getPortada($portada_id);
                $portada_row = $this->formatPortada($portada_result);
                $data = array(
                    "data" => $portada_row,
                    "idportada" => $portada_id,
                    "codeportada" => $portada_code,
                    "statusportada" => $status
                );
            } else {
                $data = $helpers->responseData(400, "No folds");
                $response = $helpers->responseHeaders(400, $data);
            }

            return $helpers->json($data);
        }

        return $response;
    }

    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  section = "Portada",
     *  description="Edicion de Portada",
     *  requirements={
     *     {"name"="id",                "dataType"="integer",   "required"="true",  "description"="* Id Portada"},
     *     {"name"="nombre",            "dataType"="string",    "required"="true",  "description"="* Name Portada"},
     *     {"name"="observaciones",     "dataType"="string",    "required"="false", "description"="Observaciones"},
     *     {"name"="image_id",          "dataType"="integer",   "required"="false", "description"="Id Image"},
     *     {"name"="idseccion",         "dataType"="integer",   "required"="true",  "description"="* Id Category"},
     *     {"name"="status",            "dataType"="string",    "required"="true",  "description"="* default | scheduled | published"},
     *     {"name"="folds",             "dataType"="json",      "required"="false", "description"="[{'id':2,'index':2,'accion':'n=new|m=modify|d=delete','visible':[0|1]}]"},
     *     {"name"="code",              "dataType"="string",    "required"="true",  "description"="* Code"},
     *     {"name"="next_published_at", "dataType"="string",    "required"="false", "description"="yyyy-mm-dd hh:mm:00 only for status scheduled -> 2017-10-02 11:35:00"},
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

        $name = $request->get('nombre');
        $observaciones = $request->get('observaciones');
        $imagen = $request->get('image_id');
        $category = $request->get('idseccion');
        $status = $request->get('status');
        $code = $request->get('code');
        $content = $request->get('folds');
        $user_id = $this->getUser()->getId();
        $nxt_pblsh = $request->get('next_published_at');

        $portada = $em->getRepository('BackendBundle:Portada')->find($id);

        if ($portada->getStatus() == "editing" && $portada->getCode() == $code) {                                      //validate concurrency
            $code = substr(md5(rand(1000, 9999)), 0, 2) . date('His', time());
            $portada->setName($name);
            $portada->setObservaciones($observaciones);

            $portada->setStatus($status);                                                                               //set state fix
            switch ($status) {
                case "scheduled":
                    $portada->setNextPublishedAt(new \DateTime($nxt_pblsh));
                    break;
                case "published":
                    $portada->setNextPublishedAt(null);
                    $portada->setPublishedAt(new \DateTime());
                    break;
                default:
                    $portada->setNextPublishedAt(null);
                    $portada->setPublishedAt(null);
            }

            $portada->setUpdatedAt(new \DateTime());
            $portada->setCode($code);

            if ($imagen) {
                $image = $em->getRepository('BackendBundle:Image')->find($imagen);
                $portada->setImage($image);
            }

            if ($category) {
                $category = $em->getRepository('BackendBundle:Category')->find($category);
                $portada->setCategory($category);
            }

            if ($user_id) {
                $user = $em->getRepository('BackendBundle:WfUser')->find($user_id);
                $portada->setCreatedBy($user);
            }

            $validator = $this->get('validator');
            $errors = $validator->validate($portada);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }
                $data = $helpers->responseData(400, $messages);
                $response = $helpers->responseHeaders(400, $data);
            } else {                                                                                                        //OK portada, now with folds
                $em = $this->getDoctrine()->getManager();
                $em->persist($portada);

                if ($status == "published") {                                                                              //unpublish old versions
                    $helpers->unpublishOthers($portada->getCategory()->getId(), $portada->getId());

                    $res = $helpers->Purga($portada->getCategory()->getSlug());
                    if ($portada->getCategory()->getSlug() == "cartones") {
                        $res = $helpers->Purga("opinion");
                    }

                    //If portada is 'Home', call Top_News
                    if ($portada->getCategory()->getId() == 1) {
                        //call Top News Helpers
                        $data_topnews = $helpers->topNews($portada->getId());
                        $helpers->logActivity("topsnews@crontab.com", "notas recibidas: " . $data_topnews['conteo'] . "---" . "ID Portada: " . $data_topnews['idPortada']);
                    }
                        //Se elimina la creacion de archivos cache por cada categora de TV
                    }

                $portada_id = $portada->getId();
                $portada_code = $portada->getCode();
                $portada_status = $portada->getStatus();
                $folds = json_decode($content);
                if (count($folds) > 0) {
                    foreach ($folds as $fold) {                                                                              //every fold
                        switch ($fold->accion) {
                            case "d":
                                $emDel = $this->getDoctrine()->getManager();
                                $old_fold = $emDel->getRepository('BackendBundle:PortadaFolds')->findOneBy(      //select old fold
                                    array(
                                        "id" => $fold->id
                                    )
                                );
                                if (count($old_fold)) {
                                    $emDel->remove($old_fold);
                                    $emDel->flush();
                                }
                                break;
                            case "m":
                                $old_fold = $em->getRepository('BackendBundle:PortadaFolds')->findOneBy(                  //select old fold
                                    array(
                                        "id" => $fold->id
                                    )
                                );
                                if (count($old_fold)) {
                                    $old_fold->setOrden($fold->index);
                                    $old_fold->setVisible($fold->visible);
                                    $em->persist($old_fold);
                                }
                                break;
                            case "n":
                                $portada_fold = new PortadaFolds();
                                $code = substr(md5(rand(1000, 9999)), 0, 2) . date('His', time());

                                $portada_fold->setIdportada($portada);
                                $portada_fold->setCreatedAt(new \DateTime());
                                $portada_fold->setUpdatedAt(new \DateTime());
                                if ($fold->id) {
                                    $fold_in = $em->getRepository('BackendBundle:Folds')->find($fold->id);
                                    $portada_fold->setIdfold($fold_in);
                                }
                                $portada_fold->setStatus("default");
                                $portada_fold->setContent(".");
                                $portada_fold->setCode($code);
                                $portada_fold->setOrden($fold->index);
                                $portada_fold->setVisible($fold->visible);
                                $portada_fold->setUpdatedBy($user);
                                $em->persist($portada_fold);
                                break;
                        }
                    }
                    $desc = "";
                } else {
                    $desc = "Portada saved, without folds changes";
                }
                $em->flush();
                $portada_result = $em->getRepository('BackendBundle:Portada')->getPortada($id);
                $data = array(
                    "data" => $portada_result,
                    "idportada" => $portada_id,
                    "codeportada" => $portada_code,
                    "statusportada" => $portada_status,
                    "info" => $desc
                );

                return $helpers->json($data);
            }
        } else {
            if ($portada->getStatus() == "editing") {
                $whoIsEditing = $portada->getEditingById();
                if (count($whoIsEditing) > 0) {
                    $data = $helpers->responseData(400, "Cover in use by " . $whoIsEditing->getUsername() . ". Your changes will be lost.");
                } else {
                    $data = $helpers->responseData(400, "Cover in use. Your changes will be lost.");
                }
            } else {
                $data = $helpers->responseData(400, "Wrong status or code.");
            }
            $response = $helpers->responseHeaders(400, $data);
        }

        return $response;
    }

    private function formatPortada($portada)
    {
        $salida = array();

        foreach ($portada as $fold) {
            array_push($salida,
                array(
                    "id" => $fold->getId(),
                    "idfold" => $fold->getIdfold(),
                    "idportada" => $fold->getIdPortada(),
                    "statusfold" => $fold->getStatus(),
                    "orden" => $fold->getOrden(),
                    "index" => $fold->getOrden(),
                    "content" => $fold->getContent(),
                    "updated_by" => $fold->getUpdatedby(),
                    "code" => $fold->getCode(),
                    "visible" => $fold->getVisible(),
                    "cloneId" => $fold->getCloneId(),
                )
            );
        }

        return $salida;
    }

    /**
     * Descripcion especifica de este metodo
     *
     * @ApiDoc(
     *  section = "Portada",
     *  description="Clonado de Portadas",
     *  requirements={
     *     {"name"="id",          "dataType"="integer", "default"="", "description"="*id portada"},
     *     {"name"="foldscc",     "dataType"="string",  "default"="", "description"="true | false - optional"},
     *     {"name"="foldsstatus", "dataType"="string",  "default"="", "description"="default | published - optional"},
     *   },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function cloningAction(Request $request)
    {
        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();
        $error = "";
        $idportada = $request->get('id');
        $foldscc = $request->get('foldscc', "false");
        $statusFolds = $request->get('foldsstatus', "default");

        if ($statusFolds != "published") {
            $statusFolds = "default";
        }

        $portada = $em->getRepository('BackendBundle:Portada')->find($idportada);
        $folds = $em->getRepository('BackendBundle:PortadaFolds')->findBy(                  //select old fold
            array(
                "idportada" => (integer)$idportada
            )
        );
        if ($portada->getStatus() != "published" && $portada->getStatus() != "scheduled" && $portada->getStatus() != "trash") {
            $error = "Portada con estatus " . $portada->getStatus();
        }

        if ($portada->getStatus() == "scheduled") {
            $portada->setStatus("trash");
            $em->persist($portada);
        }

        if ($error == "") {
            $portada_clone = clone $portada;
            $code = substr(md5(rand(1000, 9999)), 0, 2) . date('His', time());
            $user_id = $this->getUser()->getId();
            $user = $em->getRepository('BackendBundle:WfUser')->find($user_id);

            $portada_clone->setStatus($statusFolds);
            $portada_clone->setCode($code);
            $portada_clone->setCreatedAt(new \DateTime());
            $portada_clone->setUpdatedAt(new \DateTime());
            $portada_clone->setPublishedAt(NULL);
            $portada_clone->setCreatedBy($user);
            $portada_clone->setUpdatedBy($user);
            $em->persist($portada_clone);

            foreach ($folds as $fold) {
                if ($fold->getStatus() != "trash") {
                    $folds_clone = clone $fold;
                    $code = substr(md5(rand(1000, 9999)), 0, 2) . date('His', time());
                    if ($foldscc == "false") {
                        $folds_clone->setStatus($statusFolds);
                    }
                    $folds_clone->setCode($code);
                    $folds_clone->setIdportada($portada_clone);
                    $folds_clone->setCreatedAt(new \DateTime());
                    $folds_clone->setUpdatedAt(new \DateTime());
                    $folds_clone->setPublishedAt(null);
                    $folds_clone->setUpdatedBy($user);
                    $folds_clone->setEditingBy(null);

                    $em->persist($folds_clone);
                }
            }
            $em->flush();

            $id = $portada_clone->getId();
            $portada_code = $portada_clone->getCode();
            $portada_status = $portada_clone->getStatus();
            $portada = $em->getRepository('BackendBundle:Portada')->getPortada($id);
            $data = array(
                "data" => $portada,
                "idportada" => $id,
                "codeportada" => $portada_code,
                "statusportada" => $portada_status
            );

            return $helpers->json($data);
        } else {
            $msg = "Error: " . $error;
            $data = $helpers->responseData($code = 404, $msg);
            $response = $helpers->responseHeaders($code = 404, $data);

            return $response;
        }
    }

    /**
     * @ApiDoc(
     *    section = "Portada",
     *    description="Switch portada status",
     *    requirements={
     *      {"name"="id",       "dataType"="string", "required"=true, "description"="id"},
     *      {"name"="status",   "dataType"="string", "required"=true, "description"="default"},
     *      {"name"="code",     "dataType"="string", "required"=true, "description"="default"},
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function setCoverStatusAction($id, $status = "default", $code = "")
    {
        $em = $this->getDoctrine()->getManager();
        $helpers = $this->get("app.helpers");
        $error = "Type error";
        $portada = $em->getRepository('BackendBundle:Portada')->find($id);

        switch ($status) {
            case "editing":
                if ($portada->getStatus() != "default") {
                    $data = $helpers->responseData($code = 400, $error);
                    $response = $helpers->responseHeaders($code = 400, $data);

                    return $response;
                }
                break;
            case "default":
                if ($portada->getStatus() == "scheduled") {
                    $portada->setNextPublishedAt(NULL);
                }
                break;
        }
        if ($portada->getCode() == $code) {
            $portada->setStatus($status);
            $em->persist($portada);
            $em->flush();
            $data = array(
                "status" => "success"
            );

            return $helpers->json($data);
        } else {
            $error = "Code error";

            return $helpers->json(array("status" => $error));
        }
    }

    /**
     * @desc  Agregar folds en portadafolds
     * @param $helpers
     * @param $em
     * @param $portada
     * @param $folds
     * @param $user
     * @return mixed
     */
    private function setFolds($helpers, $em, $portada, $folds, $user)
    {
        //Remove all cover folds
        $folds_to_del = $em->getRepository('BackendBundle:PortadaFolds')->findBy(
            array(
                'idportada' => $portada->getId()
            )
        );
        foreach ($folds_to_del as $fold) {
            $em->remove($fold);
        }
        $em->flush();
        //Insert cover folds
        foreach ($folds as $fold) {
            $portada_fold = new PortadaFolds();
            $code = substr(md5(rand(1000, 9999)), 0, 2) . date('His', time());

            $portada_fold->setIdportada($portada);
            $portada_fold->setCreatedAt(new \DateTime());
            $portada_fold->setUpdatedAt(new \DateTime());

            if ($fold->id) {
                $fold_in = $em->getRepository('BackendBundle:Folds')->find($fold->id);
                $portada_fold->setIdfold($fold_in);
            }

            $portada_fold->setStatus("default");
            $portada_fold->setContent(".");
            $portada_fold->setOrden($fold->index);
            $portada_fold->setUpdatedBy($user);
            $portada_fold->setCode($code);
            $portada_fold->setVisible($fold->visible);

            $validator = $this->get('validator');
            $errors = $validator->validate($portada_fold);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }
                $data = $helpers->responseData(400, $messages);
                $response = $helpers->responseHeaders(400, $data);

                return $response;
            } else {
                $em = $this->getDoctrine()->getManager();
                $em->persist($portada_fold);
            }
        }

        $em->flush();
    }

    /**
     * @desc  Crear archivo json con slugs de notas de folds publicados
     * @param $slugs_array
     */
    public function createFileMostView($slugs_array)
    {

        /*try {
            file_put_contents(__DIR__ . '/../../ApipublicaBundle/Resources/config/top-news', $slugs_array, FILE_APPEND);
            $data_ga = fopen(__DIR__ . '/../../ApipublicaBundle/Resources/config/top-news/data.json', 'w');
            fwrite($data_ga, json_encode($slugs_array));
            fclose($data_ga);

        }catch ( \Exception $e){

        }*/

    }


    private function _cache($url, $response)
    {
        $hash = md5(serialize($url));


        $rutacachetv = $this->container->getParameter('rutacachetv');

        //$response = json_encode($response);


        if($this->container->getParameter("kernel.environment")=="prod") {

            $file = $this->get('kernel')->getRootDir() . "/../" . $rutacachetv  . '/' . $hash . '.cache';

        }
        elseif ($this->container->getParameter("kernel.environment")=="public")
        {
            $file = $this->get('kernel')->getRootDir() . "/../../" . '/' . $hash . '.cache';
        }

        if (file_exists($file)) {
            unlink($file);
        }

        return file_put_contents($file, $response->getContent());

    }

    private function setMyRequest($type,$subtype,$json,$page,$size,$public,$max_rows){


        $request = new Request();

        $request->request->set('json', $json);
        $request->request->set('type', $type);
        $request->request->set('subtype', $subtype);
        $request->request->set('page', $page);
        $request->request->set('size', $size);
        $request->request->set('public', $public);
        $request->request->set('max_rows', $max_rows);

        return $request;
    }

}
