<?php
/**
 * Created by PhpStorm.
 * User: jmorquecho
 * Date: 28/12/17
 * Time: 11:31
 */

namespace BackendBundle\EventListener;

use BackendBundle\Entity\Author;
use BackendBundle\Entity\Category;
use BackendBundle\Entity\Columna;
use BackendBundle\Entity\Tag;
use BackendBundle\Entity\Blog;
use BackendBundle\Entity\Portada;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\ElasticaBundle\Doctrine\Listener as BaseListener;

class CustomElasticaListener extends BaseListener
{
    protected $objectPersister;
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $class = get_class($args->getEntity());

        $ip_domain = $this->container->getParameter('ip_domain');

        switch ($class) {
            case Author::class:
                if ($args->getEntity() instanceof Author) {
                    $entity = $args->getObject();

                    $url = "/api/search/typed/?_format=json&json={\"search\":\"*\",\"authorslug\":\"" . $entity->getSlug() . "\"}&type=page&page=1&size=10000&max_rows=10000";
                    $curl_service = $this->getData($ip_domain . $url);

                    $array = json_decode($curl_service, true);

                    if ($array['data']) {
                        foreach ($array['data'][1] as $key => $item) {
                            $em = $args->getEntityManager();
                            $note = $em->getRepository("BackendBundle:Page")->find($item['_source']['id']);

                            $objectPersister = $this->container->get('fos_elastica.object_persister.efredisenio.page');
                            $objectPersister->replaceOne($note);
                        }
                    }

                }
                break;
            case Columna::class:
                if ($args->getEntity() instanceof Columna) {
                    $entity = $args->getObject();

                    $url = "/api/search/typed/?_format=json&json={\"search\":\"*\",\"columnslug\":\"" . $entity->getSlug() . "\"}&type=page&page=1&size=10000&max_rows=10000";
                    $curl_service = $this->getData($ip_domain . $url);

                    $array = json_decode($curl_service, true);

                    if ($array['data']) {
                        foreach ($array['data'][1] as $key => $item) {
                            $em = $args->getEntityManager();
                            $note = $em->getRepository("BackendBundle:Page")->find($item['_source']['id']);

                            $objectPersister = $this->container->get('fos_elastica.object_persister.efredisenio.page');
                            $objectPersister->replaceOne($note);
                        }
                    }

                }
                break;
            case Tag::class:
                if ($args->getEntity() instanceof Tag) {
                    $entity = $args->getObject();

                    $url = "/api/search/typed/?_format=json&json={\"search\":\"*\",\"tag\":\"" . $entity->getId() . "\"}&type=page&page=1&size=10000&max_rows=10000";
                    $curl_service = $this->getData($ip_domain . $url);

                    $array = json_decode($curl_service, true);

                    if ($array['data']) {
                        foreach ($array['data'][1] as $key => $item) {
                            $em = $args->getEntityManager();
                            $note = $em->getRepository("BackendBundle:Page")->find($item['_source']['id']);

                            $objectPersister = $this->container->get('fos_elastica.object_persister.efredisenio.page');
                            $objectPersister->replaceOne($note);
                        }
                    }

                }
                break;
            case Category::class:
                //Update index on subcategories Page
                if ($args->getEntity() instanceof Category) {
                    $entity = $args->getObject();

                    $url = "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":\"" . $entity->getId() . "\"}&type=page&page=1&size=10000&max_rows=10000";
                    $curl_service = $this->getData($ip_domain . $url);

                    $array = json_decode($curl_service, true);

                    if ($array['data']) {
                        foreach ($array['data'][1] as $key => $item) {
                            $em = $args->getEntityManager();
                            $note = $em->getRepository("BackendBundle:Page")->find($item['_source']['id']);

                            $objectPersister = $this->container->get('fos_elastica.object_persister.efredisenio.page');
                            $objectPersister->replaceOne($note);
                        }
                    }

                }
                break;
            case Blog::class:
                if ($args->getEntity() instanceof Blog) {
                    $entity = $args->getObject();

                    $url = "/api/search/typed/?_format=json&json={\"search\":\"*\",\"blog\":\"" . $entity->getId() . "\"}&type=page&page=1&size=10000&max_rows=10000";
                    $curl_service = $this->getData($ip_domain . $url);

                    $array = json_decode($curl_service, true);

                    if ($array['data']) {
                        foreach ($array['data'][1] as $key => $item) {
                            $em = $args->getEntityManager();
                            $note = $em->getRepository("BackendBundle:Page")->find($item['_source']['id']);

                            $objectPersister = $this->container->get('fos_elastica.object_persister.efredisenio.page');
                            $objectPersister->replaceOne($note);
                        }
                    }
                }
                break;
            case Portada::class:
                if ($args->getEntity() instanceof Portada) {
                    $entity = $args->getObject();
                    $em     = $args->getEntityManager();
                    if( $entity->getStatus() == "published" ){

                        $folds  = $em->getRepository("BackendBundle:PortadaFolds")->findBy(
                            array(
                                'idportada' => $entity->getId()
                            )
                        );
                        foreach( $folds as $fold ){
                            $objectPersister = $this->container->get('fos_elastica.object_persister.efredisenio.portadafolds');
                            $objectPersister->replaceOne($fold);
                        }
                    }
                }
                break;
        }

    }

    private function getData($url)
    {
        $jwt_auth = $this->container->get('app.jwt_auth');
        $bearer = $jwt_auth->signup("sadmin", "sadmin", true);

        $headers = ['Authorization: bearer ' . $bearer];

        /*$ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;*/

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $content = curl_exec($ch);
        curl_close($ch);

        return $content;

    }
}