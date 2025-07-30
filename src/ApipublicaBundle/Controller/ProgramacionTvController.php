<?php

namespace ApipublicaBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use BackendBundle\Controller\SearchController as BaseSearchController;

class ProgramacionTvController extends BaseSearchController
{

    /**
     * Para acceder a este metodo, no se require autorizacion(token)
     * @ApiDoc(
     *     section = "Programacion TV",
     *     description="Recupera un programa a traves del slug de una Seccion",
     *     requirements={
     *      {"name"="parametro", "dataType"="string", "required"=true, "description"="ID|Slug"},
     *      {"name"="all", "dataType"="boolean", "required"=true, "description"="All Programations. true|false"},
     *      {"name"="size", "dataType"="int", "required"=true, "description"="Size notes of programa_tv", "default"=3}
     *    }
     * )
     */
    public function getProgramationAction(Request $request)
    {
        $parametro = $request->get("parametro");    
        $all = $request->get("all");
        $size = $request->get("size", 3);
       
        $helpers = $this->get("app.helpers");
        $numero_columnas="";

        $em = $this->getDoctrine()->getManager();

        if ($parametro && $all == 'false' && $size || $parametro && $all == null && $size) {

            # Check if your variable is an integer
            if (filter_var($parametro, FILTER_VALIDATE_INT) === false) {
                //Is string
                $category = $em->getRepository('BackendBundle:Category')->findOneBy(array('slug' => $parametro));
                if ($category) {
                    $programacion = $em->getRepository('BackendBundle:Programacion')->findOneBy(array(
                        'category' => $category->getId(),
                        'active' => 1
                    ));
                    $category_id = $category->getId();

                } else {
                    $category_id = null;
                }

            } else {
                //Is integer
                $programacion = $em->getRepository('BackendBundle:Programacion')->findOneBy(array(
                    'category' => $parametro,
                    'active' => 1
                ));
                $category_id = $parametro;
            }

            //get portada seccion
            $portada_seccion = $this->getPortadaSeccion($category_id);
            //validate if category_id is Integer
            $category_db = $this->validateCategoryId($category_id);

            if ($category_db == false) {
                $msg = 'Category don´t exist in DB';
                $data = $helpers->responseData($code = 404, $msg);
                $response = $helpers->responseHeaders($code = 404, $data);
                return $response;
            }

            ($category_db) ? $title = $category_db->getTitle() : null;
            ($category_db) ? $slug = $category_db->getSlug() : null;

            //get notes of programa_tv
            $programa_tv_notes = $this->getNotesByProgramaTv($slug, $size);

            //get last 12 items slugs pages for programas tv
            $slugs_pages_programas = $this->getTwelveNotesByProgramaTv($slug);

            //get notes of columns
            if($programacion != null){
                $columnas_notes = $this->getNotesByColumn($programacion, $numero_columnas);
            }
            
            //get videos destacados tv
            $videos_destacados = $this->getVideosDestacadosTV($category_id);

            //get microdata
            $microdata = $helpers->microData($category_db, 'redisenio_portada', null, null, null, null, null, null, $slugs_pages_programas, $videos_destacados);

            if (count($programacion) > 0) {
                $numero_columnas = count($programacion->getColumna());

                $data_final = $this->getDataOutput($programacion, $programa_tv_notes, $columnas_notes, $portada_seccion);

                $data = array(
                    "status" => "success",
                    "data" => array(
                        'typePortada' => 'programaTV',
                        'category' => array(
                            'name' => $title,
                            'slug' => $slug
                        ),
                        'programas' => $data_final,
                        'secciones_tv' => null,
                        'microdata' => $microdata
                    )
                );

            } else {

                //get notes of programa_tv
                $programa_tv_notes = $this->getNotesByProgramaTv($slug, $size);

                $data_final = $this->getDataOutput(null, $programa_tv_notes, null, $portada_seccion);

                $data = array(
                    "status" => "success",
                    "data" => array(
                        'typePortada' => 'seccionTV',
                        'category' => array(
                            'name' => $title,
                            'slug' => $slug
                        ),
                        'programas' => $data_final,
                        'secciones_tv' => null,
                        'microdata' => $microdata
                    )
                );

            }

        } elseif ($all == 'true' && !$parametro) {
            //regreso al frontal los programas activos
            $programaciones = $em->getRepository('BackendBundle:Programacion')->findBy(array('active' => 1));

            if (!$programaciones) {
                $msg = 'Programacion not found';
                $data = $helpers->responseData($code = 404, $msg);
                $response = $helpers->responseHeaders($code = 404, $data);

                return $response;
            } else {

                $categories_id = array();
                //busqueda general de programas
                $programaciones_all = $em->getRepository('BackendBundle:Programacion')->findAll();
                foreach ($programaciones_all as $prog) {
                    array_push($categories_id, $prog->getCategory()->getId());
                }

                $qb = $em->createQueryBuilder();

                //Obtengo las subsecciones de tv, que no tienen programas asociados
                $categorias_tv = $qb->select('rl')
                    ->from('BackendBundle:Category', 'rl')
                    ->where($qb->expr()->notIn('rl.id', $categories_id))
                    ->andWhere('rl.parentId = :type')
                    ->andWhere('rl.active = :active')
                    ->setParameter('type', 81)
                    ->setParameter('active', 1)
                    ->getQuery()
                    ->getResult();

                $secciones_tv_not_channel = array();
                foreach ($categorias_tv as $item) {
                    array_push($secciones_tv_not_channel, $item);
                }

                $data = array(
                    "status" => "success",
                    "data" => array(
                        //'programas' => $programacion_data,
                        'typePortada' => null,
                        'category' => null,
                        'programas' => $programaciones,
                        'secciones_tv' => $secciones_tv_not_channel,
                        'microdata' => null
                    )
                );
            }


        } else {
            $msg = 'parametros invalidos';
            $data = $helpers->responseData($code = 400, $msg);
            $response = $helpers->responseHeaders($code = 400, $data);

            return $response;
        }

        return $helpers->json($data);
    }


