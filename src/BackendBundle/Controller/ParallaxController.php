<?php

namespace BackendBundle\Controller;

use BackendBundle\Entity\Parallax;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * Parallax controller.
 *
 */
class ParallaxController extends Controller
{
    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "Parallax",
     *     description="Regresa el contenido de la tabla Parallax",
     *     requirements={
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function indexAction()
    {
        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        $parallaxes = $em->getRepository('BackendBundle:Parallax')->findAll();

        foreach ($parallaxes as $parallax){
           $array = array(
               "id" => $parallax->getId(),
               "structure" => (array) json_decode($parallax->getStructure(), true),
               "updated" => $parallax->getUpdatedAt()
           );
        }


        return $helpers->json($array);

    }

    /**
     * Creates a new parallax entity.
     *
     */
    public function newAction(Request $request)
    {
        $parallax = new Parallax();
        $form = $this->createForm('BackendBundle\Form\ParallaxType', $parallax);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($parallax);
            $em->flush();

            return $this->redirectToRoute('parallax_show', array('id' => $parallax->getId()));
        }

        return $this->render('parallax/new.html.twig', array(
            'parallax' => $parallax,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a parallax entity.
     *
     */
    public function showAction(Parallax $parallax)
    {
        $deleteForm = $this->createDeleteForm($parallax);

        return $this->render('parallax/show.html.twig', array(
            'parallax' => $parallax,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "Parallax",
     *     description="Actualiza el contenido de la tabla Parallax",
     *     requirements={
     *      {"name"="structure", "dataType"="array", "required"=true, "description"="slug"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function editAction(Request $request)
    {
        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();
        $structure = $request->get('structure');

        $parallax = $em->getRepository('BackendBundle:Parallax')->find(1); //Solo existirá un id

        $parallax->setStructure($structure);
        $parallax->setUpdatedAt(new \DateTime());

        $em->persist($parallax);
        $flush = $em->flush();

        if($flush==NULL){
            $data = $helpers->responseData( 200, "success");
            $response = $helpers->responseHeaders( 200, $data);
        }else{
            $data = $helpers->responseData( 400, "No se pudo actualizar la lista de Parallax");
            $response = $helpers->responseHeaders( 400, $data);
        }


        return $response;


    }

    /**
     * Deletes a parallax entity.
     *
     */
    public function deleteAction(Request $request, Parallax $parallax)
    {
        $form = $this->createDeleteForm($parallax);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($parallax);
            $em->flush();
        }

        return $this->redirectToRoute('parallax_index');
    }

    /**
     * Creates a form to delete a parallax entity.
     *
     * @param Parallax $parallax The parallax entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Parallax $parallax)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('parallax_delete', array('id' => $parallax->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
