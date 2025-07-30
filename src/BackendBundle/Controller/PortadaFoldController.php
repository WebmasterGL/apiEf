<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use BackendBundle\Entity\PortadaFolds;


class PortadaFoldController extends Controller
{
    /**
     * Metodo para accionar un Fold de una Portada
     * @ApiDoc(
     *  section = "Portada Fold",
     *  description="Metodo para editar|publicar|scheduled un fold de portada",
     *  requirements={
     *      {"name"="idPortada", "dataType"="string", "required"=true, "description"="id Portada"},
     *      {"name"="idFold",    "dataType"="string", "required"=true, "description"="id Fold"},
     *      {"name"="action",    "dataType"="string", "required"=true, "description"="default | publish | schedule"},
     *      {"name"="date",      "dataType"="json",   "required"=true, "description"="datetime, example json: {'2017-08-11 11:16:07'}"},
     *      {"name"="content",   "dataType"="json",   "required"=true, "description"="content"},
     *      {"name"="code",      "dataType"="json",   "required"=true, "description"="code"}
     *  },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function actionPFAction(Request $request, $idPortada, $idFold)
    {
        $helpers = $this->get("app.helpers");
        $em      = $this->getDoctrine()->getManager();

        $date    = $request->get("date", null);
        $content = $request->get("content", null);
        $code    = $request->get("code", null);
        $action  = $request->get("action", null);


        $user_id   = $this->getUser()->getId();
        $user_repo = $em->getRepository('BackendBundle:WfUser');

        $portada_fold_db = $em->getRepository('BackendBundle:PortadaFolds')->findOneBy(array(
            'idportada' => $idPortada,
            'idfold'    => $idFold
        ));

        if ($portada_fold_db != null) {

            //refuse action with incorrect status and code
            if ( $portada_fold_db->getStatus() == "editing" && $portada_fold_db->getCode() != $code ){
                $whoIsEditing = $portada_fold_db->getEditingBy();
                if ( count( $whoIsEditing ) > 0 ){
                    $data = $helpers->responseData(400, "Fold in use by " . $whoIsEditing->getUsername() . ". Your changes will be lost." );
                }else{
                    $data = $helpers->responseData(400, "Fold in use. Your changes will be lost.");
                }

                return $response = $helpers->responseHeaders(400, $data);
            }
            if ( $portada_fold_db->getStatus() != "editing" && $portada_fold_db->getCode() == $code ){
                $data = $helpers->responseData(400, "Wrong status.");

                return $response = $helpers->responseHeaders(400, $data);
            }

            //ok, let's do it
            switch ($action) {
                case "default":
                    //validate json content before operation
                    $json_validate = json_decode( $content );
                    if ( $json_validate === null ){
                        $data = $helpers->responseData(400, "Wrong formation in json content.");

                        return $response = $helpers->responseHeaders(400, $data);
                    }

                    //se guardan cambios y se genera un 'code' nuevo, cambia status, actualiza updated_user
                    $portada_fold_db->setStatus($action);
                    $portada_fold_db->setContent( $content );
                    $portada_fold_db->setUpdatedAt(new \DateTime());
                    $user = $user_repo->find($user_id);
                    $portada_fold_db->setUpdatedBy($user);
                    $code = substr(md5(rand(1000, 9999)), 0, 2) . date('His', time());
                    $portada_fold_db->setCode($code);

                    $em->persist($portada_fold_db);
                    $em->flush();

                    $msg          = 'Save Fold';
                    $data         = $helpers->responseData(200, $msg);
                    $data['code'] = $portada_fold_db->getCode();
                    $response     = $helpers->responseHeaders(200, $data);
                    break;
                case "publish":
                    $portada_fold_db->setPublishedAt(new \DateTime());
                    $portada_fold_db->setNextPublishedAt(null);
                    $portada_fold_db->setStatus('published');

                    $em->persist($portada_fold_db);
                    $em->flush();

                    $msg          = 'Publish Fold';
                    $data         = $helpers->responseData(200, $msg);
                    $data['code'] = $portada_fold_db->getCode();
                    $response     = $helpers->responseHeaders(200, $data);
                    break;
                case "schedule":
                    $portada_fold_db->setPublishedAt(null);
                    $portada_fold_db->setNextPublishedAt(new \DateTime($date));
                    $portada_fold_db->setStatus("scheduled");

                    $em->persist($portada_fold_db);
                    $em->flush();

                    $msg          = 'Schedule Fold';
                    $data         = $helpers->responseData(200, $msg);
                    $data['code'] = $portada_fold_db->getCode();
                    $response     = $helpers->responseHeaders(200, $data);
                    break;
            }

        } else {
            $data     = $helpers->responseData(404, "Portada Fold doesn´t exist in DB");
            $response = $helpers->responseHeaders(404, $data);
        }

        return $response;

    }

    /**
     * Metodo para editar un Fold de una Portada
     * @ApiDoc(
     *  section = "Portada Fold",
     *  description="Metodo para editar un fold de portada",
     *  requirements={
     *      {"name"="idPortada",    "dataType"="string",    "required"=true,    "description"="id Portada"},
     *      {"name"="idFold",       "dataType"="string",    "required"=true,    "description"="id Fold"},
     *      {"name"="actionfold",   "dataType"="string",    "required"="false", "default"="get",    "description"="[ default:get | update ]"}
     *  },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function editAction(Request $request, $idPortada, $idFold)
    {
        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();


        $user_id = $this->getUser()->getId();
        $user_repo = $em->getRepository('BackendBundle:WfUser');

        $portada_fold_db = $em->getRepository('BackendBundle:PortadaFolds')->findOneBy(
            array(
                'idportada' => $idPortada,
                'idfold'    => $idFold
            )
        );

        $action = $request->get('actionfold',"get");
        if ( $action == "get" ){
            $data = array(
                "status" => "success",
                "data"   => $portada_fold_db
            );
        }else{
            //Si el fold esta en 'default', genero nuevo codigo, y el status sera 'editing'
            if ($portada_fold_db->getStatus() == 'default') {
                $portada_fold_db->setStatus("editing");
                $code = substr(md5(rand(1000, 9999)), 0, 2) . date('His', time());
                $portada_fold_db->setCode($code);
                $user = $user_repo->find($user_id);
                $portada_fold_db->setEditingBy($user);

                $em->persist($portada_fold_db);
                $em->flush();

                $data = array(
                    "status"  => "success",
                    "data"    => $portada_fold_db,
                    "warning" => null
                );
            } else {
                $user_editing = $portada_fold_db->getEditingBy();
                $user_name    = $user_editing->getUserName();
                $data         = array(
                    "status"  => "success",
                    "data"    => $portada_fold_db,
                    "warning" => "Fold is in use by " . $user_name
                );
            }
        }

        return $helpers->jsonForceObject($data);
    }

    /**
     * @ApiDoc(
     *    section = "Portada Fold",
     *    description="Switch portada fold status",
     *    requirements={
     *      {"name"="idPortada",    "dataType"="string", "required"=true, "description"="idPortada"},
     *      {"name"="idFold",       "dataType"="string", "required"=true, "description"="idFold"},
     *      {"name"="status",       "dataType"="string", "required"=true, "description"="default"},
     *      {"name"="code",         "dataType"="string", "required"=true, "description"="default"},
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function setFoldStatusAction($idPortada, $idFold, $status = "default", $code = "")
    {
        $em      = $this->getDoctrine()->getManager();
        $helpers = $this->get("app.helpers");
        $error   = "Type error";

        $fold    = $em->getRepository('BackendBundle:PortadaFolds')->findOneBy(array(
            'idportada' => $idPortada,
            'idfold'    => $idFold
        ));

        switch ($status) {
            case "editing":
                if ( $fold->getStatus() != "default" ) {
                    $data     = $helpers->responseData($code = 400, $error);
                    $response = $helpers->responseHeaders($code = 400, $data);

                    return $response;
                }
                break;
        }
        if ( $fold->getCode() == $code ) {
            $fold->setStatus($status);
            $em->persist($fold);
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
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  section = "Portada Fold",
     *  description="Clonado de folds",
     *  requirements={
     *      {"name"="idPortada",    "dataType"="int",    "required"="true", "default"="", "description"="id portada"},
     *      {"name"="idFold",       "dataType"="string", "required"=true,   "description"="idFold"},
     *   },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function cloningAction(Request $request)
    {
        $helpers   = $this->get("app.helpers");
        $em        = $this->getDoctrine()->getManager();
        $error     = "";
        $idPortada = $request->get('idPortada');
        $idFold    = $request->get('idFold');

        $portada = $em->getRepository('BackendBundle:Portada')->find($idPortada);
        $fold    = $em->getRepository('BackendBundle:PortadaFolds')->findOneBy(
            array(
                'idportada' => $idPortada,
                'idfold'    => $idFold
            )
        );
        $user_id = $this->getUser()->getId();
        $user    = $em->getRepository('BackendBundle:WfUser')->find($user_id);
        if ( $fold->getStatus() != "published" && $fold->getStatus() != "scheduled" && $fold->getStatus() != "trash" ){
            $error = "Fold con estatus " . $fold->getStatus();
        }
        if ( count( $portada ) == 0 ){
            $error = "No cover related " . $idPortada;
        }

        if ( $fold->getStatus() == "scheduled" ){
            $fold->setStatus( "trash" );
            $em->persist($fold);
        }

        if ( $error == "" ) {
            $fold_clone   = clone $fold;
            $code         = substr(md5(rand(1000, 9999)), 0, 2) . date('His', time());
            $fold_clone->setStatus( "default" );
            $fold_clone->setCode( $code );
            $fold_clone->setIdportada( $portada );
            $fold_clone->setCreatedAt( new \DateTime() );
            $fold_clone->setUpdatedAt( new \DateTime() );
            $fold_clone->setPublishedAt( null );
            $fold_clone->setUpdatedBy( $user );
            $fold_clone->setEditingBy( null );

            $em->persist($fold_clone);
            $em->flush();

            $data = array(
                "status" => "success",
                "data"   => $fold_clone
            );

            return $helpers->json($data);
        } else {
            $msg      = "Error: " . $error;
            $data     = $helpers->responseData($code = 404, $msg);
            $response = $helpers->responseHeaders($code = 404, $data);

            return $response;
        }
    }
}
