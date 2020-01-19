<?php


namespace App\Controller;


use App\Entity\Contact;
use App\Form\ContactType;
use App\Notification\ContactNotification;
use App\Repository\AteliersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @Route("/pages/contact", name="contact")
     * @param Request $request
     * @param ContactNotification $notification
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ContactPage(Request $request, ContactNotification $notification)
    {
        $contact = new Contact();

        $form = $this->createForm(ContactType::class, $contact);

        if ($request->isMethod('POST'))
        {
            $form->handleRequest($request);

            if ($form->isValid())
            {
                $notification->notify($contact);
                $this->addFlash('success', 'Votre message a bien été envoyé');
                return $this->redirectToRoute('contact', [
                    '_fragment' => 'form'
                ]);
            }
        }

        return $this->render('/pages/contact.html.twig', [
            'form' => $form->createView(),
            'current_menu' => 'contact'
        ]);
    }
}