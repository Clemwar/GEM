<?php


namespace App\Controller;


use App\Repository\AteliersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function home()
    {
        return $this->render('/pages/home.html.twig',[
            'current_menu' => 'home'
        ]);
    }

    /**
     * @Route("/pwup", name="admin")
     * @param AteliersRepository $ateliersRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function admin(AteliersRepository $ateliersRepository)
    {
        $ateliers = $ateliersRepository->getAteliers();

        return $this->render('/pwup/ateliers.html.twig', [
            'ateliers' => $ateliers,
            'current_menu' => 'admin',
            'admin_menu' => 'atelier'
        ]);
    }

    /**
     * @Route("/pwup/events", name="admin_events")
     * @param AteliersRepository $ateliersRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminEvents(AteliersRepository$ateliersRepository)
    {
        $events = $ateliersRepository->getEvents();

        return $this->render('/pwup/events.html.twig', [
            'events' => $events,
            'current_menu' => 'admin',
            'admin_menu' => 'events'
        ]);
    }
}