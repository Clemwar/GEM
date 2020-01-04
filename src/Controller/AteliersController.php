<?php


namespace App\Controller;


use App\Entity\Ateliers;
use App\Form\AteliersType;
use App\Repository\AteliersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AteliersController extends AbstractController
{
    /**
     * @var AteliersRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * AteliersController constructor.
     * @param AteliersRepository $repository
     * @param EntityManagerInterface $em
     */
    public function __construct(AteliersRepository $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/activites", name="showActivites")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showActivites()
    {
        $ateliers = $this->repository->getAteliers();
        $events = $this->repository->getEvents();

        return $this->render('/pages/activites.html.twig', [
            'ateliers' => $ateliers,
            'events' => $events,
            'current_menu' => 'activites'
        ]);
    }

    /**
     * @Route("/activites/{id}", name="getAtelier")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAtelier($id)
    {
        $atelier = $this->repository->find($id);

        return $this->render('/pages/activite.html.twig', [
            'atelier' => $atelier
        ]);
    }

    /**
     * @Route("/pwup/activites/add/{event}", name="addActivite")
     * @param $event
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAtelier($event, Request $request)
    {
        $atelier = new Ateliers();
        $menu = ($event) ? 'events':'atelier';

        //On construit le formulaire via AteliersType et on lie le formulaire à $atelier
        $form = $this->createForm(AteliersType::class, $atelier);

        //On vérifie que la requête est en POST
        if ($request->isMethod('POST')){
            //On lie la requête au formulaire
            $form->handleRequest($request);

            if ($event)
            {
            $atelier->setEvent(true);
            }
            else
            {
                $atelier->setEvent(false);
            }

            //On vérifie les données envoyées
            if ($form->isValid()){
                //on enregistre l'objet obtenu dans la bdd
               $this->em->persist($atelier);
               $this->em->flush();

               $this->addFlash('success', 'Ajout réussi');

                //On redirige vers la page d'admin
                if (!$event)
                {
                return $this->redirectToRoute('admin');
                }

                return $this->redirectToRoute('admin_events');
            }
        }

        //On crée la vue
        $formView = $form->createView();

        //Le formulaire n'est pas valide ou n'a pas été soumis > on reste sur le formulaire
        return $this->render('/pwup/form.html.twig', [
            'form' => $formView,
            'titre' => 'Ajouter',
            'current_menu' => 'admin',
            'admin_menu' => $menu
        ]);
    }

    /**
     * @Route("/pwup/activite/delete/{id}", name="delAtelier", methods="DELETE")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delAtelier($id, Request $request)
    {

        $atelier = $this->repository->find($id);
        $event = $atelier->getEvent() ? true:false;

        if ($this->isCsrfTokenValid('delete' . $id, $request->get('_token'))) {
            $this->em->remove($atelier);
            $this->em->flush();

            $this->addFlash('success', 'Suppression réussie');
        }

        if ($event)
        {
            return $this->redirectToRoute('admin_events');
        }
        return $this->redirectToRoute('admin');


    }

    /**
     * @Route("/pwup/activite/update/{id}", name="updateAtelier")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAtelier($id, Request $request)
    {
        $atelier = $this->repository->find($id);
        $menu = $atelier->getEvent() ? 'events':'atelier';

        //On construit le formulaire via AteliersType et on lie le formulaire à $atelier
        $form = $this->createForm(AteliersType::class, $atelier);

        //On vérifie que la requête est en POST
        if ($request->isMethod('POST')){
            //On lie la requête au formulaire
            $form->handleRequest($request);

            //On vérifie les données envoyées
            if ($form->isValid()){

                //on enregistre l'objet obtenu dans la bdd
                $this->em->flush();

                $this->addFlash('success', 'Mise à jour réussie');

                //On redirige vers la page d'admin
                if (!$atelier->getEvent())
                {
                    return $this->redirectToRoute('admin');
                }

                return $this->redirectToRoute('admin_events');
            }
        }

        //On crée la vue
        $formView = $form->createView();

        //Le formulaire n'est pas valide ou n'a pas été soumis > on reste sur le formulaire
        return $this->render('/pwup/form.html.twig', [
            'form' => $formView,
            'titre' => 'Modifier',
            'current_menu' => 'admin',
            'admin_menu' => $menu
        ]);
    }

    /**
     * @Route("/pwup/activite/visibility/{id}", name="toggleVisibility")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function toggleVisibility($id, Request $request)
    {
        $atelier = $this->repository->find($id);
        $visibility = $atelier->getVisibility();

        $atelier->setVisibility(!$visibility);

        $this->em->flush();

        if (!$atelier->getEvent())
        {
            return $this->redirectToRoute('admin');
        }

        return $this->redirectToRoute('admin_events');
    }

    /**
     * @Route("/pwup/atelier/{id}", name="pwAtelier")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pwAtelier($id)
    {
        $atelier = $this->repository->find($id);
        $menu = $atelier->getEvent() ? 'events':'atelier';

        return $this->render('/pwup/pw_atelier.html.twig', [
            'atelier' => $atelier,
            'current_menu' => 'admin',
            'admin_menu' => $menu
        ]);
    }
}

