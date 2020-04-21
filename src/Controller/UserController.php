<?php


namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserType2;
use App\Form\UserTypeFull;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class UserController extends AbstractController
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
     * UserController constructor.
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
     * @Route("/pwup/users", name="admin_users")
     * @return Response
     * @throws DBALException
     */
    public function getUsers()
    {
        $users = $this->repository->getUsers();

        return $this->render('/pwup/users.html.twig', [
            'users' => $users,
            'current_menu' => 'admin',
            'admin_menu' => 'users'
        ]);
    }

    /**
     * Method to add new user
     * @Route("/user/add", name="add_user")
     * @param Request $request
     * @param TokenGeneratorInterface $tokenGenerator
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function addUser(Request $request, TokenGeneratorInterface $tokenGenerator)
    {
        $user = new User();

        // On crée le FormBuilder en appelant le formtype
        $form = $this->createForm(UserType::class, $user);

        // Si la requête est en POST
        if ($request->isMethod('POST')) {
            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $user contient les valeurs entrées dans le formulaire par le visiteur
            $form->handleRequest($request);

            // On vérifie que les valeurs entrées sont correctes
            if ($form->isValid()) {

                //On traite l'encodage du mot de passe
                $password = $form['password']->getData();
                $user->setPassword($this->encoder->encodePassword($user, $password));

                //On note la date d'inscription
                $date = new DateTime();
                $user->setCreatedAt($date);

                $user->setToken($tokenGenerator->generateToken());

                // On enregistre notre objet $user dans la base de données
                $this->em->persist($user);
                $this->em->flush();

                // On redirige vers la page de connexion
                return $this->redirectToRoute('addUserBis', [
                    'id' => $user->getId(),
                    'token'=> $user->getToken(),
                    'current_menu' => 'login'
                ]);
            }
        }

        // À ce stade, le formulaire n'est pas valide car :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau
        return $this->render('/pages/register.html.twig', [
            'form' => $form->createView(),
            'current_menu' => 'login'
        ]);
    }

    /**
     * @Route("/user/add2/{id}/{token}", name="addUserBis")
     * @param Request $request
     * @param $id
     * @param $token
     * @param ImageController $imageController
     * @return Response
     */
    public function addUserBis (Request $request, $id, $token, ImageController $imageController){
        $user = $this->repository->find($id);
        $form = $this->createForm(UserType2::class, $user);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            // On vérifie que les valeurs entrées sont correctes
            if ($form->isValid() && !$user->getToken() === null || $token == $user->getToken()) {
                $this->addFlash('success', 'Bienvenue, inscription terminée');

                /** @var UploadedFile $image */
                $image = $form['photo']->getData();

                if ($image) {
                    $imageController->addImage($image, $user);
                }

                $user->setToken();

                $this->em->flush();

                return $this->redirectToRoute('login', [
                    'current_menu' => 'login'
                ]);
            }
        }

        return $this->render('/pages/register2.html.twig', [
            'form' => $form->createView(),
            'current_menu' => 'login'
        ]);
    }

    /**
     * Delete user method, administrators only
     * @Route("/pwup/user/delete/{id}", name="admin_delete_user", methods="DELETE")
     * @param $id //to find user
     * @param Request $request to catch the token
     * @param ImageController $imageController
     * @return RedirectResponse
     */
    public function deleteUser ($id, Request $request, ImageController $imageController){
        $user = $this->repository->find($id);

        if ($this->isCsrfTokenValid('delete' . $id, $request->get('_token'))) {
            $imageController->deleteImage($user);

            $this->em->remove($user);
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/user/update/{id}", name="update_user")
     * @param $id
     * @param Request $request
     * @param ImageController $imageController
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function updateUser($id, Request $request, ImageController $imageController)
    {
        $user = $this->repository->find($id);

        $form = $this->createForm(UserTypeFull::class, $user);

        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                /** @var UploadedFile $image */
                $image = $form['photo']->getData();

                if ($image) {
                    $imageController->addImage($image, $user);
                }

                $password = $form['password']->getData();
                if ($password){
                    $user->setPassword($this->encoder->encodePassword($user, $password));
                }

                $date = new DateTime();
                $user->setUpdatedAt($date);

                $this->addFlash('success', 'Mise à jour de contact terminée');

                $this->em->persist($user);
                $this->em->flush();

                return $this->redirectToRoute('login', [
                    'current_menu' => 'login'
                ]);
            }
        }

        return $this->render('/pages/form.html.twig', [
            'form' => $form->createView(),
            'current_menu' => 'login'
        ]);
    }

    /**
     * @Route("/pwup/user/addRole/{id}", name="add_role")
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return RedirectResponse
     */
    public function addRole($id, Request $request, ValidatorInterface $validator)
    {
        $user = $this->repository->find($id);

        $role = $request->get('role');

        $user->setRoles([$role]);

        $listErrors = $validator->validate($user);

        if (count($listErrors) === 0) {
            if ($this->isCsrfTokenValid('roles' . $id, $request->get('_token'))) {
                $this->em->flush();
                $this->addFlash('success', 'Changement de rôle réussi');
            }
            else
            {
                $this->addFlash('warning', 'Vous ne pouvez pas changer le rôle de cette façon. Token invalide');
            }
        }
        else
        {
            //il y'a des erreurs, les données ne sont pas conformes
            $this->addFlash('warning', 'Le rôle n\'existe pas');
        }

        return $this->redirectToRoute('admin_users');
    }

}