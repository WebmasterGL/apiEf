<?php
/**
 * Created by PhpStorm.
 * User: jorgeserrano
 * Date: 18/10/17
 * Time: 11:07
 */

namespace AppBundle\Services;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

use BackendBundle\Entity\PageVersion;


class PageClone
{
    private $manager;
    private $tokenStorage;
    public $helpers;

    public function __construct($helpers, TokenStorage $tokenStorage, $manager)
    {
        $this->helpers = $helpers;
        $this->tokenStorage = $tokenStorage;
        $this->manager = $manager;

    }

    public function clonePage($id = null, $from, $version_page)
    {
        $helpers = $this->helpers;
        $em = $this->manager;

        //Get current logged user
        $user = $this->tokenStorage->getToken()->getUser();
        $user_current_id = $user->getId();

        //Get last version on page_version
        $latestPageVersion = $this->manager->getRepository("BackendBundle:PageVersion")->latestVersion($id);

        //Si 'version_page' es diferente de null, obtengo dicha version de PageVersion
        if ($version_page != null) {
            $page = $em->getRepository('BackendBundle:PageVersion')->getPageVersion($id, $version_page);

            //De lo contrario la obtengo de Page
        } else {
            $page = $em->getRepository("BackendBundle:Page")->find($id);
        }

        if ($page != null) {

            //Actualizo en Page, el campo "editing_by"
            if ($version_page == null) {
                $user = $em->getRepository("BackendBundle:WfUser")->find($user_current_id);
                $page->setEditingBy($user);
                $em->persist($page);
                $em->flush();
            }

            $page_version = new PageVersion();

            //Si es una page_version, entonces..
            if ($version_page != null) {
                $page_version->setPage($page->getPage());
            } else {
                $page_version->setPage($page);
            }

            $page_version->setCreatedAt(new \DateTime());
            $page_version->setUpdatedAt(new \DateTime());

            if ($latestPageVersion != null) {
                $page_version->setVersionNo($latestPageVersion + 1);
            } else {
                $page_version->setVersionNo(1);
            }
            $category = $em->getRepository('BackendBundle:Category')->find($page->getCategoryId());
            $page_version->setCategoryId($category);

            // Unicamente cuando la nota fue creada e inmediatamente publicada
            if($from == 'publish' && $version_page == null){
                $page_version->setPublisher( $em->getRepository("BackendBundle:WfUser")->find($user_current_id));
                $page_version->setPublishedAt(new \DateTime());
            }
            //$page_version->setPublisher($page->getPublisher());
            if ($page->getMainImage() != null) {
                $image = $em->getRepository('BackendBundle:Image')->find($page->getMainImage());
                $page_version->setMainImage($image);
            }
            //Si es una page_version, entonces..
            if ($version_page != null) {
                $page_version->setCreatedAtPage($page->getCreatedAtPage());
                $page_version->setUpdatedAtPage($page->getUpdatedAtPage());
                $page_version->setPublishedAtPage($page->getPublishedAtPage());
                $page_version->setNextPublishedAtPage($page->getNextPublishedAtPage());
            } else {
                $page_version->setCreatedAtPage($page->getCreatedAt());
                $page_version->setUpdatedAtPage($page->getUpdatedAt());
                $page_version->setPublishedAtPage($page->getPublishedAt());
                $page_version->setNextPublishedAtPage($page->getNextPublishedAt());
            }

            $page_version->setTitle($page->getTitle());

            //Si el "from" es publish, significa que viene de la Tabla "Page" y que se acaba de publicar la nota
            //Si el "from" es scheduled, significa que viene de la Tabla "Page" y que se acaba de programar la nota
            switch ($from) {
                case "publish":
                    $page_version->setStatus($page->getStatus());
                    break;
                case "scheduled":
                    $page_version->setStatus($page->getStatus());
                    break;
                case "clone":
                    $page_version->setStatus("editing");
                    break;
            }

            $page_version->setSlug($page->getSlug());
            $page_version->setPageType($page->getPageType());
            $page_version->setPortalId($page->getPortalId());
            $page_version->setCode($page->getCode());
            $page_version->setTemplate($page->getTemplate());
            $page_version->setSettings($page->getSettings());
            $page_version->setModules($page->getModules());
            $page_version->setSeo($page->getSeo());
            $page_version->setRelated($page->getRelated());
            $page_version->setShortDescription($page->getShortDescription());
            $page_version->setContent($page->getContent());
            $page_version->setCreator($page->getCreator());
            $page_version->setSocial($page->getSocial());
            $page_version->setHtml($page->getHtml());
            //Si es una page_version, entonces..
            if ($version_page != null) {
                $page_version->setHtmlSerialize($page->getHtmlSerialize());
            } else {
                $page_version->setHtmlSerialize(json_decode($page->getHtmlSerialize()));
            }
            //Si es una page_version, entonces..
            if ($version_page != null) {
                $page_version->setElementHtmlSerialized($page->getElementHtmlSerialized());
            } else {
                $page_version->setElementHtmlSerialized(json_decode($page->getElementHtmlSerialized()));
            }

            $page_version->setFlag($page->getFlag());
            $page_version->setNewslatter($page->getNewslatter());
            $page_version->setBullets($page->getBullets());
            $page_version->setMostViewed($page->getMostViewed());
            $page_version->setRss($page->getRss());
            $page_version->setColumna($page->getColumna());
            //$page_version->setEditingBy($page->getEditingBy());
            $page_version->setEditingBy( $em->getRepository("BackendBundle:WfUser")->find($user_current_id) );
            $page_version->setElementHtml($page->getElementHtml());
            $page_version->setBlog($page->getBlog());
            $page_version->setIsBreaking($page->getIsBreaking());
            $page_version->setSlugRedirect($page->getSlugRedirect());

            //Si es una page_version, entonces..
            if ($version_page != null) {
                //Obtengo el campo many_to_many
                $many_to_many = $page->getFieldsManytoMany();

                $subcategories_array = array();
                $authors_array = array();
                $images_array = array();
                $tags_array = array();

                //Recorro el campo, para obtener sus diferentes objetos del ORM
                foreach ($many_to_many as $key => $field) {
                    if ($key == 'categories') {
                        foreach ($field as $value) {
                            array_push($subcategories_array, $value);
                        }
                    } elseif ($key == 'authors') {
                        foreach ($field as $value) {
                            array_push($authors_array, $value);
                        }
                    } elseif ($key == 'images') {
                        foreach ($field as $value) {
                            array_push($images_array, $value);
                        }
                    } elseif ($key == 'tags') {
                        foreach ($field as $value) {
                            array_push($tags_array, $value);
                        }
                    }
                }

                $fields_many_to_many = array(
                    'categories' => $subcategories_array,
                    'authors' => $authors_array,
                    'images' => $images_array,
                    'tags' => $tags_array
                );

                $page_version->setFieldsManytoMany($fields_many_to_many);

            } else {
                $categories = $page->getCategory();
                $authors = $page->getAuthor();
                $images = $page->getImage();
                $tags = $page->getTag();

                $categories_array = array();
                $authors_array = array();
                $images_array = array();
                $tags_array = array();

                foreach ($categories as $v) {
                    array_push($categories_array, $v->getId());
                }
                foreach ($authors as $v) {
                    array_push($authors_array, $v->getId());
                }
                //Si la nota original no es breaking news
                if($page->getIsBreaking() != 1){
                    foreach ($images as $v) {
                        array_push($images_array, $v->getId());
                    }
                    foreach ($tags as $v) {
                        array_push($tags_array, $v->getId());
                    }
                }

                $fields_many_to_many = array(
                    'categories' => $categories_array,
                    'authors' => $authors_array,
                    'images' => $images_array,
                    'tags' => $tags_array
                );

                $page_version->setFieldsManytoMany($fields_many_to_many);
            }

            $em->persist($page_version);
            $em->flush();

            //Si el from es 'scheduled', significa que proviene de una programacion de Page y no planchare, el status de la copia carbon
            if ($from != 'scheduled') {
                if ($page->getStatus() == 'scheduled') {
                    $page->setStatus('trash');
                    $em->persist($page);
                    $em->flush();
                }
            }

            if ($from == 'publish' || $from == 'scheduled') {
                return true;
            } else {
                $msg = 'Clone page.';
                $data = $helpers->responseData($code = 200, $msg);
                $data['from'] = 'page_version';

                $response = $helpers->responseHeaders($code = 200, $data);
            }

        } else {
            if ($from == 'publish' || $from == 'scheduled') {
                return false;
            } else {
                $msg = 'Page not found in DB';
                $data = $helpers->responseData($code = 404, $msg);
                $response = $helpers->responseHeaders($code = 404, $data);
            }

        }

        return $response;
    }
}