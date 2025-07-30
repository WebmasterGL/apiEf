<?php

namespace BackendBundle\Controller;

use Doctrine\DBAL\VersionAwarePlatformDriver;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\Author;
use Gedmo\Sluggable\Util\Urlizer;


class AuthorController extends SearchController
{

    private function slugValidate($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('BackendBundle:Author')->findOneBySlug($slug);
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
     *  section = "Author",
     *  description="Listado de Autores",
     *  requirements={
     *      {"name"="page", "dataType"="int", "required"="true", "default"="1", "description"="numero de pagina, si se omite es 1"},
     *      {"name"="size", "dataType"="int", "required"="true", "default"="10", "description"="numero de items, si se omite es 10"}
     *   },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function getAuthorsAction(Request $request, $idauthor)
    {

        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        if ($idauthor == NULL || $idauthor == '{idauthor}') //2o caso para aceptar el parametro que le pone swagger
        {
            $page = $request->get("page", 1);
            $size = $request->get("size", 10);

            $authors = $em->getRepository("BackendBundle:Author")->findby(array(
                'active' => 1
            ));
            $paginator = $this->get("knp_paginator");
            $items_per_page = $size;

            $pagination = $paginator->paginate($authors, $page, $items_per_page);
            $total_items_count = $pagination->getTotalItemCount();

            $list = true;
            $data = $helpers->responseData(200, $msg = null, $list, $pagination, $total_items_count, $page, $items_per_page);
        } else {
            $author = $em->getRepository('BackendBundle:Author')->findBy(array(
                'id' => $idauthor
            ));
            if (count($author) != 0) {
                $data = array(
                    "status" => "success",
                    "data" => $author
                );
            } else {
                $msg = "Author not found";
                $data = $helpers->responseData(404, $msg);
                $response = $helpers->responseHeaders(404, $data);

                return $response;
            }
        }


        return $helpers->json($data);

    }

    /**
     *  Para acceder a este metodo, se require autorizacion(token)
     *
     * @ApiDoc(
     *     section = "Author",
     *     description="Crea un nuevo Autor, metodo privado",
     *     requirements={
     *      {"name"="name",         "dataType"="string", "required"=true, "description"="Name"},
     *      {"name"="aPaterno",     "dataType"="string", "required"=true, "description"="Apellido Paterno"},
     *      {"name"="aMaterno",     "dataType"="string", "required"=true, "description"="Apellido Materno"},
     *      {"name"="email",        "dataType"="string", "required"=true, "description"="Email"},
     *      {"name"="bio",          "dataType"="string", "required"=true, "description"="Bio"},
     *      {"name"="image",        "dataType"="string", "required"=true, "description"="Image"},
     *      {"name"="imageSmall",   "dataType"="string", "required"=true, "description"="Image small"},
     *      {"name"="twitter",      "dataType"="string", "required"=false, "description"="Twitter"},
     *      {"name"="facebook",     "dataType"="string", "required"=false, "description"="Facebook pagegit "},
     *      {"name"="linkedin",     "dataType"="string", "required"=false, "description"="LinkedIn"},
     *      {"name"="googlePlus",   "dataType"="string", "required"=false, "description"="Google Plus"},
     *      {"name"="sexo",         "dataType"="string", "required"=false, "description"="Sexo"},
     *      {"name"="active",       "dataType"="boolean", "required"=false, "description"="Active"},
     *      {"name"="corresponsal", "dataType"="boolean", "required"=false, "description"="Corresponsal"},
     *      {"name"="hiddenName",   "dataType"="boolean", "required"=false, "description"="Hidden Name"},
     *      {"name"="rss",          "dataType"="boolean", "required"=false, "description"="RSS"},
     *      {"name"="slug",         "dataType"="boolean", "required"=false, "description"="Slug"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     *
     */
    public function newAuthorAction(Request $request)
    {
        $helpers = $this->get("app.helpers");

        $name = $request->get('name');
        $aPaterno = $request->get('aPaterno');
        $aMaterno = $request->get('aMaterno');
        $email = $request->get('email');
        $bio = $request->get('bio');
        $twitter = $request->get('twitter');
        $facebook = $request->get('facebook');
        $linkedin = $request->get('linkedin');
        $googlePlus = $request->get('googlePlus');
        $sexo = $request->get('sexo');
        $active = $request->get('active');
        $corresponsal = $request->get('corresponsal');
        $hiddenName = $request->get('hiddenName');
        $rss = $request->get('rss');
        $image = $request->get('image');
        $imageSmall = $request->get('imageSmall');
        $slug = $request->get('slug');

        $paths = array();
        $em = $this->getDoctrine()->getManager();
        $author = new Author();
        $img = $em->getRepository("BackendBundle:Image")->findOneBy(
            array(
                "id" => $image
            )
        );
        $img_small = $em->getRepository("BackendBundle:Image")->findOneBy(
            array(
                "id" => $imageSmall
            )
        );
        $def_img = $em->getRepository('BackendBundle:Image')->findOneBy(                  //select default image
            array(
                "title" => "Foto Default de Autor",
                "description" => "Foto Default de Autor",
                "credito" => "Especial"
            )
        );
        $author->setName($name);
        $author->setAPaterno($aPaterno);
        $author->setAMaterno($aMaterno);
        $author->setEmail($email);
        $author->setBio($bio);
        $author->setTwitter($twitter);
        $author->setFacebook($facebook);
        $author->setLinkedin($linkedin);
        $author->setGooglePlus($googlePlus);
        $author->setSexo($sexo);
        $author->setCreatedAt(new \DateTime());
        $author->setUpdatedAt(new \DateTime());
        if ($slug) {
            $slug_db = $em->getRepository('BackendBundle:Author')->findOneBySlug(Urlizer::urlize($slug));
            if ($slug_db != null) {
                $msg = "Slug exist in DB";
                $data = $helpers->responseData(400, $msg);
                $response = $helpers->responseHeaders(400, $data);
                return $response;
            } else {
                $slug_final = $this->createSlug($slug);
                //var_dump($slug_final);
                $author->setSlug($slug_final['value']);
            }
        }
        if ($active == 'true') {
            $author->setActive(1);
        } else {
            $author->setActive(0);
        }
        if ($rss == 'true') {
            $author->setRss(1);
        } else {
            $author->setRss(0);
        }
        if ($corresponsal == 'true') {
            $author->setCorresponsal(1);
        } else {
            $author->setCorresponsal(0);
        }
        if ($hiddenName == 'true') {
            $author->setHiddenName(1);
        } else {
            $author->setHiddenName(0);
        }
        if (count($img) > 0) {
            $author->setImage($img);
        } else {
            $author->setImage($def_img);
        }

        if (count($img_small) > 0) {
            $author->setImageSmall($img_small);
        } else {
            $author->setImageSmall($def_img);
        }

        $validator = $this->get('validator');
        $errors = $validator->validate($author);

        foreach ($errors as $error) {
            $messages[] = $error->getMessage();
        }
        if (count($errors) > 0) {
            $data = $helpers->responseData(400, $messages);
            $response = $helpers->responseHeaders(400, $data);
        } else {
            $em->persist($author);
            $em->flush();
            $msg = 'Author created!';
            $data = $helpers->responseData(200, $msg);
            $data[] = $paths;
            $response = $helpers->responseHeaders(200, $data);
        }

        return $response;
    }

    /**
     *  Para acceder a este metodo, se require autorizacion(token)
     *
     * @ApiDoc(
     *     section = "Author",
     *     description="Edita Autor, metodo privado",
     *     requirements={
     *      {"name"="id",   "dataType"="string", "required"=true,   "description"="id author"},
     *      {"name"="json", "dataType"="string", "required"=true,   "description"="Json: {name, aPaterno, aMaterno, email,bio,image,imageSmall,twitter,facebook,linkedin,googlePlus,sexo, active, corresponsal, hiddenName, rss}", "default"="{name, aPaterno, aMaterno, email,bio,image,imageSmall,twitter,facebook,linkedin,googlePlus,sexo, active, corresponsal, hiddenName, rss}"},
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     *
     */
    public function editAuthorAction(Request $request, $id)
    {
        $helpers = $this->get("app.helpers");
        $json = $request->get("json", "");
        $params = json_decode($json);

        if (count((array)$params) > 0) {
            $name = (isset($params->name)) ? $params->name : "nsfu";
            $aPaterno = (isset($params->aPaterno)) ? $params->aPaterno : "nsfu";
            $aMaterno = (isset($params->aMaterno)) ? $params->aMaterno : "nsfu";
            $email = (isset($params->email)) ? $params->email : "nsfu";
            $bio = (isset($params->bio)) ? $params->bio : "nsfu";
            $image = (isset($params->image)) ? $params->image : "nsfu";
            $imageSmall = (isset($params->imageSmall)) ? $params->imageSmall : "nsfu";
            $twitter = (isset($params->twitter)) ? $params->twitter : "nsfu";
            $facebook = (isset($params->facebook)) ? $params->facebook : "nsfu";
            $linkedin = (isset($params->linkedin)) ? $params->linkedin : "nsfu";
            $googlePlus = (isset($params->googlePlus)) ? $params->googlePlus : "nsfu";
            $sexo = (isset($params->sexo)) ? $params->sexo : "nsfu";
            $active = (isset($params->active)) ? $params->active : "nsfu";
            $corresponsal = (isset($params->corresponsal)) ? $params->corresponsal : "nsfu";
            $hiddenName = (isset($params->hiddenName)) ? $params->hiddenName : "nsfu";
            $rss = (isset($params->rss)) ? $params->rss : "nsfu";
            //Busco en la BD el autor que vamos a editar, a traves del video_id que agarro de la url
            $em = $this->getDoctrine()->getManager();

            $author = $em->getRepository("BackendBundle:Author")->findOneBy(array(
                "id" => $id
            ));
            $img = $em->getRepository("BackendBundle:Image")->findOneBy(
                array(
                    "id" => $image
                )
            );
            $img_small = $em->getRepository("BackendBundle:Image")->findOneBy(
                array(
                    "id" => $imageSmall
                )
            );
            $def_img = $em->getRepository('BackendBundle:Image')->findOneBy(                  //select default image
                array(
                    "title" => "Foto Default de Autor",
                    "description" => "Foto Default de Autor",
                    "credito" => "Especial"
                )
            );
            if (count($author) == 1) {
                $author->setUpdatedAt(new \DateTime());
                if ($name != "nsfu") {
                    $author->setName($name);
                }
                if ($aPaterno != "nsfu") {
                    $author->setAPaterno($aPaterno);
                }
                if ($aMaterno != "nsfu") {
                    $author->setAMaterno($aMaterno);
                }
                if ($email != "nsfu") {
                    $author->setEmail($email);
                }
                if ($bio != "nsfu") {
                    $author->setBio($bio);
                }
                if ($twitter != "nsfu") {
                    $author->setTwitter($twitter);
                }
                if ($facebook != "nsfu") {
                    $author->setFacebook($facebook);
                }
                if ($linkedin != "nsfu") {
                    $author->setLinkedin($linkedin);
                }
                if ($googlePlus != "nsfu") {
                    $author->setGooglePlus($googlePlus);
                }
                if ($sexo != "nsfu") {
                    $author->setSexo($sexo);
                }
                if ($active !== "nsfu") {
                    if ($active == 'true') {
                        $author->setActive(1);
                    } else {
                        $author->setActive(0);
                    }
                }
                if ($corresponsal !== null) {
                    if ($corresponsal == 'true') {
                        $author->setCorresponsal(1);
                    } else {
                        $author->setCorresponsal(0);
                    }
                }
                if ($rss !== null) {
                    if ($rss == 'true') {
                        $author->setRss(1);
                    } else {
                        $author->setRss(0);
                    }
                }
                if ($hiddenName !== null) {
                    if ($hiddenName == 'true') {
                        $author->setHiddenName(1);
                    } else {
                        $author->setHiddenName(0);
                    }
                }
                if ($image != "nsfu") {
                    if (count($img) > 0) {
                        $author->setImage($img);
                    } else {
                        $author->setImage($def_img);
                    }
                }

                if ($imageSmall != "nsfu") {
                    if (count($img_small) > 0) {
                        $author->setImageSmall($img_small);
                    } else {
                        $author->setImageSmall($def_img);
                    }
                }
                $em->persist($author);
                $em->flush();


                /*
                $notes = $this->broadPublic("page", "", "{\"search\":\"*\",\"author\":\"" . $author->getId() . "\"}" , 1, 10, true);
                var_dump( count( $notes ) );
                die();
                */

                /*var_dump($request->getHttpHost());
                die();*/


                $msg = 'Author updated';
                $data = $helpers->responseData(200, $msg);
                $response = $helpers->responseHeaders(200, $data);
            } else {
                $msg = 'Author don´t exist in DB';
                $data = $helpers->responseData(404, $msg);
                $response = $helpers->responseHeaders(404, $data);
            }
        } else {
            $msg = 'No json data';
            $data = $helpers->responseData(404, $msg);
            $response = $helpers->responseHeaders(404, $data);
        }

        return $response;

    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     *
     * @ApiDoc(
     *     section = "Author",
     *     description="Borra Autor, metodo privado",
     *     requirements={
     *      {"name"="id", "dataType"="string", "required"=true, "description"="id user"},
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     *
     */
    public function deleteAuthorAction(Request $request, $id)
    {
        $helpers = $this->get("app.helpers", null);

        $em = $this->getDoctrine()->getManager();
        $author = $em->getRepository('BackendBundle:Author')->find($id);

        if ($author !== null) {
            $em->remove($author);
            $em->flush();

            $msg = 'Delete author.';
            $data = $helpers->responseData(200, $msg);
            $response = $helpers->responseHeaders(200, $data);
        } else {
            $msg = 'Cannot delete author, don´t exist in DB.';
            $data = $helpers->responseData(404, $msg);
            $response = $helpers->responseHeaders(404, $data);
        }

        return $response;
    }
}
