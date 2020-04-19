<?php


namespace App\Controller;

use App\Form\ResettingType;
use App\Notification\ResettingMailer;
use App\Repository\UserRepository;
use Datetime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResettingController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $repository;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * ResettingController constructor.
     * @param UserRepository $repository
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $em
     */
    public function __construct(UserRepository $repository, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->encoder = $encoder;
        $this->em = $em;
    }

    /**
     * @Route("/resetting/request", name="resettingRequest")
     * @param Request $request
     * @param TokenGeneratorInterface $tokenGenerator
     * @param ResettingMailer $resettingMailer
     * @return Response
     * @throws Exception
     */
    public function resettingRequest(Request $request, TokenGeneratorInterface $tokenGenerator, ResettingMailer $resettingMailer){
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'label'=> 'Votre adresse e-mail :',
                'constraints' => [
                    new Email(),
                    new NotBlank()
                ]
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash(
                'notice',
                'Si votre adresse est valide, vous allez recevoir un email de réinitialisation dans quelques instants.'
            );

            $user = $this->repository->findOneBy([
                'email' => $form['email']->getData()
            ]);

            if (!$user) {
                return $this->redirectToRoute("login");
            }

            $user->setToken($tokenGenerator->generateToken());

            $user->setPasswordRequestedAt(new Datetime());
            $this->em->flush();

            $resettingMailer->notify($user);

            return $this->redirectToRoute("login");
        }

        return $this->render('security/request.html.twig', [
            'form' => $form->createView()
        ]);

    }

    private function isRequestInTime(Datetime $passwordRequestedAt = null)
    {
        if ($passwordRequestedAt === null)
        {
            return false;
        }

        $now = new DateTime();
        $interval = $now->getTimestamp() - $passwordRequestedAt->getTimestamp();

        return $interval < 601;
    }

    /**
     * @Route("/resetting/{id}/{token}", name="resetting")
     * @param $id
     * @param $token
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function resettingPassword($id, $token, Request $request){
        $user = $this->repository->find($id);

        if ($user->getToken() === null || $token !== $user->getToken() || !$this->isRequestInTime($user->getPasswordRequestedAt()))
        {
            $this->addFlash(
                'warning',
                'La demande a expiré, veuillez réessayer.'
            );
            return $this->redirectToRoute('login');
        }

        $form = $this->createForm(ResettingType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $password = $form['password']->getData();
            $user->setPassword($this->encoder->encodePassword($user, $password));

            $user->setToken();
            $user->setPasswordRequestedAt();
            $user->setUpdatedAt(new DateTime());

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash(
                'success',
                'Votre mot de passe a été renouvelé !'
            );

            return $this->redirectToRoute('login');
        }

        return $this->render('security/resetting.html.twig', [
            'form' => $form->createView()
        ]);
    }

}