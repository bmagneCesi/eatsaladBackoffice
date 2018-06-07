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
     * @Rest\Get("/rest/evaluations-by-restaurant/{id}")
     */
    public function getByRestaurantAction($id)
    {
        $singleresult = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($id);
        if ($singleresult === null) {
            return new View("evaluation not found", Response::HTTP_NOT_FOUND);
        }
        return $singleresult;
    }

    /**
    * @Rest\Post("/rest/evaluation")
    */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = new Evaluation();
        $date = new \Datetime('NOW');
        $id_restaurant = $request->get('id_restaurant');
        $subcategoriesDone = $request->get('subcategories_done');

        $restaurant = $em->getRepository('AppBundle:Restaurant')->find($id_restaurant);
        if(empty($date) || empty($restaurant))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $data->setDate($date);
        $data->setTemp(true);
        $data->setSubcategoriesDone($subcategoriesDone);
        $data->setRestaurant($restaurant);
        $em->persist($data);
        $em->flush();
        return new Response($data->getId(), Response::HTTP_OK);
    }
}