    /**
     * @desc returns the object a list of columns with their last notes
     * @param $entity , $numeroColumnas
     * @return array
     */
    public function getNotesByColumn($entity, $numeroColumnas)
    {
        $columas_array = array();

        switch ($numeroColumnas) {
            //If contain 1 column
            case "1":
                $notes_data = array();
                foreach ($entity->getColumna() as $key => $columna) {
                    //Resultado de la busqueda para obtener la notas
                    $page_filter = $this->broadPublic("page", null, "{\"columnslug\":\"" . $columna->getSlug() . "\"}", 1, 3, true, 10);
                    //Si encontro un resultado
                    if (count($page_filter) > 0) {
                        foreach ($page_filter[1] as $item) {
                            array_push($notes_data, $item["_source"]);
                        }
                    }

                    array_push($columas_array,
                        array(
                            "id" => $columna->getId(),
                            "nombre" => $columna->getNombre(),
                            "slug" => $columna->getSlug(),
                            "nombreSistema" => $columna->getNombreSistema(),
                            "active" => $columna->getActive(),
                            "authors" => $columna->getAuthors(),
                            "seo" => $columna->getSeo(),
                            "social" => $columna->getSocial(),
                            "image" => $columna->getImage(),
                            "createdAt" => $columna->getCreatedAt(),
                            "updatedAt" => $columna->getUpdatedAt(),
                            "activatedAt" => $columna->getActivatedAt(),
                            "notes" => $notes_data
                        ));
                }
                break;

            //If contain 2 columns
            case "2":
                foreach ($entity->getColumna() as $key => $columna) {
                    if ($key == 0) {
                        $notes_data[$key] = array();
                        //Resultado de la busqueda para obtener la notas
                        $page_filter = $this->broadPublic("page", null, "{\"columnslug\":\"" . $columna->getSlug() . "\"}", 1, 1, true, 10);
                        //Si encontro un resultado
                        if (count($page_filter) > 0) {

                            foreach ($page_filter[1] as $item) {
                                array_push($notes_data[$key], $item["_source"]);
                            }

                            array_push($columas_array,
                                array(
                                    "id" => $columna->getId(),
                                    "nombre" => $columna->getNombre(),
                                    "slug" => $columna->getSlug(),
                                    "nombreSistema" => $columna->getNombreSistema(),
                                    "active" => $columna->getActive(),
                                    "authors" => $columna->getAuthors(),
                                    "seo" => $columna->getSeo(),
                                    "social" => $columna->getSocial(),
                                    "image" => $columna->getImage(),
                                    "createdAt" => $columna->getCreatedAt(),
                                    "updatedAt" => $columna->getUpdatedAt(),
                                    "activatedAt" => $columna->getActivatedAt(),
                                    "notes" => $notes_data[$key]
                                ));

                        }
                    } else {
                        $notes_data[$key] = array();
                        //Resultado de la busqueda para obtener la notas
                        $page_filter = $this->broadPublic("page", null, "{\"columnslug\":\"" . $columna->getSlug() . "\"}", 1, 2, true, 10);
                        //Si encontro un resultado
                        if (count($page_filter) > 0) {

                            foreach ($page_filter[1] as $item) {
                                array_push($notes_data[$key], $item["_source"]);
                            }

                            array_push($columas_array,
                                array(
                                    "id" => $columna->getId(),
                                    "nombre" => $columna->getNombre(),
                                    "nombreSistema" => $columna->getNombreSistema(),
                                    "active" => $columna->getActive(),
                                    "authors" => $columna->getAuthors(),
                                    "seo" => $columna->getSeo(),
                                    "social" => $columna->getSocial(),
                                    "image" => $columna->getImage(),
                                    "createdAt" => $columna->getCreatedAt(),
                                    "updatedAt" => $columna->getUpdatedAt(),
                                    "activatedAt" => $columna->getActivatedAt(),
                                    "notes" => $notes_data[$key]
                                ));

                        }
                    }

                }
                break;
            //If contain more 2 columns
            default:
                foreach ($entity->getColumna() as $key => $columna) {

                    $notes_data[$key] = array();
                    //Resultado de la busqueda para obtener la notas
                    $page_filter = $this->broadPublic("page", null, "{\"columnslug\":\"" . $columna->getSlug() . "\"}", 1, 1, true, 10);
                    //Si encontro un resultado
                    if (count($page_filter) > 0) {

                        foreach ($page_filter[1] as $item) {
                            array_push($notes_data[$key], $item["_source"]);
                        }

                        array_push($columas_array,
                            array(
                                "id" => $columna->getId(),
                                "nombre" => $columna->getNombre(),
                                "slug" => $columna->getSlug(),
                                "nombreSistema" => $columna->getNombreSistema(),
                                "active" => $columna->getActive(),
                                "authors" => $columna->getAuthors(),
                                "seo" => $columna->getSeo(),
                                "social" => $columna->getSocial(),
                                "image" => $columna->getImage(),
                                "createdAt" => $columna->getCreatedAt(),
                                "updatedAt" => $columna->getUpdatedAt(),
                                "activatedAt" => $columna->getActivatedAt(),
                                "notes" => $notes_data[$key]
                            ));
                    }

                }
                break;
        }

        return $columas_array;

    }

