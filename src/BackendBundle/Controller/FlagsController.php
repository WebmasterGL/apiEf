<?php
/**
 * Created by PhpStorm.
 * User: javiermorquecho
 * Date: 07/07/17
 * Time: 16:35
 */

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Entity\Flags;
use BackendBundle\Entity\Page;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class FlagsController extends Controller
{
    /**
     * Descripcion especifica de este metodo
     *
     * @ApiDoc(
     *  section = "Flags",
     *  description="Add flag",
     *     requirements={
     *      {"name"="json",     "dataType"="array",   "required"=true, "description"="name,url"},
     *     {"name"="active",    "dataType"="boolean", "required"=true, "description"="Is active? [1|0]"},
     *    },
     *     headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function addAction(Request $request)
    {
        $helpers = $this->get("app.helpers");
        $json    = $request->get("json", null);
        $active  = $request->get("active", null);

        if ($json !== null) {
            $params  = json_decode( $json );
            $name    = ( isset( $params->name ) ) ? $params->name : null;
            $url     = ( isset( $params->url ) ) ? $params->url : null;

            $flags = new Flags();
            $em    = $this->getDoctrine()->getManager();
            $flags->setName( $name );
            $flags->setImageUrl( $url );
            $flags->setActive( $active );

            $validator = $this->get('validator');
            $errors = $validator->validate($flags);
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            if(count($errors)>0){
                $data = $helpers->responseData($code = 400, $messages);
                $response = $helpers->responseHeaders($code = 400, $data);
            }else{
                $em->persist( $flags );
                $em->flush();
                $data = $helpers->responseData($code = 200, "success");
                $response = $helpers->responseHeaders($code = 200, $data);
            }
        }else{
            $msg = 'Flag not created, without data';
            $data = $helpers->responseData($code = 400, $msg);
            $response = $helpers->responseHeaders($code = 400, $data);
        }

        return $response;
    }

    /**
     * Descripcion especifica de este metodo
     *
     * @ApiDoc(
     *     section = "Flags",
     *     description="Edit a flag, private method",
     *     requirements={
     *      {"name"="id",       "dataType"="string",  "required"=true, "description"="id flag"},
     *      {"name"="json",     "dataType"="array",   "required"=true, "description"="name,url,id_page"},
     *      {"name"="active",   "dataType"="boolean", "required"=true, "description"="Is active? [1|0]"},
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
        $json    = $request->get("json", null);
        $active  = $request->get("active", null);

        if ($json !== null) {
            $params  = json_decode($json);
            $name    = ( isset( $params->name ) ) ? $params->name : null;
            $url     = ( isset( $params->url ) ) ? $params->url : null;

            $em   = $this->getDoctrine()->getManager();
            $flag = $em->getRepository("BackendBundle:Flags")->findOneBy(array(
                "idflags" => $id
            ));

            if ( count( $flag ) > 0 ) {
                $flag->setName( $name );
                $flag->setImageUrl( $url );
                $flag->setActive( $active );
                $validator = $this->get('validator');
                $errors    = $validator->validate($flag);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }
                if(count($errors)>0){
                    $data = $helpers->responseData($code = 400, $messages);
                    $response = $helpers->responseHeaders($code = 400, $data);
                }else{
                    $em->persist( $flag );
                    $em->flush();
                    $data = $helpers->responseData($code = 200, "success");
                    $response = $helpers->responseHeaders($code = 200, $data);
                }
            } else {
                $msg = 'Flag not updated, flag doesn\'t exist';
                $data = $helpers->responseData($code = 404, $msg);
                $response = $helpers->responseHeaders($code = 404, $data);
            }
        } else {
            $msg = 'Flag not updated, params failed';
            $data = $helpers->responseData($code = 400, $msg);
            $response = $helpers->responseHeaders($code = 400, $data);
        }

        return $response;
    }

    /**
     * @ApiDoc(
     *  section = "Flags",
     *  description="Flags list or just one based in id",
     *  requirements={
     *    {"name"="id", "dataType"="integer", "required"=false, "description"="id flag"},
     *    {"name"="page", "dataType"="string", "required"=false, "default"="1", "description"="page number"},
     *    {"name"="itemsInPage", "dataType"="integer", "required"="false", "default"="10", "description"="items by page"}
     *
     *  },
     *   headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     * )
     */
    public function flagsAction( Request $request, $id=NULL )
    {
        $helpers = $this->get("app.helpers");
        $em      = $this->getDoctrine()->getManager();

        if ( $id == NULL || $id=='{id}' ){
            $page              = $request->query->get('page', 1);
            $size              = $request->query->get('itemsInPage', 10);
            $flags             = $em->getRepository('BackendBundle:Flags')->findAll();
            $paginator         = $this->get("knp_paginator");
            $page              = $request->query->get('page',1);
            $pagination        = $paginator->paginate( $flags, $page, $size );
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
            $data = $em->getRepository('BackendBundle:Flags')->find( $id );
        }

        return $helpers->json($data);
    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     *
     * @ApiDoc(
     *     section = "Flags",
     *     description="Delete a flag, private method",
     *     requirements={
     *      {"name"="id", "dataType"="string", "required"=true, "description"="id flag"},
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     *
     */
    public function deleteAction( Request $request, $id )
    {
        $helpers = $this->get("app.helpers", null);
        $em      = $this->getDoctrine()->getManager();
        $flags   = $em->getRepository('BackendBundle:Flags')->find( $id );

        if ( $flags !== null ) {
            $em->remove( $flags );
            $em->flush();

            $msg = 'Flag deleted';
            $data = $helpers->responseData($code = 200, $msg);
            $response = $helpers->responseHeaders($code = 200, $data);
        } else {
            $msg = 'Error. Flag not found in DB';
            $data = $helpers->responseData($code = 404, $msg);
            $response = $helpers->responseHeaders($code = 404, $data);
        }

        return $response;
    }
}
