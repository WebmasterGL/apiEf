<?php

namespace BackendBundle\Controller;

use BackendBundle\Entity\Programacion;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Gedmo\Sluggable\Util\Urlizer;

/**
 * Programacion controller.
 *
 */
class ProgramacionController extends Controller
{
    private function assignDetails($programacion, Request $request)
    {
        $programacion->setName($request->get('name'));
        $programacion->setDiaHora($request->get('diaHora'));
        $programacion->setNameConductor($request->get('nameConductor'));
        $programacion->setColorHexa($request->get('colorHexa'));
        $programacion->setTwitter($request->get('twitter'));
        $programacion->setDescription($request->get('description'));
        $programacion->setSvgChannel($request->get('svgChannel'));
        if ($request->get('active') != null) {
            if ($request->get('active') == 'true') {
                $programacion->setActive(1);
            } else {
                $programacion->setActive(0);
            }
        }

        return $programacion;
    }

    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  section = "Programacion",
     *  description="Listado de Programaciones de Canales de TV",
     *  requirements={
     *      {"name"="page", "dataType"="int", "required"="true", "default"="1", "description"="numero de pagina, si se omite es 1"},
     *      {"name"="size", "dataType"="int", "required"="true", "default"="10", "description"="numero de items, si se omite es 10"}
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

        if ($id == NULL || $id == '{id}') //2o caso para aceptar el parametro que le pone swagger
        {

            $page = $request->get("page", 1);
            $size = $request->get("size", 10);

            $programaciones = $em->getRepository('BackendBundle:Programacion')->findAll();

            $paginator = $this->get("knp_paginator");
            $items_per_page = $size;

            $pagination = $paginator->paginate($programaciones, $page, $items_per_page);
            $total_items_count = $pagination->getTotalItemCount();

            $list = true;
            $data = $helpers->responseData(200, null, $list, $pagination, $total_items_count, $page, $items_per_page);
        } else {
            $programacion = $em->getRepository('BackendBundle:Programacion')->find($id);

            if (count($programacion) != 0) {

                $data = array(
                    "status" => "success",
                    "data" => $programacion
                );

            } else {
                $msg = "Programacion not found";
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
     *  section = "Programacion",
     *  description="Creación de Programación de Canal de TV",
     *  requirements={
     *      {"name"="name", "dataType"="string", "required"="true",  "description"="Nombre del Programa"},
     *      {"name"="diaHora", "dataType"="string", "required"="false",  "description"="Día y Hora de transmisión"},
     *      {"name"="nameConductor", "dataType"="string", "required"=false, "description"="Nombre del Conductor"},
     *      {"name"="colorHexa", "dataType"="string", "required"="false",  "description"="Color Hexadecimal"},
     *      {"name"="twitter", "dataType"="string", "required"="false",  "description"="Cuenta Twitter del conductor"},
     *      {"name"="description", "dataType"="string", "required"="false",  "description"="Descripción del programa"},
     *      {"name"="imageHost", "dataType"="int", "required"="true",  "description"="ID IMAGE HOST"},
     *      {"name"="imageTapiz", "dataType"="int", "required"="true",  "description"="ID IMAGE TAPIZ"},
     *      {"name"="category", "dataType"="int", "required"="true",  "description"="ID CATEGORY"},
     *      {"name"="columna", "dataType"="json", "required"=true, "description"="Columnas JSON. Example: [148,149]"},
     *      {"name"="svgChannel", "dataType"="string", "required"=true, "description"="SVG Channel"},
     *      {"name"="active", "dataType"="boolean", "required"=true, "description"="true | false"}
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

        $imageHost = $request->get('imageHost');
        $imageTapiz = $request->get('imageTapiz');
        $category = $request->get('category');
        $columna = $request->get('columna');

        $programacion = new Programacion();
        $programacion = $this->assignDetails($programacion, $request);

        if ($category) {
            $programacion_tv = $em->getRepository('BackendBundle:Programacion')->findOneBy(array(
                'category' => $category
            ));
            if (!$programacion_tv) {
                $category_db = $em->getRepository('BackendBundle:Category')->find($category);
                if ($category_db) {
                    $programacion->setCategory($category_db);
                }
            } else {
                $msg = "Ya existe una Programacion con esta categoría";
                $data = $helpers->responseData(400, $msg);
                $response = $helpers->responseHeaders(400, $data);

                return $response;
            }

        }

        if ($columna) {
            $array_columnas = json_decode($columna);

            foreach ($array_columnas as $idcolumna) {
                $lacolumna = $em->getRepository('BackendBundle:Columna')->find($idcolumna);
                if ($lacolumna != null) {
                    $programacion->addColumna($lacolumna);
                }
            }
        }
        if ($imageHost != null) {
            $image_host = $em->getRepository('BackendBundle:Image')->find($imageHost);
            $programacion->setImageHost($image_host);
        } else {
            $programacion->setImageHost(null);
        }
        if ($imageTapiz != null) {
            $image_tapiz = $em->getRepository('BackendBundle:Image')->find($imageTapiz);
            $programacion->setImageTapiz($image_tapiz);
        } else {
            $programacion->setImageTapiz(null);
        }

        $validator = $this->get('validator');
        $errors = $validator->validate($programacion);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            $data = $helpers->responseData($code = 400, $messages);
            $response = $helpers->responseHeaders($code = 400, $data);
        } else {

            $em = $this->getDoctrine()->getManager();
            $em->persist($programacion);
            $em->flush();

            $msg = "Programación created";
            $data = $helpers->responseData($code = 200, $msg);
            $data['programcion_id'] = $programacion->getId();

            $response = $helpers->responseHeaders(200, $data);

            //purge programacion tv
            $res = $helpers->Purga("tv/programacion");
        }

        return $response;

    }

    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  section = "Programacion",
     *  description="Actualización de Programación de Canal de TV",
     *  requirements={
     *      {"name"="id", "dataType"="int", "required"="true",  "description"="ID del Programa"},
     *      {"name"="name", "dataType"="string", "required"="true",  "description"="Nombre del Programa"},
     *      {"name"="diaHora", "dataType"="string", "required"="false",  "description"="Día y Hora de transmisión"},
     *      {"name"="nameConductor", "dataType"="string", "required"=false, "description"="Nombre del Conductor"},
     *      {"name"="colorHexa", "dataType"="string", "required"="false",  "description"="Color Hexadecimal"},
     *      {"name"="twitter", "dataType"="string", "required"="false",  "description"="Cuenta Twitter del conductor"},
     *      {"name"="description", "dataType"="string", "required"="false",  "description"="Descripción del programa"},
     *      {"name"="imageHost", "dataType"="int", "required"="true",  "description"="ID IMAGE HOST"},
     *      {"name"="imageTapiz", "dataType"="int", "required"="true",  "description"="ID IMAGE TAPIZ"},
     *      {"name"="category", "dataType"="int", "required"="true",  "description"="ID CATEGORY"},
     *      {"name"="columna", "dataType"="json", "required"=true, "description"="Columnas JSON. Example: [148,149]"},
     *      {"name"="svgChannel", "dataType"="string", "required"=true, "description"="SVG Channel"},
     *      {"name"="active", "dataType"="boolean", "required"=true, "description"="true | false"}
     *   },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function updateAction(Request $request, $id)
    {
        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        $imageHost = $request->get('imageHost');
        $imageTapiz = $request->get('imageTapiz');
        $category = $request->get('category');
        $columna = $request->get('columna');

        //get data current programacion tv
        $programacion = $em->getRepository('BackendBundle:Programacion')->find($id);

        if (count($programacion) != 0) {

            $programacion = $this->assignDetails($programacion, $request);

            if ($category) {
                if ($programacion->getCategory()->getId() == $category) {
                    $flag_existencia = true;
                } else {
                    $flag_existencia = false;
                }

                if ($flag_existencia == true) {
                    $category_db = $em->getRepository('BackendBundle:Category')->find($category);
                    if ($category_db) {
                        $programacion->setCategory($category_db);
                    }
                } else {
                    $programacion_tv = $em->getRepository('BackendBundle:Programacion')->findOneBy(array(
                        'category' => $category
                    ));

                    if ($programacion_tv) {
                        $msg = "Ya existe una Programacion con esta categoría";
                        $data = $helpers->responseData(400, $msg);
                        $response = $helpers->responseHeaders(400, $data);

                        return $response;
                    } else {
                        $category_db = $em->getRepository('BackendBundle:Category')->find($category);
                        if ($category_db) {
                            $programacion->setCategory($category_db);
                        }
                    }

                }
            }

            if ($columna) {

                /*Borrando columnas almacenadas en bd actuales*/
                $columnasActuales = $programacion->getColumna();

                foreach ($columnasActuales as $columnaActual) {
                    $programacion->removeColumna($columnaActual);
                }

                /*Asignando las nuevas*/
                $array_columnas = json_decode($columna);

                foreach ($array_columnas as $idcolumna) {
                    $lacolumna = $em->getRepository('BackendBundle:Columna')->find($idcolumna);
                    if ($lacolumna != null) {
                        $programacion->addColumna($lacolumna);
                    }
                }
            }
            if ($imageHost != null) {
                $image_host = $em->getRepository('BackendBundle:Image')->find($imageHost);
                $programacion->setImageHost($image_host);
            } else {
                $programacion->setImageHost(null);
            }

            if ($imageTapiz != null) {
                $image_tapiz = $em->getRepository('BackendBundle:Image')->find($imageTapiz);
                $programacion->setImageTapiz($image_tapiz);
            } else {
                $programacion->setImageTapiz(null);
            }

            $validator = $this->get('validator');
            $errors = $validator->validate($programacion);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }
                $data = $helpers->responseData(400, $messages);
                $response = $helpers->responseHeaders(400, $data);
            } else {

                $em = $this->getDoctrine()->getManager();
                $em->persist($programacion);
                $em->flush();

                $msg = "Programación updated";
                $data = $helpers->responseData(200, $msg);
                $data['programcion_id'] = $programacion->getId();

                $response = $helpers->responseHeaders(200, $data);

                //purge programacion tv
                $res = $helpers->Purga("tv/programacion");
            }
        } else {
            $msg = "Programacion not found";
            $data = $helpers->responseData(404, $msg);
            $response = $helpers->responseHeaders(404, $data);

            return $response;
        }


        return $response;

    }
}
