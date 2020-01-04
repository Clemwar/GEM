<?php


namespace App\Controller;


use App\Entity\Details;
use App\Form\DetailsType;
use App\Repository\AteliersRepository;
use App\Repository\DetailsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class DetailsController extends AbstractController
{

    /**
     * @var AteliersRepository
     */
    private $ateliersRepo;

    /**
     * @var DetailsRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * DetailsController constructor.
     * @param AteliersRepository $ateliersRepo
     * @param DetailsRepository $repository
     * @param EntityManagerInterface $em
     */
    public function __construct(AteliersRepository $ateliersRepo, DetailsRepository $repository, EntityManagerInterface $em)
    {
        $this->ateliersRepo = $ateliersRepo;
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/pwup/add/date/{id}", name="add_date")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addDate($id, Request $request)
    {
        $date = new Details();
        $activite = $this->ateliersRepo->find($id);
        $menu = $activite->getEvent() ? 'events':'atelier';

        $form = $this->createForm(DetailsType::class, $date);

        if ($request->isMethod('POST'))
        {
            $form->handleRequest($request);

            $date->setAtelier($activite);

            if ($form->isValid())
            {
                $this->em->persist($date);
                $this->em->flush();

                $this->addFlash('success', 'Ajout réussi');

                return $this->redirectToRoute('pwAtelier', [
                    'id' => $id
                ]);
            }
        }

        //On crée la vue
        $formView = $form->createView();

        //Le formulaire n'est pas valide ou n'a pas été soumis > on reste sur le formulaire
        return $this->render('/pwup/form.html.twig', [
            'form' => $formView,
            'titre' => 'Nouvelle date',
            'atelier' => $activite,
            'current_menu' => 'admin',
            'admin_menu' => $menu
        ]);
    }

    /**
     * @Route("/pwup/date/delete/{id}", name="delDate", methods="DELETE")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delAtelier($id, Request $request)
    {
        $detail = $this->repository->find($id);
        $atelierID = $detail->getAtelier()->getId();

        if ($this->isCsrfTokenValid('delete' . $id, $request->get('_token')))
        {
            $this->em->remove($detail);
            $this->em->flush();

            $this->addFlash('success', 'Suppression réussie');
        }

        return $this->redirectToRoute('pwAtelier', [
            'id' => $atelierID
        ]);
    }

    /**
     * @Route("/pwup/update/date/{id}", name="update_date")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateDate($id, Request $request)
    {
        $date = $this->repository->find($id);
        $activite = $date->getAtelier();
        $menu = $activite->getEvent() ? 'events':'atelier';

        $form = $this->createForm(DetailsType::class, $date);

        if ($request->isMethod('POST'))
        {
            $form->handleRequest($request);

            $date->setAtelier($activite);

            if ($form->isValid())
            {
                $this->em->persist($date);
                $this->em->flush();

                $this->addFlash('success', 'Mise à jour réussie');

                return $this->redirectToRoute('pwAtelier', [
                    'id' => $activite->getId()
                ]);
            }
        }

        //On crée la vue
        $formView = $form->createView();

        //Le formulaire n'est pas valide ou n'a pas été soumis > on reste sur le formulaire
        return $this->render('/pwup/form.html.twig', [
            'form' => $formView,
            'titre' => 'Modifier date',
            'nom' => $activite->getNom(),
            'current_menu' => 'admin',
            'admin_menu' => $menu
        ]);
    }

    /**
     * @Route("/pwup/get/details/{id}", name="getDetails")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDetails($id)
    {
        $detail = $this->repository->find($id);
        $menu = $detail->getAtelier()->getEvent() ? 'events':'atelier';

        return $this->render('/pwup/pw_date.html.twig', [
            'detail'=>$detail,
            'current_menu' => 'admin',
            'admin_menu' => $menu
        ]);
    }
}