    /**
     * @desc returns the object a list of programas tv with their last notes
     * @param $subSeccionTvSlug , $size
     * @return array
     */
    public function getNotesByProgramaTv($subSeccionTvSlug, $size = 3)
    {
        //Resultado de la busqueda para obtener la notas tv por id de categoria
        $page_filter = $this->broadPublic("page", "tv", "{\"search\":\"*\",\"categoriesslug\":\"" . $subSeccionTvSlug . "\"}", 1, $size, true, 0);

        if ($page_filter[1] == null) {
            $final_notes_data = null;
        } else {
            $notes_data = array();
            foreach ($page_filter[1] as $item) {
                array_push($notes_data, $item["_source"]);
            }
            $final_notes_data = $notes_data;
        }

        return $final_notes_data;

    }

    /**
     * @desc returns the object a list of programas tv with their last 12 notes
     * @param $subSeccionTvSlug
     * @return array
     */
    public function getTwelveNotesByProgramaTv($subSeccionTvSlug)
    {
        //Resultado de la busqueda para obtener la notas tv por id de categoria
        $page_filter = $this->broadPublic("page", "tv", "{\"search\":\"*\",\"categoriesslug\":\"" . $subSeccionTvSlug . "\"}", 1, 12, true, 0);

        if ($page_filter[1] == null) {
            $final_notes_data = null;
        } else {
            $notes_data = array();
            foreach ($page_filter[1] as $item) {
                array_push($notes_data, $item["_source"]["slug"]);
            }
            $final_notes_data = $notes_data;
        }

        //format slugs in ItemList
        $final_notes_data = $this->getSlugProgramaTv($final_notes_data);

        return $final_notes_data;

    }

