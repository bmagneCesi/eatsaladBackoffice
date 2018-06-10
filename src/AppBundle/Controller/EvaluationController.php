<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use AppBundle\Entity\Evaluation;
use Symfony\Component\Filesystem\Filesystem;


class EvaluationController extends FOSRestController
{
    /**
     * @Rest\Get("/rest/evaluation")
     */
    public function getAction()
    {
        $restresult = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->findAll();
        if ($restresult === null) {
            return new View("there are no evaluations", Response::HTTP_NOT_FOUND);
        }
        return $restresult;
    }

    /**
     * @Rest\Get("/rest/evaluation/{id}")
     */
    public function idAction($id)
    {
        $singleresult = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($id);
        if ($singleresult === null) {
            return new View("evaluation not found", Response::HTTP_NOT_FOUND);
        }
        return $singleresult;
    }

    /**
     * @Rest\Get("/rest/evaluations-by-restaurant/{id_restaurant}")
     */
    public function getByRestaurantAction($id_restaurant)
    {
        $restaurant = $this->getDoctrine()->getRepository('AppBundle:Restaurant')->find($id_restaurant);
        if ($restaurant === null) {
            return new View("evaluation not found", Response::HTTP_NOT_FOUND);
        }
        return $restaurant->getEvaluations();
    }

    /**
    * @Rest\Post("/rest/evaluation")
    */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $evaluation = new Evaluation();
        $date = new \Datetime('NOW');
        $id_restaurant = $request->get('id_restaurant');
        $subcategoriesDone = $request->get('subcategories_done');
        $restaurant = $em->getRepository('AppBundle:Restaurant')->find($id_restaurant);
        if(empty($date) || empty($restaurant))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $evaluation->setDate($date);
        $evaluation->setTemp(true);
        $evaluation->setSubcategoriesDone($subcategoriesDone);
        $restaurant->addEvaluation($evaluation);
        $em->persist($restaurant);
        $em->persist($evaluation);
        $em->flush();
        return new Response($evaluation->getId(), Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/rest/evaluation/subcategory-done")
     */
    public function addSubcategoriesDone(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id_evaluation = $request->request->get('id_evaluation');
        $subcategory_done = $request->request->get('subcategory_done');
        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($id_evaluation);
        if(empty($subcategory_done))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $alreadyDone = $evaluation->getSubcategoriesDone();
        $alreadyDone[] = $subcategory_done;
        $evaluation->setSubcategoriesDone($alreadyDone);
        $em->persist($evaluation);
        $em->flush();
        return new Response($evaluation, Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/rest/evaluation/refusal")
     */
    public function setRefusal(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $refusal = $request->request->get('refusal');
        $id_evaluation = $request->request->get('id_evaluation');
        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($id_evaluation);
        if(empty($subcategory_done))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $evaluation->setRefusal($refusal);
        $em->persist($evaluation);
        $em->flush();
        return new Response($evaluation, Response::HTTP_OK);
    }

    /**
     * @Rest\Delete("/rest/evaluation/{id}")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $evaluation = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($id);
        if (empty($evaluation)) {
            return new View("Evaluation not found", Response::HTTP_NOT_FOUND);
        }
        else {
            $fileSystem = new Filesystem();
            $restPath = $this->container->getParameter('photos_directory');
            $evaluationAnswers = $evaluation->getEvaluationAnswers();
            foreach ($evaluationAnswers as $index => $evaluationAnswer) {
                $photos = $evaluationAnswer->getPhotos();
                foreach ($photos as $index => $photo) {
                    $fileSystem->remove($restPath.'/'.$evaluation->getId());
                }
            }
            $em->remove($evaluation);
            $em->flush();
        }
        return new View("Deleted successfully", Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/rest/evaluation/report")
     */
    public function createReport(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id_evaluation = $request->request->get('id_evaluation');
        $controllerName = $request->request->get('controllerName');
        $controllerSignature = $request->request->get('controllerSignature');
        $controllerFranchised = $request->request->get('controllerFranchised');
        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($id_evaluation);
        if(empty($controllerFranchised) || empty($controllerSignature || $controllerName))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $evaluation->setControllerName($controllerName);
        $evaluation->setControllerSignature();
        $evaluation->setTemp(false);
        $em->persist($evaluation);
        $em->flush();

        return new Response($evaluation, Response::HTTP_OK);
    }
}
