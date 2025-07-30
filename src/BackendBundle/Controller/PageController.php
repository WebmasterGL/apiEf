<?php

namespace BackendBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use BackendBundle\Entity\Page;
use BackendBundle\Entity\Tag;
use BackendBundle\Entity\Image;
use BackendBundle\Entity\PageVersion;

use BackendBundle\Repository\PageVersionRepository;


use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Elastica\Query\QueryString;
use Elastica\Query;
use \Elastica\Query\Terms;
use Elastica\Query\BoolQuery;

use Gedmo\Sluggable\Util\Urlizer;
//use Symfony\Bundle\FrameworkBundle\Console\Application;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use BackendBundle\Controller\SearchController as BaseSearchController;


class PageController extends BaseSearchController
{


    /**
     * Para acceder a este metodo, no se require autorizacion(token)
     * @ApiDoc(
     *     section = "Page",
     *     description="Comando Symfony",
     *     requirements={
     *      {"name"="command", "dataType"="string", "required"=true, "description"="Comando para ejecutar despues del console"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function cmdSymfonyAction($command)
    {

        $text = 'Starting ...';

        ini_set('max_execution_time', 600);


        $command = new \ApipublicaBundle\Command\CreateGoogleASectionFileCommand();
        $command->setContainer($this->container);

        $arguments = array(
            'command' => $command
        );


        $input = new ArgvInput($arguments);
        $output = new ConsoleOutput();


        $returnCode = $command->run($input, $output);


        if ($returnCode == 0) {
            $text .= 'successfully loaded!';
        } else {
            $text .= 'error!: ' . $returnCode;
        }


        /*$kernel = $this->get('kernel');
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput(array(
            'command' => $command,
            '--filename' => "test",
            '--extension' => "txt"
        ));
        // Use the NullOutput class instead of BufferedOutput.
        $output = new NullOutput();
        $error = $application->run($input, $output);*/


        $helpers = $this->get("app.helpers");


        $data = array(
            'code' => 200,
            'output' => $text
        );

        $response = $helpers->responseHeaders($code = 200, $data);