    /**
     * @desc returns slugs items for pages of programas tv
     * @param $programa_tv_notes
     * @return array
     */
    public function getSlugProgramaTv($programa_tv_notes)
    {
        // Get paramter host_name
        if ($this->container->hasParameter('host_name') == true) {
            $host = $this->container->getParameter('host_name');
        } else {
            $host = null;
        }

        $list_item = [];        

        foreach ($programa_tv_notes as $key => $value) {
            array_push($list_item, array(
                'url' => $host . "/" . $value,
                '@type' => 'ListItem',
                'position' => $key             
            ));
        }

        return $list_item;
    }

    /**
     * @desc returns slugs items for pages destacadas of tv
     * @return array
     */
    public function getVideosDestacadosTV()
    {
        $id_tv = 81;
        $portada_tv = $this->broadPublic("portada", null, "{\"search\":\"*\",\"id\":\"" . $id_tv . "\"}", 1, 10, true, 0);

        $list_item = [];

        foreach($portada_tv[1] as $key => $data){
            $content = $data["_source"]["content"];
            // convierto a array el string del content
            $mi_content = json_decode($content, true);
            foreach ($mi_content as $llave => $value) {

                if($llave == 'featured'){
                    foreach($value as $llave => $z){
                        array_push($list_item, array(
                            'url' => $z['data']['_source']['slug'],
                            '@type' => 'ListItem',
                            'position' => $llave
                        ));
                    }
                }
            }
            
         }

        return $list_item;
    }

    /**
     * @desc returns the portada seccion object
     * @param $subSeccionTvId
     * @return array
     */
    public function getPortadaSeccion($subSeccionTvId)
    {

        $portada_filter = $this->broadPublic("portada", null, "{\"search\":\"*\",\"id\":\"" . $subSeccionTvId . "\"}", 1, 10, true, 0);

        if ($portada_filter[1] == null) {
            $portada_data = null;
        } else {
            $notes_data = array();
            foreach ($portada_filter[1] as $item) {
                array_push($notes_data, $item["_source"]);
            }
            $portada_data = $notes_data;
        }

        return $portada_data;

    }

    /**
     * @desc returns data object of programacion tv
     * @param $programacion , $programas_notas, $columns_notes, $portada_seccion
     * @return array
     */
    public function getDataOutput($programacion = null, $programas_notes = null, $colums_notes = null, $portada_seccion = null)
    {

        $data_array = array();
        array_push($data_array,
            array(
                "id" => ($programacion != null) ? $programacion->getId() : null,
                "name" => ($programacion != null) ? $programacion->getName() : null,
                "diaHora" => ($programacion != null) ? $programacion->getDiaHora() : null,
                "nameConductor" => ($programacion != null) ? $programacion->getNameConductor() : null,
                "colorHexa" => ($programacion != null) ? $programacion->getColorHexa() : null,
                "twitter" => ($programacion != null) ? $programacion->getTwitter() : null,
                "description" => ($programacion != null) ? $programacion->getDescription() : null,
                "category" => ($programacion != null) ? $programacion->getCategory() : null,
                "imageHost" => ($programacion != null) ? $programacion->getImageHost() : null,
                "imageTapiz" => ($programacion != null) ? $programacion->getImageTapiz() : null,
                "svgChannel" => ($programacion != null) ? $programacion->getSvgCHannel() : null,
                "active" => ($programacion != null) ? $programacion->getActive() : null,
                "columna" => ($colums_notes != null) ? $colums_notes : null,
                "notesProgramas" => ($programas_notes != null) ? $programas_notes : null,
                "portadaSeccion" => ($portada_seccion != null) ? $portada_seccion : null
            ));

        return $data_array;

    }

    /**
     * @desc returns data object of category or false
     * @param $category_id
     * @return array
     */
    public function validateCategoryId($category_id = null)
    {
        $em = $this->getDoctrine()->getManager();

        //validate if category_id is not integer
        if (filter_var($category_id, FILTER_VALIDATE_INT) === false) {
            return false;

        } else {
            //get Category
            $category_db = $em->getRepository('BackendBundle:Category')->find($category_id);
            if (!$category_db) {
                return false;
            }
        }

        return $category_db;

    }


}
