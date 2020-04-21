<?php


namespace App\Controller;

use App\Repository\DetailsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var DetailsRepository
     */
    private $detailsRepository;

    /**
     * ReservationController constructor.
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $em
     * @param DetailsRepository $detailsRepository
     */
    public function __construct(UserRepository $userRepository, EntityManagerInterface $em, DetailsRepository $detailsRepository)
    {
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->detailsRepository = $detailsRepository;
    }

    /**
     * @Route("pages/reservation/{userID}/{detailID}/{event}", name="reservation")
     * @param $userID
     * @param $detailID
     * @param $event
     * @param Request $request
     * @return RedirectResponse
     */
    public function addReservation($userID, $detailID, $event, Request $request)
    {
        $details = $this->detailsRepository->find($detailID);
        $user= $this->userRepository->find($userID);
        $fragment = ($event) ? 'events':'ateliers';
        $places = $details->getPlaces();

        if ((isset($places) && (count($details->getParticipants()) < $places)) || (empty($places))) {
            if ($this->isCsrfTokenValid('reservation' . $userID, $request->get('_token'))) {
                $user->setReservations($details);
                $details->setParticipants($user);

                $this->em->flush();
            }
        }

        return $this->redirectToRoute('showActivites', ['_fragment' => $fragment]);
    }

    /**
     * @Route("pages/annulation/{userID}/{detailID}/{event}", name="annulation")
     * @param $userID
     * @param $detailID
     * @param $event
     * @param Request $request
     * @return RedirectResponse
     */
    public function delReservation($userID, $detailID, $event, Request $request)
    {
        $participant = $this->userRepository->find($userID);
        $reservation = $this->detailsRepository->find($detailID);
        $fragment = ($event) ? 'events':'ateliers';

        if ($this->isCsrfTokenValid('annulation' . $userID, $request->get('_token'))) {
            $participant->removeReservation($reservation);
            $reservation->removeParticipant($participant);

            $this->em->flush();
        }

        return $this->redirectToRoute('showActivites', ['_fragment' => $fragment]);
    }

    /**
     * @Route("pwup/annulation/{userID}/{detailID}", name="admin_annulation")
     * @param $userID
     * @param $detailID
     * @return RedirectResponse
     */
    public function rmvReservation($userID, $detailID)
    {
        $participant = $this->userRepository->find($userID);
        $reservation = $this->detailsRepository->find($detailID);

        $participant->removeReservation($reservation);
        $reservation->removeParticipant($participant);

        $this->em->flush();

        $this->addFlash('success', 'Participant retiré');

        return $this->redirectToRoute('getDetails', [
            'id'=> $detailID
        ]);
    }
}