        return $response;

    }

    /**
     * Para acceder a este metodo, no se require autorizacion(token)
     * @ApiDoc(
     *     section = "Page",
     *     description="Probando Purga",
     *     requirements={
     *      {"name"="s1", "dataType"="string", "required"=false, "description"="s1"},
     *     {"name"="s2", "dataType"="string", "required"=false, "default"="", "description"="s2"},
     *     {"name"="s3", "dataType"="string", "required"=false, "default"="", "description"="s3"},
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function testPurgaAction($s1, $s2, $s3)
    {
        $helpers = $this->get("app.helpers");

        if ($s1 == "{s1}") {
            $s1 = "";
        }
        if ($s2 == "{s2}") {
            $s2 = "";
        }
        if ($s3 == "{s3}") {
            $s3 = "";
        }


        $myslug = "";

        if (strlen($s3) > 0) {
            $res = $helpers->Purga($s1 . "/" . $s2 . "/" . $s3);
            $myslug = $s1 . "/" . $s2 . "/" . $s3;
        } elseif (strlen($s2) > 0) {
            $res = $helpers->Purga($s1 . "/" . $s2);
            $myslug = $s1 . "/" . $s2;
        } elseif (strlen($s1) > 0) {
            $res = $helpers->Purga($s1);
            $myslug = $s1;
        } else {
            $res = $helpers->Purga("");

        }

        $data = array(
            'code' => 200,
            'status' => $res,
            'slug' => $myslug
        );

        $response = $helpers->responseHeaders($code = 200, $data);


        return $response;

    }

    /**
     * Para acceder a este metodo, no se require autorizacion(token)
     * @ApiDoc(
     *     section = "Page",
     *     description="Purga para Api Publica",
     *     requirements={
     *      {"name"="s1", "dataType"="string", "required"=false, "description"="s1"},
     *     {"name"="s2", "dataType"="string", "required"=false, "default"="", "description"="s2"},
     *     {"name"="s3", "dataType"="string", "required"=false, "default"="", "description"="s3"},
     *     {"name"="s4", "dataType"="string", "required"=false, "default"="", "description"="s4"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    /* public function purgaPublicaAction($s1, $s2, $s3, $s4)
     {
         $helpers = $this->get("app.helpers");

         if ($s1 == "{s1}") {
             $s1 = "";
         }
         if ($s2 == "{s2}") {
             $s2 = "";
         }
         if ($s3 == "{s3}") {
             $s3 = "";
         }
         if ($s4 == "{s4}") {
             $s4 = "";
         }

         $myslug = "";

         if (strlen($s4) > 0) {
             $res = $helpers->purgaPublica($s1 . "/" . $s2 . "/" . $s3 . "/" . $s4);

             $myslug = $s1 . "/" . $s2 . "/" . $s3 . "/" . $s4;
         } elseif (strlen($s3) > 0) {

             $res = $helpers->purgaPublica($s1 . "/" . $s2 . "/" . $s3);
             $myslug = $s1 . "/" . $s2 . "/" . $s3;

         } elseif (strlen($s2) > 0) {
             $res = $helpers->purgaPublica($s1 . "/" . $s2);
             $myslug = $s1;
         } elseif (strlen($s1) > 0) {
             $res = $helpers->purgaPublica($s1);
             $myslug = $s1;
         } else {
             $res = $helpers->purgaPublica("");
         }

         $data = array(
             'code' => 200,
             'status' => $res,
             'slug' => $myslug
         );

         $response = $helpers->responseHeaders($code = 200, $data);


         return $response;

     }*/

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "Page",
     *     description="Valida slug, metodo privado",
     *     requirements={
     *      {"name"="slug", "dataType"="string", "required"=true, "description"="slug, Example: economia/titulo-de-notas"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function slugValidateAction(Request $request)
    {
        $helpers = $this->get("app.helpers");
        $slug = $request->get('slug');

        if ($slug !== null) {

            $em = $this->getDoctrine()->getManager();
            $page = $em->getRepository('BackendBundle:Page')->findOneBy(array(
                'slug' => $slug,
                //'status' => 'published'
            ));

            if ($page !== null) {
                $data = array(
                    'code' => 200,
                    'status' => 'Success',
                    'valid' => true
                );
                $response = $helpers->responseHeaders($code = 200, $data);
            } else {
                $data = array(
                    'code' => 200,
                    'status' => 'Success',
                    'valid' => false
                );
                $response = $helpers->responseHeaders($code = 200, $data);
            }
        } else {
            $msg = 'Missing slug';
            $data = $helpers->responseData($code = 400, $msg);
            $response = $helpers->responseHeaders($code = 400, $data);
        }

        return $response;
    }

    private function slugValidate($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('BackendBundle:Page')->findOneBySlug($slug);
        return (($page == NULL) ? FALSE : TRUE);
    }

    /**
     * @ApiDoc(
     *     section = "Page",
     *     description="Regresa un slug, metodo privado",
     *     requirements={
     *      {"name"="json", "dataType"="array", "required"=true, "description"="category_id, title"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     *
     */
    public function slugAction(Request $request)
    {
        $helpers = $this->get("app.helpers");
        $json = $request->get("json", null);

        $users = $this->get('app.users');
        $em = $this->getDoctrine()->getManager();

        $params = json_decode($json);
        $category = (isset($params->category_id)) ? $params->category_id : null;

        $data = $users->getCategoriesUserLogged($category); //Servicio que regresa las categorias de un usuario

        if ($data["isValidCategory"]) // ¿Es valida la categoría que quiero usar?
        {
            if ($json != null) {
                $params = json_decode($json);
                $category = (isset($params->category_id)) ? $params->category_id : null;
                $title = (isset($params->title)) ? $params->title : null;

                if ($category != null && $title != null) {
                    $slug = $this->createSlug(array(
                        'category' => $category,
                        'title' => $title
                    ));

                    $data = array(
                        "status" => "success",
                        "code" => 200,
                        "data" => $slug
                    );
                } else {
                    $data = array(
                        "status" => "error",
                        "code" => 400,
                        "data" => "Error, One value is empty"
                    );
                }
            } else {
                $data = array(
                    "status" => "error",
                    "code" => 400,
                    "data" => "params failed"
                );
            }
        } else {

            $data = array(
                "status" => "error",
                "code" => 401,
                "data" => "Authorization failed by filter"
            );

        }

        return $helpers->json($data);
    }

    private function createSlug($array_slug)
    {
        $em = $this->getDoctrine()->getManager();
        if ($array_slug['category'] != NULL && $array_slug['title'] != NULL) {
            $category = $em->getRepository('BackendBundle:Category')->find($array_slug['category']);

            if ($category != NULL) {

                $slug = $category->getSlug() . '/' . Urlizer::urlize($array_slug['title']);
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
        }
        return array(
            'slug' => FALSE,
            'value' => ''
        );

    }

    private function createSlugMultiple($array_slug)
    {
        if ($array_slug['category'] != NULL && $array_slug['subCategory'] != NULL && $array_slug['title'] != NULL) {


            $slug = $array_slug['category'] . '/' . $array_slug['subCategory'] . '/' . Urlizer::urlize($array_slug['title']);
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
     *  section = "Page",
     *  description="Listado de Pages",
     *  requirements={
     *      {"name"="page", "dataType"="int", "required"="true", "default"="1", "description"="numero de pagina, si se omite es 1"},
     *      {"name"="size", "dataType"="int", "required"="true", "default"="10", "description"="numero de items, si se omite es 10"},
     *      {"name"="tipo", "dataType"="string", "required"="true", "description"="tipo: get or update"},
     *      {"name"="version", "dataType"="string", "required"="true", "description"="Number Version"},
     *   },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function getPagesAction(Request $request, $idpage)
    {

        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        $action = $request->get("tipo", null);
        $no_version = $request->get("version", null);

        if ($idpage == NULL || $idpage == '{idpage}') //2o caso para aceptar el parametro que le pone swagger
        {
            $page = $request->get("page", 1);
            $size = $request->get("size", 10);

            //Obtengo todos los Pages
            $pages = $em->getRepository("BackendBundle:Page")->findAll();

            $page_output = array();

            //Obtengo ciertos campos del objeto de Page
            foreach ($pages as $v) {
                $page_output[$v->getId()] = array(
                    'id' => $v->getId(),
                    'title' => $v->getTitle(),
                    'createdAt' => $v->getCreatedAt(),
                    'code' => $v->getCode(),
                    'from' => 'page'
                );

                $pages_versions = $em->getRepository('BackendBundle:PageVersion')->findBy(array(
                    'page' => $v->getId()
                ));

                if ($pages_versions != null) {
                    //Creo un nuevo indice, para meter los pages_versions, si existen
                    foreach ($pages_versions as $x) {
                        $page_output[$v->getId()]["pages_versions"][$x->getId()] = array(
                            'page_id' => $x->getPage()->getId(),
                            'title' => $x->getTitle(),
                            'createdAt' => $x->getCreatedAt(),
                            'from' => 'page_version'
                        );
                    }
                }
            }

            $paginator = $this->get("knp_paginator");
            $items_per_page = $size;

            $pagination = $paginator->paginate($page_output, $page, $items_per_page);
            $total_items_count = $pagination->getTotalItemCount();

            $list = true;
            $data = $helpers->responseData($code = 200, $msg = null, $list, $pagination, $total_items_count, $page, $items_per_page);

        } else {

            $page = $em->getRepository('BackendBundle:Page')->find($idpage);
            $page_version = $em->getRepository('BackendBundle:PageVersion')->findOneBy(array(
                'page' => $idpage
            ));

            //Si manda el "no_version", obtendra el page_version con dicha version
            if ($no_version != null) {
                $page_version_db = $em->getRepository('BackendBundle:PageVersion')->findOneBy(array(
                    'page' => $idpage
                ));

                $page_version_db_complete = $em->getRepository('BackendBundle:PageVersion')->findOneBy(array(
                    'page' => $idpage,
                    'versionNo' => $no_version
                ));

                if ($page_version_db != null) {
                    if ($page_version_db_complete != null) {
                        $page_version = $em->getRepository('BackendBundle:PageVersion')->getPageVersion($idpage, $no_version);

                        if ($page_version != null) {
                            $data = array(
                                "status" => "success",
                                "data" => $helpers->jsonObjeto($page_version, $flag = "page_version"),
                                "from" => "page_version",
                                "code_db_page" => $page_version->getCode()
                            );
                            return $helpers->json($data);
                        }
                    } else {
                        $msg = "PageVersion with this number version not found in DB";
                        $data = $helpers->responseData($code = 404, $msg);
                        $response = $helpers->responseHeaders($code = 404, $data);

                        return $response;
                    }

                } else {
                    $msg = "PageVersion Id not found in DB";
                    $data = $helpers->responseData($code = 404, $msg);
                    $response = $helpers->responseHeaders($code = 404, $data);

                    return $response;
                }
            }

            if (count($page) != 0) {

                //Obtengo las categorias que tiene asociada 'page'
                $array_categories = array();
                foreach ($page->getCategory() as $v) {
                    $array_categories[] = $v->getId();
                }
                //Agrego tambien el 'id' de la categoria principal
                $array_categories[] = $page->getCategoryId()->getId();

                $users = $this->get('app.users');
                $categories = $users->getCategoriesUserLogged();                         //get current user's cats

                //Obtengo las categorias asociadas al user logedo
                $cats_ids = array();
                foreach ($categories["categories"] as $l_cat)                               //get cat's ids
                {
                    array_push($cats_ids, $l_cat->getId());
                }

                //Si la nota esta publicada
                if ($page->getStatus() == 'published') {
                    if (array_intersect($array_categories, $cats_ids) || $categories["fullAccess"]) {
                        if ($page_version !== null) {

                            //Obtengo el ultimo numero de version de la nota requerida
                            $latestPageVersion = $em->getRepository("BackendBundle:PageVersion")->latestVersion($idpage);

                            //Obtengo la ultima version de la Nota requeida
                            $page_version = $em->getRepository("BackendBundle:PageVersion")->findOneBy(array(
                                'page' => $idpage,
                                'versionNo' => $latestPageVersion
                            ));

                            //si el type es 'update', el status sera 'editing'
                            if ($action == 'update') {
                                //Si quiere editar la nota y esta publicada y aparte es la version No 1. Mando error
                                if ($page_version->getVersionNo() === 1) {
                                    $msg = "Para editar esta nota, debes clonarla";
                                    $data = $helpers->responseData($code = 400, $msg);
                                    $response = $helpers->responseHeaders($code = 400, $data);

                                    return $response;
                                }
                                /*$page_version->setStatus("editing");
                                $em->persist($page_version);
                                $em->flush();*/

                                //Regreso la nota
                                $data = array(
                                    "status" => "success",
                                    "data" => $helpers->jsonObjeto($page_version, $flag = "page_version"),
                                    "from" => "page_version",
                                    "code_db_page" => $page_version->getCode()
                                );
                                //Si la nota esta siendo editada, mando msg de warning
                                if ($page_version->getStatus() == 'editing') {
                                    $username_editor_page = $page->getEditingBy()->getUsername();

                                    $data['warning'] = 'Esta nota esta siendo editada por: ' . $username_editor_page;
                                } else {
                                    $data['warning'] = 'null';
                                }
                                //Si el type es 'Get'
                            } else {
                                //Regreso la nota
                                $data = array(
                                    "status" => "success",
                                    "data" => $helpers->jsonObjeto($page_version, $flag = "page_version"),
                                    "from" => "page_version",
                                    "code_db_page" => $page_version->getCode()
                                );
                                //Si la nota esta siendo editada, mando msg de warning
                                if ($page_version->getStatus() == 'editing') {
                                    $username_editor_page = $page->getEditingBy()->getUsername();

                                    $data['warning'] = 'Esta nota esta siendo editada por: ' . $username_editor_page;
                                } else {
                                    $data['warning'] = 'null';

                                    /*$page_version->setStatus("editing");
                                    $em->persist($page_version);
                                    $em->flush();*/
                                }
                            }

                            // Si la nota esta publicada, pero no existe una 'page_version'...
                        } else {

                            $data = array(
                                "status" => "success",
                                "data" => $helpers->jsonObjeto($page, $flag = "page"),
                                "from" => "page",
                                "code_db_page" => $page->getCode()
                            );
                        }
                    } else {
                        $data = $helpers->responseData($code = 400, "No tienes permiso para esta Seccion");
                        $response = $helpers->responseHeaders($code = 400, $data);

                        return $response;
                    }

                    // Sino esta publicada la nota o si esta en trash...
                } else {
                    //Si existen al menos un registro del array de las secciones del usuario dentro del array de categorias del page
                    if (array_intersect($array_categories, $cats_ids) || $categories["fullAccess"]) {
                        if ($page_version !== null) {
                            //Obtengo el ultimo numero de version de la nota requerida
                            $latestPageVersion = $em->getRepository("BackendBundle:PageVersion")->latestVersion($idpage);
                            //Obtengo la ultima version de la Nota requeida
                            $page_version = $em->getRepository("BackendBundle:PageVersion")->findOneBy(array(
                                'page' => $idpage,
                                'versionNo' => $latestPageVersion
                            ));

                            //Si el status es "trash", y ademas solo existe la copia carbon
                            if ($page->getStatus() == 'trash' && $latestPageVersion == 1) {
                                $data = array(
                                    "status" => "success",
                                    "data" => $helpers->jsonObjeto($page, $flag = "page"),
                                    "from" => "page",
                                    "code_db_page" => $page->getCode()
                                );
                                //Si la nota original es trash y su ultima version es 'scheduled'
                            } elseif ($page->getStatus() == 'trash' && $page_version->getStatus() == 'scheduled') {
                                $data = array(
                                    "status" => "success",
                                    "data" => $helpers->jsonObjeto($page, $flag = "page"),
                                    "from" => "page",
                                    "code_db_page" => $page->getCode()
                                );
                                //si la nota es trash y su ultima version esta siendo editada
                            } elseif ($page->getStatus() == 'trash' && $page_version->getStatus() == 'editing') {
                                $data = array(
                                    "status" => "success",
                                    "data" => $helpers->jsonObjeto($page_version, $flag = "page_version"),
                                    "from" => "page_version",
                                    "code_db_page" => $page_version->getCode()
                                );

                            } else {
                                $data = array(
                                    "status" => "success",
                                    "data" => $helpers->jsonObjeto($page_version, $flag = "page_version"),
                                    "from" => "page_version",
                                    "code_db_page" => $page_version->getCode()
                                );
                            }
                        } else {
                            $data = array(
                                "status" => "success",
                                "data" => $helpers->jsonObjeto($page, $flag = "page"),
                                "from" => "page",
                                "code_db_page" => $page->getCode()
                            );
                        }

                        if ($action == 'update') {
                            if ($page->getStatus() == 'editing') {
                                $username_editor_page = $page->getEditingBy()->getUsername();
                                $page->setStatus("editing");
                                $user_current_id = $this->getUser()->getId();
                                $user = $em->getRepository("BackendBundle:WfUser")->find($user_current_id);
                                $page->setEditingBy($user);
                                $em->persist($page);
                                $em->flush();

                                $data['warning'] = 'Esta nota esta siendo editada por: ' . $username_editor_page;
                            } else {
                                $data['warning'] = 'null';
                            }

                        }
                    } else {
                        $data = $helpers->responseData($code = 401, "No tienes permiso para esta Seccion");
                        $response = $helpers->responseHeaders($code = 401, $data);

                        return $response;
                    }
                }

            } else {
                $msg = "Page not found in DB";
                $data = $helpers->responseData($code = 404, $msg);
                $response = $helpers->responseHeaders($code = 404, $data);

                return $response;
            }
        }

        return $helpers->json($data);

    }


    /**
     *  Para acceder a este metodo, se require autorizacion(token). Los campos requeridos obligatoriamente son 'title' y 'categories'
     *
     * @ApiDoc(
     *     section = "Page",
     *     description="Crea un nueva Nota, metodo privado",
     *     requirements={
     *      {"name"="type", "dataType"="string", "required"=true, "description"="article | column | blogpost | carton | sponsor", "default" = "article"},
     *      {"name"="category", "dataType"="integer", "required"=true, "description"="Category Main Id, is required"},
     *      {"name"="title", "dataType"="string", "required"=true, "description"="Title, is required"},
     *      {"name"="authors", "dataType"="json", "required"=true, "description"="Authors JSON. Example: [148,149]"},
     *      {"name"="tags", "dataType"="json", "required"=true, "description"="Tags JSON. EXample: [1,2]"},
     *      {"name"="flag", "dataType"="integer", "required"=true, "description"="Flag ID"},
     *      {"name"="html", "dataType"="string", "required"=true, "description"="HTML"},
     *      {"name"="htmlSerialized", "dataType"="json", "required"=true, "description"="HTML Serialized JSON"},
     *      {"name"="subCategories", "dataType"="json", "required"=true, "description"="Sub Categories ID. Example: [1,2,3]"},
     *      {"name"="related", "dataType"="json", "required"=true, "description"="Related Pages JSON"},
     *      {"name"="bullets", "dataType"="json", "required"=true, "description"="Bullets JSON. Example: ['mi bullet 1', 'mi bullet 2']"},
     *      {"name"="place", "dataType"="text", "required"=true, "description"="Place"},
     *      {"name"="mostViewed", "dataType"="boolean", "required"=true, "description"="mostViewed. true|false" },
     *      {"name"="newsletter", "dataType"="boolean", "required"=true, "description"="Newslatter. true|false"},
     *      {"name"="rss", "dataType"="json", "required"=true, "description"="RSS"},
     *      {"name"="columna", "dataType"="json", "required"=true, "description"="Columna Id. Only for arcticle page"},
     *      {"name"="mainElementHTML", "dataType"="string", "required"=true, "description"="Main Element HTML"},
     *      {"name"="mainElementSerialized", "dataType"="string", "required"=true, "description"="Main Element Serialized"},
     *      {"name"="blog", "dataType"="string", "required"=true, "description"="Blog Id. Only for arcticle page"},
     *      {"name"="isBreaking", "dataType"="boolean", "required"=true, "description"="Is Breaking. true|false"},
     *      {"name"="cartonImages", "dataType"="json", "required"=true, "description"="Carton Imgs Id. Only for carton page. Example: [196743,196744]"},
     *      {"name"="seo", "dataType"="json", "required"=true, "description"="SEO JSON"},
     *      {"name"="social", "dataType"="json", "required"=true, "description"="Social JSON"},
     *      {"name"="mainImage", "dataType"="json", "required"=true, "description"="Main Image Id"},
     *      {"name"="status", "dataType"="string", "required"=true, "description"="Status"},
     *      {"name"="slug", "dataType"="string", "required"=true, "description"="Slug. If user custom slug"},
     *      {"name"="authorsModified", "dataType"="json", "required"=true, "description"="Authors Modified"},
     *      {"name"="slugRedirect", "dataType"="string", "required"=true, "description"="Slug Redirect"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     *
     */
    public function newPageAction(Request $request)
    {
        $helpers = $this->get("app.helpers");
        $page_clone = $this->get("app.pages_clone");

        $user_id = $this->getUser()->getId();
        $page_type = $request->get('type');
        $title = $request->get('title');
        $authors = $request->get('authors');
        $category = $request->get('category');
        $tags = $request->get('tags');
        $flag = $request->get('flag');
        $newslatter = $request->get('newsletter');
        $html = $request->get('html');
        $htmlSerialized = $request->get('htmlSerialized');
        $subCategories = $request->get('subCategories');
        $related = $request->get('related');
        $bullets = $request->get('bullets');
        $place = $request->get('place');
        $mostViewed = $request->get('mostViewed');
        $rss = $request->get('rss');
        $columna_id = $request->get('columna');
        $blog_id = $request->get('blog');
        $elementHtml = $request->get('mainElementHTML');
        $mainElementSerialized = $request->get('mainElementSerialized');
        $isBreaking = $request->get('isBreaking');
        $cartonImages = $request->get('cartonImages');
        $seo = $request->get('seo');
        $social = $request->get('social');
        $mainImage = $request->get('mainImage');
        $status = $request->get('status');
        $slug = $request->get('slug');
        $authorsModified = $request->get('authorsModified');
        $slugRedirect = $request->get('slugRedirect');

        $em = $this->getDoctrine()->getManager();

        $page = new Page();

        if ($page_type == null) {
            $data = $helpers->responseData($code = 400, "Missing page type. Add page type");
            $response = $helpers->responseHeaders($code = 400, $data);
            return $response;
        }

        //Validacion de 'page_type'
        $pages_type_array = array('article', 'column', 'blogpost', 'carton', 'sponsor');

        if (!in_array($page_type, $pages_type_array)) {
            $data = $helpers->responseData($code = 400, "Wrong Page Type.");
            $response = $helpers->responseHeaders($code = 400, $data);
            return $response;
        }

        $category_db = $em->getRepository('BackendBundle:Category')->find($category);
        $category_slug = $category_db->getSlug();

        switch ($page_type) {
            case "article":
                $find_key = '/';
                $pos = strpos($category_slug, $find_key);
                if ($pos !== false) {
                    $first_level = strstr($category_slug, '/', true);
                    if ($first_level == 'pages' || $first_level == 'tv') {
                        $page->setTemplate($first_level);
                        $page->setPageType($first_level);
                    } else {
                        $page->setTemplate($page_type);
                        $page->setPageType($page_type);
                    }
                } else {
                    $page->setTemplate($page_type);
                    $page->setPageType($page_type);
                }
                $page->setColumna(null);
                $page->setBlog(null);
                break;
            case "sponsor":
                $page->setTemplate($page_type);
                $page->setPageType($page_type);
                $page->setColumna(null);
                $page->setBlog(null);
                break;
            case "column":
                $page->setTemplate($page_type);
                $page->setPageType($page_type);
                if ($columna_id == null) {
                    $data = $helpers->responseData($code = 400, "Column Note not created. Add column");
                    $response = $helpers->responseHeaders($code = 400, $data);
                    return $response;
                } else {
                    $page->setColumna($em->getRepository('BackendBundle:Columna')->find($columna_id));
                }
                break;
            case "blogpost":
                $page->setTemplate($page_type);
                $page->setPageType($page_type);
                if ($blog_id == null) {
                    $data = $helpers->responseData($code = 400, "Blog Note not created. Add blog");
                    $response = $helpers->responseHeaders($code = 400, $data);
                    return $response;
                } else {
                    $page->setBlog($em->getRepository('BackendBundle:Blog')->find($blog_id));
                }
                break;
            case "carton":
                $page->setTemplate($page_type);
                $page->setPageType($page_type);
                if ($cartonImages != null) {
                    $images = json_decode($cartonImages);
                    foreach ($images as $val) {
                        $image = $em->getRepository('BackendBundle:Image')->find($val);
                        $page->addImage($image);
                    }
                }
                break;
        }

        if ($category != null) {
            $category_main = $em->getRepository('BackendBundle:Category')->find($category);
            $page->setCategoryId($category_main);
        }

        //Si el slug no viene vacio y fue creado por el usuario

        if ($slug != null) {
            $title_final = $slug;
        } else {
            $title_final = $title;
        }
        $category_db = $em->getRepository('BackendBundle:Category')->find($category);

        switch ($page_type) {
            case "article":
                $slug_create = $category_db->getSlug() . '/' . Urlizer::urlize($title_final);
                $slug_validate = $this->slugValidate($slug_create);
                if ($slug_validate == true) {
                    $msg = 'Error. Slug exist in DB';
                    $data = $helpers->responseData($code = 400, $msg);
                    $response = $helpers->responseHeaders($code = 400, $data);

                    return $response;
                } else {
                    //Construyo un array con el id de la categoria y el title!
                    $array_slug = array(
                        'title' => $title_final,
                        'category' => $category
                    );
                    //Invoco mi funcion 'createSlug'
                    $slug_final = $this->createSlug($array_slug);

                    $page->setSlug($slug_final['value']);
                }
                break;
            case "sponsor":
                $slug_create = $category_db->getSlug() . '/' . Urlizer::urlize($title_final);
                $slug_validate = $this->slugValidate($slug_create);
                if ($slug_validate == true) {
                    $msg = 'Error. Slug exist in DB';
                    $data = $helpers->responseData($code = 400, $msg);
                    $response = $helpers->responseHeaders($code = 400, $data);

                    return $response;
                } else {
                    //Construyo un array con el id de la categoria y el title!
                    $array_slug = array(
                        'title' => $title_final,
                        'category' => $category
                    );
                    //Invoco mi funcion 'createSlug'
                    $slug_final = $this->createSlug($array_slug);

                    $page->setSlug($slug_final['value']);
                }
                break;
            case "carton":
                $authors_json = json_decode($authors);
                foreach ($authors_json as $key => $author) {
                    if ($key == 0) {
                        $author_db = $em->getRepository('BackendBundle:Author')->find($author);
                        $author_db_slug = $author_db->getSlug();
                        if ($author_db_slug == NULL) {
                            $msg = 'Error. Author don´t have slug';
                            $data = $helpers->responseData($code = 400, $msg);
                            $response = $helpers->responseHeaders($code = 400, $data);

                            return $response;
                        }
                    }
                }
                $slug_create = $category_db->getSlug() . '/' . $author_db_slug . '/' . Urlizer::urlize($title_final);
                $slug_validate = $this->slugValidate($slug_create);
                if ($slug_validate == true) {
                    $msg = 'Error. Slug exist in DB';
                    $data = $helpers->responseData($code = 400, $msg);
                    $response = $helpers->responseHeaders($code = 400, $data);

                    return $response;
                } else {
                    $array_slug = array(
                        'title' => $title_final,
                        'category' => $category_db->getSlug(),
                        'subCategory' => $author_db_slug
                    );
                    $slug_final = $this->createSlugMultiple($array_slug);
                    $page->setSlug($slug_final['value']);
                }
                break;
            case "column":
                $columna_db = $em->getRepository('BackendBundle:Columna')->find($columna_id);
                $slug_create = $category_db->getSlug() . '/' . $columna_db->getSlug() . '/' . Urlizer::urlize($title_final);
                $slug_validate = $this->slugValidate($slug_create);
                if ($slug_validate == true) {
                    $msg = 'Error. Slug exist in DB';
                    $data = $helpers->responseData($code = 400, $msg);
                    $response = $helpers->responseHeaders($code = 400, $data);

                    return $response;
                } else {
                    $array_slug = array(
                        'title' => $title_final,
                        'category' => $category_db->getSlug(),
                        'subCategory' => $columna_db->getSlug()
                    );
                    $slug_final = $this->createSlugMultiple($array_slug);
                    $page->setSlug($slug_final['value']);
                }
                break;
            case "blogpost":
                //$blog_db = $em->getRepository('BackendBundle:Blog')->find($blog_id);
                $slug_create = $category_db->getSlug() . '/' . Urlizer::urlize($title_final);
                $slug_validate = $this->slugValidate($slug_create);
                if ($slug_validate == true) {
                    $msg = 'Error. Slug exist in DB';
                    $data = $helpers->responseData($code = 400, $msg);
                    $response = $helpers->responseHeaders($code = 400, $data);

                    return $response;
                } else {
                    $array_slug = array(
                        'title' => $title_final,
                        'category' => $category
                    );
                    //Invoco mi funcion 'createSlug'
                    $slug_final = $this->createSlug($array_slug);
                    $page->setSlug($slug_final['value']);
                }
                break;
            default:
                $data = $helpers->responseData($code = 400, "Wrong Page type");
                $response = $helpers->responseHeaders($code = 400, $data);
                return $response;
        }

        if ($subCategories != null) {
            $categories_json = json_decode($subCategories);

            foreach ($categories_json as $key => $mydata) {
                //Solo si la subcategoria no es igual a la vategoria principal...
                if ($mydata != $category) {
                    $category = $em->getRepository('BackendBundle:Category')->find($mydata);
                    if ($category != null) {
                        $page->addCategory($category);
                    }
                }

            }
        }

        $page->setCreatedAt(new \DateTime());
        $page->setUpdatedAt(new \DateTime('0000-00-00 00:00:00'));

        if ($flag != null) {
            $flag_db = $em->getRepository("BackendBundle:Flags")->find($flag);
            $page->setFlag($flag_db);
        }

        $related_page = json_decode($related);
        $page->setRelated($related_page);

        $page->setBullets(json_decode($bullets));

        if ($newslatter == 'true') {
            $page->setNewslatter(1);
        } else {
            $page->setNewslatter(0);
        }

        if ($mostViewed == 'true') {
            $page->setMostViewed(1);
        } else {
            $page->setMostViewed(0);
        }

        $page->setPlace($place);
        $page->setRss(json_decode($rss));
        $page->setTitle($title);
        //Si el status existe y es "editing"
        if ($status != null) {
            $page->setStatus($status);
            $page->setEditingBy($em->getRepository('BackendBundle:WfUser')->find($user_id));
        } else {
            //Si es breaking news, el status == 'published'
            if ($isBreaking == 'true') {
                $page->setStatus("published");
                $page->setPublishedAt(new \DateTime());
                $page->setPublisher($em->getRepository('BackendBundle:WfUser')->find($user_id));
            } else {
                $page->setStatus("default");
            }
        }
        $page->setShortDescription(null);
        $page->setPortalId(3);
        $page->setHtml($html);
        $page->setHtmlSerialize(json_decode($htmlSerialized));
        $page->setCreator($em->getRepository('BackendBundle:WfUser')->find($user_id));
        $page->setElementHtml($elementHtml);
        $page->setElementHtmlSerialized(json_decode($mainElementSerialized));
        if ($isBreaking == 'true') {
            //Search current breaking_news
            $breaking_news = $em->getRepository('BackendBundle:Page')->findOneBy(array(
                'isBreaking' => 1,
                'status' => 'published'
            ));
            if ($breaking_news != null) {
                //Off current beaking_news
                $breaking_news_db = $em->getRepository('BackendBundle:Page')->find($breaking_news->getId());
                $breaking_news_db->setIsBreaking(0);
                $em->persist($breaking_news_db);
                $em->flush();
            }
            //Set new breakinn_news
            $page->setIsBreaking(1);
        } else {
            $page->setIsBreaking(0);
        }

        $settings = array(
            "comments" => null
        );
        $page->setSettings($settings);

        if ($authors) {
            $authors_json = json_decode($authors);
            $authors_temp = array();
            foreach ($authors_json as $key => $value) {
                //Si el Id es -1, significa que escribio texto simple.
                if (gettype($value) == 'integer') {
                    $page->addAuthor($em->getRepository('BackendBundle:Author')->find($value));
                } else if (gettype($value) == 'string') {
                    $authors_temp[] = $value;
                }
            }
            $content = array(
                'authors_temp' => $authors_temp,
                'authorsModified' => json_decode($authorsModified)
            );
        }

        $page->setContent(json_encode($content, JSON_UNESCAPED_UNICODE));

        $page->setModules(null);

        $page->setSeo(json_decode($seo));

        $page->setSocial(json_decode($social));

        if ($tags) {
            $tags_json = json_decode($tags);
            foreach ($tags_json as $key => $value) {
                $tag = $em->getRepository('BackendBundle:Tag')->find($value);
                if ($tag != null) {
                    $page->addTag($tag);
                }
            }
        }

        $code = substr(md5(rand(1000, 9999)), 0, 2) . date('His', time());
        $page->setCode($code);
        $page->setSlugRedirect($slugRedirect);

        if ($mainImage != null) {
            $image = $em->getRepository('BackendBundle:Image')->find($mainImage);
            if ($image != null) {
                $page->setMainImage($image);
            } else {
                switch ($page_type) {
                    case "column":
                        $title = "Foto Default de Nota Columna";
                        $description = "Foto Default de Nota Columna";
                        break;
                    case "carton":
                        $title = "Foto Default de Nota Carton";
                        $description = "Foto Default de Nota Carton";
                        break;
                    default:
                        $title = "Foto Default de Nota";
                        $description = "Foto Default de Nota";
                }
                $def_img = $em->getRepository('BackendBundle:Image')->findOneBy( //select default image
                    array(
                        "title" => $title,
                        "description" => $description,
                        "credito" => "Especial"
                    )
                );
                if ($def_img != null) {
                    $page->setMainImage($def_img);
                } else {
                    $def_img = $em->getRepository('BackendBundle:Image')->findOneBy( //select default image
                        array(
                            "title" => "Foto Default de Nota",
                            "description" => "Foto Default de Nota",
                            "credito" => "Especial"
                        )
                    );
                    $page->setMainImage($def_img);
                }
            }
        } else {
            switch ($page_type) {
                case "column":
                    $title = "Foto Default de Nota Columna";
                    $description = "Foto Default de Nota Columna";
                    break;
                case "carton":
                    $title = "Foto Default de Nota Carton";
                    $description = "Foto Default de Nota Carton";
                    break;
                default:
                    $title = "Foto Default de Nota";
                    $description = "Foto Default de Nota";
            }
            $def_img = $em->getRepository('BackendBundle:Image')->findOneBy( //select default image
                array(
                    "title" => $title,
                    "description" => $description,
                    "credito" => "Especial"
                )
            );
            if ($def_img != null) {
                $page->setMainImage($def_img);
            } else {
                $def_img = $em->getRepository('BackendBundle:Image')->findOneBy( //select default image
                    array(
                        "title" => "Foto Default de Nota",
                        "description" => "Foto Default de Nota",
                        "credito" => "Especial"
                    )
                );
                $page->setMainImage($def_img);
            }

        }

        $validator = $this->get('validator');
        $errors = $validator->validate($page);

        foreach ($errors as $error) {
            $messages[] = $error->getMessage();
        }

        if (count($errors) > 0) {

            $data = $helpers->responseData($code = 400, $messages);
            $response = $helpers->responseHeaders($code = 400, $data);

        } else {

            $array_categories = array();
            //Obtengo las subcategorias
            if ($subCategories != null) {
                $categories_json = json_decode($subCategories);
                foreach ($categories_json as $key => $mydata) {
                    $array_categories[] = $mydata;
                }
            }
            //Agrego tambien el 'id' de la categoria principal
            $array_categories[] = $category;

            $users = $this->get('app.users');
            $categories = $users->getCategoriesUserLogged();                         //get current user's cats

            //Obtengo las categorias asociadas al user logedo
            $cats_ids = array();
            foreach ($categories["categories"] as $l_cat)                               //get cat's ids
            {
                array_push($cats_ids, $l_cat->getId());
            }

            //Si existen al menos un registro del array de las secciones del usuario dentro del array de categorias del page
            if (array_intersect($array_categories, $cats_ids) || $categories["fullAccess"]) {
                $em->persist($page);
                $em->flush();
            } else {
                $data = $helpers->responseData($code = 401, "No tienes permiso para esta Seccion");
                $response = $helpers->responseHeaders($code = 401, $data);

                return $response;
            }

            //Y Hago una copia carbon a Page Version, con el servicio 'clonePage'
            //Le paso el 'id' de page actual, y la bandera "publish", el "number_version" es null porque viene de Page
            if ($isBreaking == 'true') {
                $page_clone->clonePage($page->getId(), $from = "publish", $number_version = null);
            }

            $msg = 'Page created!';
            $data = $helpers->responseData($code = 200, $msg);
            $data['page_id'] = $page->getId();
            $data['code_page'] = $page->getCode();
            $data['from'] = 'page';
            $response = $helpers->responseHeaders($code = 200, $data);
        }

        return $response;

    }

    /**
     * @ApiDoc(
     *    section = "Page",
     *    description="Publica una nota, metodo privado",
     *    requirements={
     *      {"name"="date", "dataType"="string", "required"=true, "description"="date, Example: 2017-08-11 11:16:07"},
     *      {"name"="id", "dataType"="string", "required"=true, "description"="Id page"},
     *      {"name"="from", "dataType"="string", "required"=true, "description"="page | page_version"},
     *      {"name"="ignoreUpdatedAt", "dataType"="boolean", "required"=true, "description"="Ignore UpdatedAt"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function publishPageAction(Request $request, $id = null)
    {
        $helpers = $this->get("app.helpers");
        $page_clone = $this->get("app.pages_clone");
        $em = $this->getDoctrine()->getManager();

        $from = $request->get('from');
        $ignoreUpdatedAt = $request->get('ignoreUpdatedAt');
        //Obtengo la fecha a programar la nota, si fuese enviada
        $date = $request->get('date');

        if ($id != null || $id == '{id}') {

            //Encuentro la nota que se va publicar, por el 'id'
            $page = $em->getRepository('BackendBundle:Page')->find($id);

            //Obtengo la ultima version de nota sobre PageVersion
            $latestPageVersion = $em->getRepository("BackendBundle:PageVersion")->latestVersion($id);
            //Y luego busco sobre PageVersion la nota con la ultima version y con el campo "page" == "id" que obtengo en la URL
            $page_version = $em->getRepository("BackendBundle:PageVersion")->findOneBy(array(
                'page' => $id,
                'versionNo' => $latestPageVersion
            ));

            //Si el from es 'page_version', actualizo 'page' con los datos de la ultima 'page_version', siempre y cuando no venga programada la nota
            if ($from == 'page_version' && $date == null && empty($date)) {

                $many_to_many = $page_version->getFieldsManyToMany();

                foreach ($many_to_many as $key => $indice) {
                    if ($key == 'categories') {
                        $categories_array = array();
                        foreach ($indice as $value) {
                            array_push($categories_array, $value);
                        }
                    }
                    if ($key == 'authors') {
                        $authors_array = array();
                        foreach ($indice as $value) {
                            array_push($authors_array, $value);
                        }
                    }
                    if ($key == 'images') {
                        $images_array = array();
                        foreach ($indice as $value) {
                            array_push($images_array, $value);
                        }
                    }
                    if ($key == 'tags') {
                        $tags_array = array();
                        foreach ($indice as $value) {
                            array_push($tags_array, $value);
                        }
                    }
                }

                //categories array to purga
                $remove_categories_purga = array();
                $add_categories_purga = array();
                //tags array to purga
                $remove_tags_purga = array();
                $add_tags_purga = array();

                //If data is new then remove and add data
                if ($categories_array != null) {
                    $categories_db = $page->getCategory();
                    foreach ($categories_db as $val) {
                        $page->removeCategory($val);
                        array_push($remove_categories_purga, $val->getSlug());
                    }

                    foreach ($categories_array as $val) {
                        $category = $em->getRepository('BackendBundle:Category')->find($val);
                        $page->addCategory($category);
                        array_push($add_categories_purga, $category->getSlug());
                    }
                    //If data is null remove data
                } else {
                    $categories_db = $page->getCategory();
                    foreach ($categories_db as $val) {
                        $page->removeCategory($val);
                        array_push($remove_categories_purga, $val->getSlug());
                    }
                }
                if ($authors_array != null) {
                    $authors_db = $page->getAuthor();
                    foreach ($authors_db as $val) {
                        $page->removeAuthor($val);
                    }

                    foreach ($authors_array as $val) {
                        $author = $em->getRepository('BackendBundle:Author')->find($val);
                        $page->addAuthor($author);
                    }
                }
                if ($images_array != null) {
                    $images_db = $page->getImage();
                    foreach ($images_db as $val) {
                        $page->removeImage($val);
                    }

                    foreach ($images_array as $val) {
                        $image = $em->getRepository('BackendBundle:Image')->find($val);
                        $page->addImage($image);
                    }
                }
                if ($tags_array != null) {
                    $tags_db = $page->getTag();
                    foreach ($tags_db as $val) {
                        $page->removeTag($val);
                        array_push($remove_tags_purga, $val->getSlug());
                    }

                    foreach ($tags_array as $val) {
                        $tag = $em->getRepository('BackendBundle:Tag')->find($val);
                        $page->addTag($tag);
                        array_push($add_tags_purga, $tag->getSlug());
                    }
                } else {
                    $tags_db = $page->getTag();
                    foreach ($tags_db as $val) {
                        $page->removeTag($val);
                        array_push($remove_tags_purga, $val->getSlug());
                    }
                }

                $page->setSlug($page_version->getSlug());

                if ($ignoreUpdatedAt == 'true') {
                    //$page->setUpdatedAt(new \DateTime());
                } else {
                    $page->setUpdatedAt(new \DateTime());
                }

                $page->setFlag($page_version->getFlag());

                $page->setRelated($page_version->getRelated());

                $page->setBullets($page_version->getBullets());

                $page->setNewslatter($page_version->getNewslatter());

                $page->setPlace($page_version->getPlace());

                $page->setMostViewed($page_version->getMostViewed());

                $page->setRss($page_version->getRss());

                $category = $em->getRepository('BackendBundle:Category')->find($page_version->getCategoryId());
                $page->setCategoryId($category);
                $page->setTitle($page_version->getTitle());
                $page->setShortDescription($page_version->getShortDescription());
                $page->setTemplate($page_version->getTemplate());
                $page->setPageType($page_version->getPageType());
                $page->setHtml($page_version->getHtml());
                $page->setHtmlSerialize($page_version->getHtmlSerialize());
                $page->setSettings($page_version->getSettings());

                $page->setContent($page_version->getContent());

                $page->setModules($page_version->getModules());

                $page->setSeo($page_version->getSeo());

                $page->setSocial($page_version->getSocial());

                $page->setEditingBy($page_version->getEditingBy());

                $page->setColumna($page_version->getColumna());

                $page->setBlog($page_version->getBlog());

                $page->setIsBreaking($page_version->getIsBreaking());

                $page->setElementHtml($page_version->getElementHtml());

                $page->setElementHtmlSerialized($page_version->getElementHtmlSerialized());

                $page->setCode($page_version->getCode());

                $page->setSlugRedirect($page_version->getSlugRedirect());

                $page->setMainImage($page_version->getMainImage());

            }

            //Bullets requeridos unicamente para 'article'
            //Si los bullets vienen vacios y sino es Breaking news, no podra publicar nota.
            if ($page->getPageType() == 'article' && count($page->getBullets()) == 0 && $page->getIsBreaking() != 1) {

                $msg = 'Bullets cannot be null.';
                $data = $helpers->responseData($code = 400, $msg);
                $response = $helpers->responseHeaders($code = 400, $data);

                //De lo contrario persisto en la BD, y publico
            } else {

                //categories array for purga
                $categories_purga = array();
                //tags array for purga
                $tags_purga = array();

                //No viene fecha, significa que debe ser publicada inmediatamente
                if ($date == null || empty($date)) {

                    //Obtengo user logeado
                    $user_id = $this->getUser()->getId();
                    $user = $em->getRepository('BackendBundle:WfUser')->find($user_id);
                    $page->setPublisher($user);

                    $pageVersion = $em->getRepository('BackendBundle:PageVersion')->findOneBy(array(
                        'page' => $id
                    ));

                    if ($pageVersion != null) {
                        $copia_carbon = $em->getRepository('BackendBundle:PageVersion')->getCopiaCarbon($id);
                        if ($copia_carbon->getPublishedAtPage() != null) {
                            $page->setPublishedAt($copia_carbon->getPublishedAtPage());
                        } else {
                            $page->setPublishedAt(new \DateTime());
                        }

                    } else {
                        $page->setPublishedAt(new \DateTime());
                        $page->setUpdatedAt(new \DateTime());
                    }

                    $page->setStatus("published");
                    //Tambien seteo page_version en published
                    if ($page_version != null) {

                        //Mando a trash las nota(s) de page_version que esten en 'published'
                        $pages_version = $em->getRepository('BackendBundle:PageVersion')->findBy(array('page' => $id));
                        foreach ($pages_version as $page_v) {
                            if ($page_v->getStatus() == 'published') {
                                $page_v->setStatus('trash');
                                $em->persist($page_v);
                                $em->flush();
                            }
                        }
                        $page_version->setStatus("published");
                        $page_version->setPublishedAt(new \DateTime());
                        $page_version->setUpdatedAt(new \DateTime());
                        $page_version->setPublisher($em->getRepository('BackendBundle:WfUser')->find($user->getId()));
                    }
                    $page->setNextPublishedAt(null);

                    $msg = 'Publish page';

                    $categories_db = $page->getCategory();
                    foreach ($categories_db as $category) {
                        array_push($categories_purga, $category->getSlug());
                    }
                    $tags_db = $page->getTag();
                    foreach ($tags_db as $tag) {
                        array_push($tags_purga, $tag->getSlug());
                    }

                    //Y Hago una copia carbon a Page Version
                    //Le paso el 'id' de page actual, y la bandera "published", el "number_version" es null porque viene de Page
                    if ($from == 'page') {
                        $page_clone->clonePage($id, $from = "publish", $number_version = null);
                    }

                    //si se programa, se le asigna un status scheduled, a la nota original y a la page version, si existiera tambien
                } else {

                    //Si la nota original esta en 'trash', al momento de programar
                    //seteo page_version en 'scheduled'
                    //if ($page->getStatus() == 'trash') {
                    if ($page_version != null) {
                        //Obtengo user logeado
                        $user_id = $this->getUser()->getId();
                        $user = $em->getRepository('BackendBundle:WfUser')->find($user_id);

                        $page_version->setStatus('scheduled');
                        $page_version->setNextPublishedAt(new \DateTime($date));
                        $page_version->setNextPublishedAtPage(new \DateTime($date));
                        //$page_version->setPublisher($user);

                        $em->persist($page_version);
                        $em->flush();
                    }
                    //Si la nota original esta publicada, no mover el status ni la fecha de publicacion
                    if ($page->getStatus() != 'published') {
                        $page->setStatus("scheduled");
                        $page->setPublishedAt(null);
                    }

                    //$page->setStatus("scheduled");
                    $page->setNextPublishedAt(new \DateTime($date));

                    $msg = 'Scheduled page';

                    $categories_db = $page->getCategory();
                    foreach ($categories_db as $category) {
                        array_push($categories_purga, $category->getSlug());
                    }

                    $tags_db = $page->getTag();
                    foreach ($tags_db as $tag) {
                        array_push($tags_purga, $tag->getSlug());
                    }

                    //Y Hago una copia carbon a Page Version!
                    //Le paso el 'id' de page actual, y la bandera "scheduled", el "number_version" es null porque viene de Page
                    if ($from == 'page') {
                        $page_clone->clonePage($id, $from = "scheduled", $number_version = null);
                    }
                }

                $em->persist($page);
                $em->flush();

                if ($page->getPageType() == 'tv' && $page->getStatus() == 'published') {
                    $page_category = $page->getCategoryId();
                    $category_id = $page_category->getId();

                    $resultDSG = $this->allAction($this->setMyRequest("page", "tv", "{\"search\":\"*\",\"categoryId\":" . $category_id . "}", 1, 13, true, 0));
                    $valorfile = $this->_cache("pagetv{'search':'*','categoryId':" . $category_id . "}113true0", $resultDSG);
                }

                $res = $helpers->Purga($page->getSlug());
                $res = $helpers->Purga(strstr($page->getSlug(), "/", true)); //purgando la portada de la seccion de la nota

                //Purga to subcategories only origin 'page'
                foreach ($categories_purga as $cat) {
                    $res = $helpers->Purga($cat);
                }
                //Purga to removed subcategories only origin 'page_version'
                foreach ($remove_categories_purga as $cat) {
                    $res = $helpers->Purga($cat);
                }
                //Purga to added subcategories only origin 'page_version'
                foreach ($add_categories_purga as $cat) {
                    $res = $helpers->Purga($cat);
                }
                //Purga to tags only origin 'page'
                foreach ($tags_purga as $tag) {
                    $res = $helpers->Purga("tag/" . $tag);
                }
                //Purga to removed tags only origin 'page_version'
                foreach ($remove_tags_purga as $tag) {
                    $res = $helpers->Purga("tag/" . $tag);
                }
                //Purga to added tags only origin 'page_version'
                foreach ($add_tags_purga as $tag) {
                    $res = $helpers->Purga("tag/" . $tag);
                }

                $data = $helpers->responseData($code = 200, $msg);
                $response = $helpers->responseHeaders($code = 200, $data);
            }

        } else {
            $msg = 'Missing page id';
            $data = $helpers->responseData($code = 400, $msg);
            $response = $helpers->responseHeaders($code = 400, $data);
        }

        return $response;
    }


    private function minify_output($buffer)
    {
        $search = array(
            '/\>[^\S ]+/s',
            '/[^\S ]+\</s',
            '/(\s)+/s'
        );
        $replace = array(
            '>',
            '<',
            '\\1'
        );
        if (preg_match("/\<html/i", $buffer) == 1 && preg_match("/\<\/html\>/i", $buffer) == 1) {
            $buffer = preg_replace($search, $replace, $buffer);
        }
        return $buffer;
    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     *
     * @ApiDoc(
     *  section = "Page",
     *  description="Trash list.",
     *     requirements={
     *     {"name"="json", "dataType"="array", "required"=false, "description"="Conditions props: createdAt, type,updatedAt,slug, title, description, sourceId, imageName, fields, credito, portalId. Don't use special chars and use just one word by criteria in no date type columns. Without conditions, you will retrieve a list of elements of site with id 1"},
     *     {"name"="page", "dataType"="int", "required"="false", "default"="1", "description"="Page"},
     *     {"name"="size", "dataType"="int", "required"="false", "default"="10", "description"="Tamaño de la pagina, si se omite es 10"}
     *    },
     *   headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     * )
     */
    public function trashListAction(Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        $items = array();
        $conditions = array();
        $boolQuery = new BoolQuery();
        $queryString = new QueryString();
        $query = new Query();
        $filter = new Terms();
        $json = $request->get('json', "");
        $page = $request->get('page', 1);
        $size = $request->get('size', 10);
        $finder = $this->get('fos_elastica.finder.efredisenio.page');
        $paginator = $this->get('knp_paginator');

        $queryString->setQuery("*");
        $boolQuery->addMust($queryString);
        if ($json != "") {
            $conditions = json_decode($json);
            $filter->setTerms("status", ["trash"]);
            foreach ($conditions as $key => $val) {
                $filter->setTerms($key, [$val]);
            }
            $query->setSort(['updatedAt' => ['order' => 'desc']]);
            $boolQuery->addMust($filter);
            $query->setQuery($boolQuery);
            $hybridResults = $finder->findHybrid($query);
            foreach ($hybridResults as $hybridResult) {         //add dynamic conditions (json)
                $items[] = $hybridResult->getResult()->getHit();
            }
            $pagination = $paginator->paginate($items, $page, $size);
            $total_items_count = $pagination->getTotalItemCount();
            $data = array(
                "status" => "success",
                "total_items_count" => $total_items_count,
                "current_page" => $page,
                "items_per_page" => $size,
                "data" => $pagination
            );
        } else {
            $filter->setTerms("status", ["trash"]);
            $query->setSort(['updatedAt' => ['order' => 'desc']]);
            $boolQuery->addMust($filter);
            $query->setQuery($boolQuery);
            $hybridResults = $finder->findHybrid($query);
            foreach ($hybridResults as $hybridResult) {
                $items[] = $hybridResult->getResult()->getHit();
            }
            $pagination = $paginator->paginate($items, $page, $size);
            $total_items_count = $pagination->getTotalItemCount();

            if ($total_items_count > 0) {
                $data = array(
                    "status" => "success",
                    "total_items_count" => $total_items_count,
                    "current_page" => $page,
                    "items_per_page" => $size,
                    "data" => $pagination
                );
            } else {
                $data = array(
                    "status" => "No Data",
                    "total_items_count" => 0,
                    "current_page" => 0,
                    "items_per_page" => 0,
                    "data" => "No Data"
                );
            }
        }
        $jsonContent = $serializer->serialize($data, 'json');
        $response = new Response($jsonContent);

        return $response;
    }

    /**
     * @ApiDoc(
     *    section = "Page",
     *    description="Unpublish Page, metodo privado",
     *    requirements={
     *      {"name"="id", "dataType"="string", "required"=true, "description"="Id page"},
     *      {"name"="noVersion", "dataType"="string", "required"=true, "description"="No Version"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function unpublishPageAction(Request $request, $id = null)
    {
        $no_version = $request->get('noVersion');

        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('BackendBundle:Page')->find($id);

        if ($page != null) {

            $latest_version = $em->getRepository('BackendBundle:PageVersion')->latestVersion($id);
            /*$page_version = $em->getRepository('BackendBundle:PageVersion')->getPageVersion($id, $latest_version);*/
            $page_version = $em->getRepository('BackendBundle:PageVersion')->getPageVersion($id, $no_version);

            /*if ($latest_version > 1) {*/
            //Tambien despublico page_version, siempre y cuando no sea copia_carbon
            /*if ($page_version->getVersionNo() > 1) {*/
            /*$page_version->setStatus('trash');
            $page_version->setPublishedAt(null);*/
            /*$page_version->setNextPublishedAt(null);*/
            /*$em->persist($page_version);
            $em->flush();*/
            /*}*/
            /*}*/

            if ($page_version->getStatus() != 'scheduled') {
                $page->setStatus('trash');
                //$page->setPublishedAt(null);
                $page->setNextPublishedAt(null);
                $page->setPublisher(null);
                $em->persist($page);
                $em->flush();
            } else {
                $page->setNextPublishedAt(null);
            }

            //page_version
            $page_version->setStatus('trash');
            $page_version->setPublishedAt(null);
            $page_version->setPublisher(null);
            $em->persist($page_version);
            $em->flush();

            $res = $helpers->Purga($page->getSlug());
            $res = $helpers->Purga(strstr($page->getSlug(), "/", true)); //purgando la portada de la seccion de la nota

            $msg = 'Unpublish page success';
            $data = $helpers->responseData($code = 200, $msg);
            $response = $helpers->responseHeaders($code = 200, $data);
        } else {
            $msg = 'Page not found in DB';
            $data = $helpers->responseData($code = 404, $msg);
            $response = $helpers->responseHeaders($code = 404, $data);
        }

        return $response;

    }

    /**
     * @ApiDoc(
     *    section = "Page",
     *    description="Clone Page, metodo privado",
     *    requirements={
     *      {"name"="id", "dataType"="string", "required"=true, "description"="Id page"},
     *      {"name"="version", "dataType"="string", "required"="true", "description"="Number Version"},
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function clonePageAction(Request $request, $id = null)
    {
        $version = $request->get('version');

        $page_clone = $this->get("app.pages_clone");

        $page_version = (isset($version)) ? $version : null;
        $response = $page_clone->clonePage($id, $from = "clone", $page_version);

        return $response;

    }

    /**
     *  Para acceder a este metodo, se require autorizacion(token). Los campos requeridos obligatoriamente son 'title' y 'categories'
     *
     * @ApiDoc(
     *     section = "Page",
     *     description="Guardar una Nota, metodo privado",
     *     requirements={
     *      {"name"="type", "dataType"="string", "required"=true, "description"="article | column | blogpost | carton | sponsor"},
     *      {"name"="category", "dataType"="integer", "required"=true, "description"="Category Main Id. Is required. Example: 1"},
     *      {"name"="title", "dataType"="string", "required"=true, "description"="Title, is required"},
     *      {"name"="authors", "dataType"="json", "required"=true, "description"="Authors JSON. Example: [148,149]"},
     *      {"name"="tags", "dataType"="json", "required"=true, "description"="Tags JSON. EXample: [1,2]"},
     *      {"name"="flag", "dataType"="integer", "required"=true, "description"="Flag Id"},
     *      {"name"="html", "dataType"="string", "required"=true, "description"="HTML"},
     *      {"name"="htmlSerialized", "dataType"="json", "required"=true, "description"="HTML Serialized"},
     *      {"name"="subCategories", "dataType"="json", "required"=true, "description"="Sub Categories ID. Example: [1,2,3]"},
     *      {"name"="related", "dataType"="json", "required"=true, "description"="Related Pages"},
     *      {"name"="bullets", "dataType"="json", "required"=true, "description"="Bullets JSON. Example: ['mi bullet 1', 'mi bullet 2']"},
     *      {"name"="place", "dataType"="text", "required"=true, "description"="Place"},
     *      {"name"="mostViewed", "dataType"="boolean", "required"=true, "description"="true | false"},
     *      {"name"="newsletter", "dataType"="boolean", "required"=true, "description"="true | false"},
     *      {"name"="rss", "dataType"="json", "required"=true, "description"="RSS"},
     *      {"name"="columna", "dataType"="json", "required"=true, "description"="Columna Id. Only for arcticle page"},
     *      {"name"="mainElementHTML", "dataType"="string", "required"=true, "description"="Main Element HTML"},
     *      {"name"="mainElementSerialized", "dataType"="string", "required"=true, "description"="Main Element Serialized"},
     *      {"name"="blog", "dataType"="string", "required"=true, "description"="Blog Id. Only for arcticle page"},
     *      {"name"="isBreaking", "dataType"="boolean", "required"=true, "description"="Is Breaking. true|false"},
     *      {"name"="cartonImages", "dataType"="json", "required"=true, "description"="Carton Imgs Id. Only for carton page. Example: [196743,196744]"},
     *      {"name"="seo", "dataType"="json", "required"=true, "description"="SEO JSON"},
     *      {"name"="social", "dataType"="json", "required"=true, "description"="Social JSON"},
     *      {"name"="mainImage", "dataType"="json", "required"=true, "description"="Main Image Id"},
     *      {"name"="from", "dataType"="json", "required"=true, "description"="From Page"},
     *      {"name"="code", "dataType"="json", "required"=true, "description"="Code Page"},
     *      {"name"="slug", "dataType"="string", "required"=true, "description"="Slug. If user custom slug"},
     *      {"name"="authorsModified", "dataType"="json", "required"=true, "description"="Authors Modified"},
     *      {"name"="slug", "dataType"="string", "required"=true, "description"="Slug. If user custom slug"},
     *      {"name"="slugRedirect", "dataType"="string", "required"=true, "description"="Slug Redirect"},
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     *
     */
    public function savePageAction(Request $request, $id = null)
    {
        $helpers = $this->get("app.helpers");

        $user_id = $this->getUser()->getId();

        $page_type = $request->get('type');
        $title = $request->get('title');
        $authors = $request->get('authors');
        $category = $request->get('category');
        $tags = $request->get('tags');
        $flag = $request->get('flag');
        $newslatter = $request->get('newsletter');
        $html = $request->get('html');
        $htmlSerialized = $request->get('htmlSerialized');
        $subCategories = $request->get('subCategories');
        $related = $request->get('related');
        $bullets = $request->get('bullets');
        $place = $request->get('place');
        $mostViewed = $request->get('mostViewed');
        $rss = $request->get('rss');
        $columna_id = $request->get('columna');
        $blog_id = $request->get('blog');
        $elementHtml = $request->get('mainElementHTML');
        $mainElementSerialized = $request->get('mainElementSerialized');
        $isBreaking = $request->get('isBreaking');
        $cartonImages = $request->get('cartonImages');
        $seo = $request->get('seo');
        $social = $request->get('social');
        $mainImage = $request->get('mainImage');
        $slug = $request->get('slug');
        $authorsModified = $request->get('authorsModified');
        $slugRedirect = $request->get('slugRedirect');

        //field 'from'
        $from = $request->get('from');
        //field 'code'
        $code = $request->get('code');

        $em = $this->getDoctrine()->getManager();

        //Validacion de 'page_type'
        $pages_type_array = array('article', 'column', 'blogpost', 'carton', 'sponsor');

        if (!in_array($page_type, $pages_type_array)) {
            $data = $helpers->responseData($code = 400, "Wrong Page Type");
            $response = $helpers->responseHeaders($code = 400, $data);
            return $response;
        }

        //Validations fields
        if ($authors == null) {
            $data = $helpers->responseData($code = 400, "Missing Author.");
            $response = $helpers->responseHeaders($code = 400, $data);
            return $response;
        }

        if ($from != null) {
            if ($from == 'page') {
                $page = $em->getRepository("BackendBundle:Page")->find($id);
            } else {
                $latestPageVersion = $em->getRepository("BackendBundle:PageVersion")->latestVersion($id);
                $page = $em->getRepository("BackendBundle:PageVersion")->findOneBy(array(
                    'page' => $id,
                    'versionNo' => $latestPageVersion
                ));
            }
        } else {
            $msg = 'Error. Field from is missing';
            $data = $helpers->responseData($code = 400, $msg);
            $response = $helpers->responseHeaders($code = 400, $data);

            return $response;
        }

        if ($page != null) {
            //Si la nota a guardar esta en 'editing' y aparte el code que viene arrastrando el user es el mismo al code de la BD...
            if ($page->getStatus() == 'editing' && $code == $page->getCode()) {
                $em = $this->getDoctrine()->getManager();

                //El slug solo se podra modificar si proviene de page, siempre y cuando no este publicada la nota.
                if ($from == 'page') {

                    if ($slug != null) {
                        $title_final = $slug;
                    } else {
                        $title_final = $title;
                    }
                    $category_db = $em->getRepository('BackendBundle:Category')->find($category);

                    switch ($page_type) {
                        case "article":
                            $slug_edit = $category_db->getSlug() . '/' . Urlizer::urlize($title_final);
                            //If slug_edit is not equal a slug_current
                            if ($page->getSlug() != $slug_edit) {
                                //Validate slug_edit
                                $slug_validsate = $this->slugValidate($slug_edit);
                                //If slug_validate exist in DB
                                if ($slug_validsate == true) {
                                    $msg = 'Error. Slug exist in DB';
                                    $data = $helpers->responseData($code = 400, $msg);
                                    $response = $helpers->responseHeaders($code = 400, $data);

                                    return $response;
                                } else {
                                    //Construyo un array con el id de la categoria y el title!
                                    $array_slug = array(
                                        'title' => $title_final,
                                        'category' => $category
                                    );

                                    //Invoco mi funcion 'createSlug'
                                    $slug_final = $this->createSlug($array_slug);

                                    $page->setSlug($slug_final['value']);
                                }
                            }
                            break;
                        case "sponsor":
                            $slug_edit = $category_db->getSlug() . '/' . Urlizer::urlize($title_final);
                            //If slug_edit is not equal a slug_current
                            if ($page->getSlug() != $slug_edit) {
                                //Validate slug_edit
                                $slug_validsate = $this->slugValidate($slug_edit);
                                if ($slug_validsate == true) {
                                    $msg = 'Error. Slug exist in DB';
                                    $data = $helpers->responseData($code = 400, $msg);
                                    $response = $helpers->responseHeaders($code = 400, $data);

                                    return $response;
                                } else {
                                    //Construyo un array con el id de la categoria y el title!
                                    $array_slug = array(
                                        'title' => $title_final,
                                        'category' => $category
                                    );

                                    //Invoco mi funcion 'createSlug'
                                    $slug_final = $this->createSlug($array_slug);

                                    $page->setSlug($slug_final['value']);
                                }
                            }
                            break;
                        case "carton":
                            //Loop authors_json
                            $authors_json = json_decode($authors);
                            foreach ($authors_json as $key => $author) {
                                //Only first position array, get author_slug
                                if ($key == 0) {
                                    $author_db = $em->getRepository('BackendBundle:Author')->find($author);
                                    $author_db_slug = $author_db->getSlug();
                                    if ($author_db_slug == NULL) {
                                        $msg = 'Error. Author don´t have slug';
                                        $data = $helpers->responseData($code = 400, $msg);
                                        $response = $helpers->responseHeaders($code = 400, $data);

                                        return $response;
                                    }
                                }
                            }

                            $slug_edit = $category_db->getSlug() . '/' . $author_db_slug . '/' . Urlizer::urlize($title_final);
                            if ($page->getSlug() != $slug_edit) {

                                $slug_validsate = $this->slugValidate($slug_edit);
                                if ($slug_validsate == true) {
                                    $msg = 'Error. Slug exist in DB';
                                    $data = $helpers->responseData($code = 400, $msg);
                                    $response = $helpers->responseHeaders($code = 400, $data);

                                    return $response;
                                } else {
                                    //Construyo un array con el id de la categoria y el title!
                                    $array_slug = array(
                                        'title' => $title_final,
                                        'category' => $category_db->getSlug(),
                                        'subCategory' => $author_db_slug
                                    );
                                    $slug_final = $this->createSlugMultiple($array_slug);
                                    $page->setSlug($slug_final['value']);
                                }
                            }
                            break;
                        case "column":
                            $columna_db = $em->getRepository('BackendBundle:Columna')->find($columna_id);
                            $slug_edit = $category_db->getSlug() . '/' . $columna_db->getSlug() . '/' . Urlizer::urlize($title_final);
                            if ($page->getSlug() != $slug_edit) {

                                $slug_validsate = $this->slugValidate($slug_edit);
                                if ($slug_validsate == true) {
                                    $msg = 'Error. Slug exist in DB';
                                    $data = $helpers->responseData($code = 400, $msg);
                                    $response = $helpers->responseHeaders($code = 400, $data);

                                    return $response;
                                } else {
                                    //Construyo un array con el id de la categoria y el title!
                                    $array_slug = array(
                                        'title' => $title_final,
                                        'category' => $category_db->getSlug(),
                                        'subCategory' => $columna_db->getSlug()
                                    );
                                    $slug_final = $this->createSlugMultiple($array_slug);
                                    $page->setSlug($slug_final['value']);
                                }
                            }
                            break;
                        case "blogpost":
                            //$blog_db = $em->getRepository('BackendBundle:Blog')->find($blog_id);
                            $slug_edit = $category_db->getSlug() . '/' . Urlizer::urlize($title_final);
                            if ($page->getSlug() != $slug_edit) {

                                $slug_validsate = $this->slugValidate($slug_edit);
                                if ($slug_validsate == true) {
                                    $msg = 'Error. Slug exist in DB';
                                    $data = $helpers->responseData($code = 400, $msg);
                                    $response = $helpers->responseHeaders($code = 400, $data);

                                    return $response;
                                } else {
                                    //Construyo un array con el id de la categoria y el title!
                                    $array_slug = array(
                                        'title' => $title_final,
                                        'category' => $category
                                    );
                                    //Invoco mi funcion 'createSlug'
                                    $slug_final = $this->createSlug($array_slug);
                                    $page->setSlug($slug_final['value']);
                                }
                            }
                            break;
                        default:
                            $data = $helpers->responseData($code = 400, "Wrong Page type");
                            $response = $helpers->responseHeaders($code = 400, $data);
                            return $response;
                    }
                }

                //if from is not page, set updated_at
                if ($from != 'page') {
                    $page->setUpdatedAt(new \DateTime());
                }

                if ($flag != null) {
                    $flag_fb = $em->getRepository('BackendBundle:Flags')->find($flag);
                    $page->setFlag($flag_fb);
                }

                if ($related != null) {
                    $related_page = json_decode($related);
                    $page->setRelated($related_page);
                }

                if ($bullets != null) {
                    $page->setBullets(json_decode($bullets));
                }

                if ($newslatter != null) {
                    if ($newslatter == 'true') {
                        $page->setNewslatter(1);
                    } else {
                        $page->setNewslatter(0);
                    }
                }

                if ($place != null) {
                    $page->setPlace($place);
                }

                if ($mostViewed != null) {
                    if ($mostViewed == 'true') {
                        $page->setMostViewed(1);
                    } else {
                        $page->setMostViewed(0);
                    }
                }

                if ($rss != null) {
                    $page->setRss(json_decode($rss));
                }

                if ($category != null) {
                    $category_db = $em->getRepository('BackendBundle:Category')->find($category);
                    $page->setCategoryId($category_db);
                }

                $category_db = $em->getRepository('BackendBundle:Category')->find($category);
                $category_slug = $category_db->getSlug();

                switch ($page_type) {
                    case "article":
                        $find_key = '/';
                        $pos = strpos($category_slug, $find_key);
                        if ($pos !== false) {
                            $first_level = strstr($category_slug, '/', true);
                            if ($first_level == 'pages' || $first_level == 'tv') {
                                $page->setTemplate($first_level);
                                $page->setPageType($first_level);
                            } else {
                                $page->setTemplate($page_type);
                                $page->setPageType($page_type);
                            }
                        } else {
                            $page->setTemplate($page_type);
                            $page->setPageType($page_type);
                        }
                        $page->setColumna(null);
                        $page->setBlog(null);
                        break;
                    case "sponsor":
                        $page->setTemplate($page_type);
                        $page->setPageType($page_type);
                        $page->setColumna(null);
                        $page->setBlog(null);
                        break;
                    case "column":
                        $page->setTemplate($page_type);
                        $page->setPageType($page_type);
                        if ($columna_id != null) {
                            $page->setColumna($em->getRepository('BackendBundle:Columna')->find($columna_id));
                        }
                        break;
                    case "blogpost":
                        $page->setTemplate($page_type);
                        $page->setPageType($page_type);
                        if ($blog_id != null) {
                            $page->setBlog($em->getRepository('BackendBundle:Blog')->find($blog_id));
                        }
                        break;
                    case "carton":
                        if ($cartonImages != null) {
                            if ($from != 'page_version') {
                                $images_db = $page->getImage();
                                foreach ($images_db as $val) {
                                    $page->removeImage($val);
                                }
                                $images = json_decode($cartonImages);
                                foreach ($images as $val) {
                                    $image = $em->getRepository('BackendBundle:Image')->find($val);
                                    $page->addImage($image);
                                }
                            }
                        }
                        break;
                    default:
                        $msg = 'Error. Wrong page type';
                        $data = $helpers->responseData($code = 400, $msg);
                        $response = $helpers->responseHeaders($code = 400, $data);

                        return $response;
                }

                if ($title != null) {
                    $page->setTitle($title);
                }

                $page->setShortDescription(null);

                /*$page->setTemplate($page_type);
                $page->setPageType($page_type);*/
                $page->setPortalId(3);
                if ($html != null) {
                    $page->setHtml($html);
                }
                if ($htmlSerialized != null) {
                    $page->setHtmlSerialize(json_decode($htmlSerialized));
                }
                if ($elementHtml != null) {
                    $page->setElementHtml($elementHtml);
                }
                if ($mainElementSerialized != null) {
                    $page->setElementHtmlSerialized(json_decode($mainElementSerialized));
                }

                $page->setCreator($em->getRepository('BackendBundle:WfUser')->find($user_id));
                if ($isBreaking != null) {
                    if ($isBreaking == 'true') {

                        //Search current breaking_news
                        $breaking_news = $em->getRepository('BackendBundle:Page')->findOneBy(array(
                            'isBreaking' => 1,
                            'status' => 'published'
                        ));

                        if ($breaking_news != null) {
                            $breaking_news_db = $em->getRepository('BackendBundle:Page')->find($breaking_news->getId());
                            $breaking_news_db->setIsBreaking(0);
                            $em->persist($breaking_news_db);
                            $em->flush();
                        }

                        $page->setIsBreaking(1);
                        $helpers->purgaPublica("public/notas/breaking-news");

                    } else {
                        $page->setIsBreaking(0);
                        $helpers->purgaPublica("public/notas/breaking-news");
                    }
                }

                $code = substr(md5(rand(1000, 9999)), 0, 2) . date('His', time());
                $page->setCode($code);

                if ($mainImage != null) {
                    $image = $em->getRepository('BackendBundle:Image')->find($mainImage);
                    if ($image != null) {
                        $page->setMainImage($image);
                    } else {
                        switch ($page_type) {
                            case "column":
                                $title = "Foto Default de Nota Columna";
                                $description = "Foto Default de Nota Columna";
                                break;
                            case "carton":
                                $title = "Foto Default de Nota Carton";
                                $description = "Foto Default de Nota Carton";
                                break;
                            default:
                                $title = "Foto Default de Nota";
                                $description = "Foto Default de Nota";
                        }
                        $def_img = $em->getRepository('BackendBundle:Image')->findOneBy( //select default image
                            array(
                                "title" => $title,
                                "description" => $description,
                                "credito" => "Especial"
                            )
                        );
                        if ($def_img != null) {
                            $page->setMainImage($def_img);
                        } else {
                            $def_img = $em->getRepository('BackendBundle:Image')->findOneBy( //select default image
                                array(
                                    "title" => "Foto Default de Nota",
                                    "description" => "Foto Default de Nota",
                                    "credito" => "Especial"
                                )
                            );
                            $page->setMainImage($def_img);
                        }

                    }
                }

                $settings = array(
                    "comments" => null
                );
                $page->setSettings($settings);

                $page->setModules(NULL);

                if ($seo != null) {
                    $page->setSeo(json_decode($seo));
                }

                if ($social != null) {
                    $page->setSocial(json_decode($social));
                }

                $authors_temp = array();

                switch ($from) {
                    case "page":
                        //Si 'author' no es null, remuevo datos relacionales
                        if ($authors != null) {
                            $authors_db = $page->getAuthor();
                            foreach ($authors_db as $val) {
                                $page->removeAuthor($val);
                            }
                        }
                        if ($authors != null) {
                            $authors_json = json_decode($authors);
                            $authors_temp = array();

                            foreach ($authors_json as $key => $value) {
                                //Si el Id es integer, agrego a tabla relacional
                                if (gettype($value) == 'integer') {
                                    $author_db = $em->getRepository('BackendBundle:Author')->find($value);
                                    if ($author_db != null) {
                                        $page->addAuthor($author_db);
                                    } else {
                                        $data = $helpers->responseData($code = 400, "Authors not found in DB");
                                        $response = $helpers->responseHeaders($code = 400, $data);
                                        return $response;
                                    }
                                    //Si es el data es integer, agrego al array authors_temp
                                } else if (gettype($value) == 'string') {
                                    $authors_temp[] = $value;
                                }
                            }
                        }

                        if ($tags != null) {
                            //Remuevo datos relacionales, si no es null tags
                            $tags_db = $page->getTag();
                            foreach ($tags_db as $val) {
                                $page->removeTag($val);
                            }

                            //Agrego datos relacionales
                            $tags_json = json_decode($tags);
                            foreach ($tags_json as $key => $value) {
                                $tag = $em->getRepository('BackendBundle:Tag')->find($value);
                                if ($tag != null) {
                                    $page->addTag($tag);
                                }
                            }
                        }

                        if ($subCategories != null) {
                            //Remuevo datos relacionales, si no es null
                            $categories_db = $page->getCategory();
                            foreach ($categories_db as $val) {
                                $page->removeCategory($val);
                            }
                            //Agrego datos relacionales
                            $categories_json = json_decode($subCategories);
                            foreach ($categories_json as $key => $mydata) {
                                $category = $em->getRepository('BackendBundle:Category')->find($mydata);
                                if ($category != null) {
                                    $page->addCategory($category);
                                }
                            }
                        }

                        $content = array(
                            'authors_temp' => $authors_temp
                        );

                        break;
                    case "page_version":
                        //Armo estructura para el campo 'many_to_many'
                        $categories_array = array();
                        $authors_array = array();
                        $images_array = array();
                        $tags_array = array();

                        if ($subCategories != null) {
                            $subCategories_json = json_decode($subCategories);
                            foreach ($subCategories_json as $v) {
                                array_push($categories_array, $v);
                            }
                        }
                        if ($authors != null) {
                            $authors_json = json_decode($authors);
                            foreach ($authors_json as $v) {
                                if (gettype($v) == 'integer') {
                                    array_push($authors_array, $v);
                                } elseif (gettype($v) == 'string') {
                                    $authors_temp[] = $v;
                                }
                            }
                        }

                        if ($page_type == 'carton') {
                            if ($cartonImages != null) {
                                $catones_imgs_json = json_decode($cartonImages);
                                foreach ($catones_imgs_json as $v) {
                                    array_push($images_array, $v);
                                }
                            }
                        }
                        if ($tags != null) {
                            $tags_json = json_decode($tags);
                            foreach ($tags_json as $v) {
                                array_push($tags_array, $v);
                            }
                        }

                        $fields_many_to_many = array(
                            'categories' => $categories_array,
                            'authors' => $authors_array,
                            'images' => $images_array,
                            'tags' => $tags_array
                        );

                        $page->setFieldsManytoMany($fields_many_to_many);

                        $content = array(
                            'authors_temp' => $authors_temp
                        );

                        break;
                }

                $content['authorsModified'] = json_decode($authorsModified);
                $page->setContent(json_encode($content, JSON_UNESCAPED_UNICODE));

                $page->setSlugRedirect($slugRedirect);

                $em->persist($page);
                $em->flush();

                $msg = 'Page saved!';
                $data = $helpers->responseData($code = 200, $msg);
                if ($from == 'page') {
                    $data['from'] = 'page';
                    $data['code_page'] = $page->getCode();
                    $data['status_page'] = $page->getStatus();
                } elseif ($from == 'page_version') {
                    $data['from'] = 'page_version';
                    $data['code_page'] = $page->getCode();
                    $data['status_page'] = $page->getStatus();
                }

                $response = $helpers->responseHeaders($code = 200, $data);
            } else {
                $msg = 'Error. Someone saved this note. Your changes will be lost.';
                $data = $helpers->responseData($code = 400, $msg);
                $data['code_page'] = $page->getCode();
                $response = $helpers->responseHeaders($code = 400, $data);
            }
        } else {
            $msg = 'Error. Page Id not found in DB';
            $data = $helpers->responseData($code = 404, $msg);
            $response = $helpers->responseHeaders($code = 404, $data);
        }

        return $response;

    }

    /**
     * @ApiDoc(
     *    section = "Page",
     *    description="List Clone Page, metodo privado",
     *    requirements={
     *      {"name"="id", "dataType"="string", "required"=true, "description"="id page"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function getClonePageAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $helpers = $this->get("app.helpers");
        $listPageVersion = $em->getRepository("BackendBundle:PageVersion")->listVersion($id);

        if ($listPageVersion != null) {
            $data = array(
                "status" => "success",
                "data" => $listPageVersion
            );
        } else {
            $msg = 'Page Version not found in DB';
            $data = $helpers->responseData($code = 404, $msg);
            $response = $helpers->responseHeaders($code = 404, $data);

            return $response;
        }

        return $helpers->json($data);

    }

    /**
     * @ApiDoc(
     *    section = "Page",
     *    description="Change page status",
     *    requirements={
     *      {"name"="id",       "dataType"="string", "required"=true, "description"="id page"},
     *      {"name"="from",     "dataType"="string", "required"=true, "description"="Possible values: page | page_version"},
     *      {"name"="status",   "dataType"="string", "required"=true, "description"="default"},
     *      {"name"="code",     "dataType"="string", "required"=true, "description"="default"},
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function setPageStatusAction($id, $from = "page", $status = "default", $code = "")
    {
        $em = $this->getDoctrine()->getManager();
        $helpers = $this->get("app.helpers");
        $error = "Type error";

        if ($from == "page") {
            $page = $em->getRepository('BackendBundle:Page')->find($id);

        } else {
            $latestPageVersion = $em->getRepository("BackendBundle:PageVersion")->latestVersion($id);
            $page = $em->getRepository("BackendBundle:PageVersion")->findOneBy(array(
                    'page' => $id,
                    'versionNo' => $latestPageVersion
                )
            );
        }
        switch ($status) {
            case "editing":
                if ($page->getStatus() != "default") {
                    $data = $helpers->responseData($code = 400, $error);
                    $response = $helpers->responseHeaders($code = 400, $data);

                    return $response;
                }
                break;
            /*case "default":
                if ($page->getStatus() == "editing") return $helpers->json(array("status" => $error));
                break;*/
        }
        if ($page->getCode() == $code) {
            $page->setStatus($status);
            $em->persist($page);
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
     * @ApiDoc(
     *    section = "Page",
     *    description="Get Page By Slug",
     *    requirements={
     *      {"name"="slug",       "dataType"="string", "required"=true, "description"="Slug page"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function getPageSlugAction(Request $request)
    {
        $slug = $request->get('slug');

        $helpers = $this->get('app.helpers');
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('BackendBundle:Page')->findOneBy(array(
            'slug' => $slug
        ));

        if ($page != null) {
            $data = array(
                "status" => "success",
                "data" => $helpers->jsonObjeto($page, $flag = "page"),
            );

            return $helpers->json($data);
        } else {
            $msg = 'Page Slug not found in DB';
            $data = $helpers->responseData($code = 404, $msg);
            $response = $helpers->responseHeaders($code = 404, $data);
        }

        return $response;

    }

    /**
     * @ApiDoc(
     *    section = "Page",
     *    description="Se debe EJECUTAR 2 VECES, 1 con value=1caracter y la 2a con value=VACIO",
     *    requirements={
     *      {"name"="ids",       "dataType"="string", "required"=true, "description"="x,y,z"},
     *     {"name"="type",      "dataType"="string", "required"=true, "description"="1=Concatenate, 2=UNConcatenate"},
     *     {"name"="value",       "dataType"="string", "required"=true, "description"="value"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function updateMassPageAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $helpers = $this->get('app.helpers');

        $lista = $request->get('ids');
        $value = $request->get('value');
        $type = $request->get('type');

        $ids_array = explode(",",$lista);

        $l_reporte="";
        $elvalor = "";

        $arr_estado = array();

        foreach ($ids_array as $id){
            $page = $em->getRepository('BackendBundle:Page')->find($id);

            $l_title = $page->getTitle();

            if($type==2){
                $elvalor=substr($l_title,0, strlen($l_title)-1);
            }
            else{
                $elvalor= $l_title.$value;

            }

            $page->setTitle($elvalor);




            $em->persist($page);


            array_push($arr_estado, $elvalor . "/" . $id );

        }

        $em->flush();

        $data = array(
            "status" => "success",
            "msg" => $arr_estado
        );

        return $helpers->json($data);



    }
    /**
     * @ApiDoc(
     *    section = "Page",
     *    description="Update field on Page",
     *    requirements={
     *      {"name"="id",       "dataType"="string", "required"=true, "description"="Id page"},
     *      {"name"="field",       "dataType"="string", "required"=true, "description"="field for example  ('created_at' | 'updated_at' | 'published_at' | 'title' | 'status' | 'slug' | 'rss' | 'html' | 'html_serialized' | 'creator' |'element_html' | 'elementHtmlSerialized' | 'content' | 'seo' | 'social' | 'slug_redirect' | author)"},
     *      {"name"="value",       "dataType"="string", "required"=true, "description"="value"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function updateFieldPageAction(Request $request, $id = null)
    {
        $helpers = $this->get("app.helpers");

        $field = $request->get('field');
        $value = $request->get('value');

        //Validacion de 'page_type'
        $fields_array = array('created_at', 'updated_at', 'published_at', 'title', 'status', 'slug', 'rss', 'html', 'html_serialized', 'creator', 'element_html', 'elementHtmlSerialized', 'content', 'seo', 'social', 'slug_redirect', 'author', 'category', 'categories');

        if (!in_array($field, $fields_array)) {
            $data = $helpers->responseData($code = 400, "Wrong field page.");
            $response = $helpers->responseHeaders($code = 400, $data);
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('BackendBundle:Page')->find($id);

        if ($page != null) {
            switch ($field) {
                case 'title':
                    $page->setTitle($value);
                    break;
                case 'slug':
                    $page->setSlug($value);
                    break;
                case 'created_at':
                    $page->setCreatedAt(new \DateTime($value));
                    break;
                case 'updated_at':
                    $page->setUpdatedAt(new \DateTime($value));
                    break;
                case 'published_at':
                    $page->setPublishedAt(new \DateTime($value));
                    break;
                case 'status':
                    $page->setStatus($value);
                    break;
                case 'rss':
                    $page->setRss(json_decode($value));
                    break;
                case 'html':
                    $page->setHtml($value);
                    break;
                case 'html_serialized':
                    $page->setHtmlSerialize(json_decode($value));
                    break;
                case 'creator':
                    $page->setCreator($em->getRepository('BackendBundle:WfUser')->find($value));
                    break;
                case 'element_html':
                    $page->setElementHtml($value);
                    break;
                case 'elementHtmlSerialized':
                    $page->setElementHtmlSerialized(json_decode($value));
                    break;
                case 'content':
                    $page->setContent($value);
                    break;
                case 'seo':
                    $page->setSeo(json_decode($value));
                    break;
                case 'social':
                    $page->setSocial(json_decode($value));
                    break;
                case 'slug_redirect':
                    $page->setSlugRedirect($value);
                    break;
                case 'author':
                    //Si 'author' no es null, remuevo datos relacionales
                    if ($value != null) {
                        $authors_db = $page->getAuthor();
                        foreach ($authors_db as $val) {
                            $page->removeAuthor($val);
                        }
                    }
                    if ($value != null) {
                        $authors_json = json_decode($value);

                        foreach ($authors_json as $key => $value) {
                            //Si el Id es integer, agrego a tabla relacional
                            if (gettype($value) == 'integer') {
                                $author_db = $em->getRepository('BackendBundle:Author')->find($value);
                                if ($author_db != null) {
                                    $page->addAuthor($author_db);
                                } else {
                                    $data = $helpers->responseData($code = 400, "Authors not found in DB!");
                                    $response = $helpers->responseHeaders($code = 400, $data);
                                    return $response;
                                }

                            }
                        }
                    }
                    break;
                case 'category':
                    $categories_db = $em->getRepository('BackendBundle:Category')->find($value);
                    if ($categories_db) {
                        $page->setCategoryId($categories_db);
                    } else {
                        $data = $helpers->responseData($code = 400, "Category not found in DB!");
                        $response = $helpers->responseHeaders($code = 400, $data);
                        return $response;
                    }
                    break;
                case 'categories':
                    if ($value != null) {
                        //Remuevo datos relacionales, si no es null
                        $categories_db = $page->getCategory();
                        foreach ($categories_db as $val) {
                            $page->removeCategory($val);
                        }
                        //Agrego datos relacionales
                        $categories_json = json_decode($value);
                        foreach ($categories_json as $key => $mydata) {
                            $category = $em->getRepository('BackendBundle:Category')->find($mydata);
                            if ($category != null) {
                                $page->addCategory($category);
                            } else {
                                $data = $helpers->responseData($code = 400, "Category not found in DB!");
                                $response = $helpers->responseHeaders($code = 400, $data);
                                return $response;
                            }
                        }
                    }
                    break;
            }
            $em->persist($page);
            $em->flush();

            $data = array(
                "status" => "success",
                "msg" => "update success page"
            );

            return $helpers->json($data);
        } else {
            $msg = 'Page Version not found in DB';
            $data = $helpers->responseData($code = 404, $msg);
            $response = $helpers->responseHeaders($code = 404, $data);

            return $response;
        }
    }

    /**
     * @ApiDoc(
     *    section = "Page",
     *    description="Baja una nota Legacy(cambia status = 'default')",
     *    requirements={
     *      {"name"="slug", "dataType"="string", "required"=true, "description"="Slug page legacy"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function bajarNotaLegacyAction(Request $request)
    {
        $slug = $request->get('slug');
        $helpers = $this->get('app.helpers');

        $users = $this->get('app.users');
        $user_rolls = $users->getUserRolls();

        $busqueda_role = array_search('ROLE_SUPER_ADMIN', $user_rolls);

        // --Only users with 'ROLE_SUPER_ADMIN' can perform this function
        if($busqueda_role === false){

            $data = $helpers->responseData(401, "No tienes acceso para este modulo");
            $response = $helpers->responseHeaders(401, $data);

        }else{
            $find_key = '.html';
            $pos = strpos($slug, $find_key);

            if ($pos !== false) {
                $slug = str_replace('.html', '', $slug);
            }

            $em2 = $this->getDoctrine()->getManager('efOld');
            $note = $em2->getRepository('XalokBundle:Page')->findOneBy(array('slug' => $slug));

            if ($note) {
                $note->setStatus('default');
                $em2->persist($note);
                $em2->flush();

                $purga = $helpers->ejecutaPurgaTools(  $slug );

                $data = $helpers->responseData(200, "Success.");
                $data["Resultado Purga"] = $purga;

                $response = $helpers->responseHeaders(200, $data);
            } else {
                $data = $helpers->responseData(404, "Page not found in DB");
                $response = $helpers->responseHeaders(404, $data);
            }
        }

        return $response;

    }

    private function _cache($url, $response)
    {
        $hash = md5(serialize($url));


        $rutacachetv = $this->container->getParameter('rutacachetv');

        //$response = json_encode($response);


        if ($this->container->getParameter("kernel.environment") == "prod") {

            $file = $this->get('kernel')->getRootDir() . "/../" . $rutacachetv . '/' . $hash . '.cache';

        } elseif ($this->container->getParameter("kernel.environment") == "public") {
            $file = $this->get('kernel')->getRootDir() . "/../../" . '/' . $hash . '.cache';
        }

        if (file_exists($file)) {
            unlink($file);
        }

        return file_put_contents($file, $response->getContent());

    }

    private function setMyRequest($type, $subtype, $json, $page, $size, $public, $max_rows)
    {


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

