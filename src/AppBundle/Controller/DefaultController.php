<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/shows", name="shows")
     * @Template()
     */
    public function showsAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:TVShow');
        //On récupère d'abbord toutes les séries
        $shows = $repo->findAll();

        //Ensuite on utilise le bundle knp_paginator 
        //pour les organiser en pages de 6 séries
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $shows,
            $request->query->getInt('page', 1),
            6
        );

        return ['shows' => $pagination];
    }

    /**
     * @Route("/show/{id}", name="show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:TVShow');

        return [
            'show' => $repo->find($id)
        ];        
    }

    /**
     * @Route("/calendar", name="calendar")
     * @Template()
     */
    public function calendarAction()
    {
        $em = $this->getDoctrine()->getManager();

        //Création requete sql
        $query = $em->createQueryBuilder();
        $query  ->select('e')
                ->from('AppBundle:Episode', 'e')
                //on récupère les épisodes ayant une date postérieure à aujourd'hui
                ->where('e.date > CURRENT_TIMESTAMP()')
                //triés de la date la plus proche à la plus lointaine
                ->orderBy('e.date');

        $episodes = $query->getQuery()->getResult();

        return ['episodes' => $episodes];
    }

    /**
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction()
    {
        return [];
    }

    /**
     * @Route("/search", name="search")
     * @Template()
     */
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ($request->getMethod() == "POST") {
            //On récupère la recherche
            $research = $request->request->get('search');
            
            //Création requete sql
            $query = $em->createQueryBuilder();
            $query  ->select('tvShow') //on récupérera les séries
                    ->from('AppBundle:TVShow', 'tvShow')
                    //on regarde le nom et le résumé des séries
                    ->where('tvShow.name LIKE :data OR tvShow.synopsis LIKE :data') 
                    ->setParameter('data', '%'.$research.'%');
            //On récupère le résultat de la recherche
            $tvShows = $query->getQuery()->getResult();
        }
        //On affiche les résultats
        return ['shows' => $tvShows];
    }
}
