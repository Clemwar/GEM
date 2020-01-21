<?php


namespace App\Controller;


use App\Entity\Details;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\DetailsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
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
     */
    public function __construct(UserRepository $repository, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->encoder = $encoder;
        $this->em = $em;
    }

    /**
     * @Route("/pwup/users", name="admin_users")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\DBALException
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
     * @Route("/user/add", name="add_user")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addUser(Request $request)
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

                //On traite l'ajout d'image
                /** @var UploadedFile $image */
                $image = $form['photo']->getData();

                // On vérifie la présence d'un fichier uploadé
                if ($image) {
                    $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                    //On place le fichier dans le dossier prévu sur le serveur
                    try {
                        $image->move(
                            $this->getParameter('photos_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('warning', 'L\'envoi de la photo a échoué');
                    }

                    // On ajoute le nom de l'image à l'utilisateur concerné
                    $user->setPhoto($newFilename);
                }

                //On traite l'encodage du mot de passe
                $password = $form['password']->getData();
                $user->setPassword($this->encoder->encodePassword($user, $password));

                $this->addFlash('success', 'Bienvenue, inscription terminée');

                // On enregistre notre objet $user dans la base de données, par exemple
                $this->em->persist($user);
                $this->em->flush();

                // On redirige vers la page de connexion
                return $this->redirectToRoute('login', [
                    'current_menu' => 'login'
                ]);
            }
        }

        // À ce stade, le formulaire n'est pas valide car :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau
        return $this->render('/pages/form.html.twig', [
            'form' => $form->createView(),
            'current_menu' => 'login'
        ]);
    }

    /**
     * @Route("/pwup/user/delete/{id}", name="admin_delete_user", methods="DELETE")
     */
    public function deleteUser ($id, Request $request){

        $user = $this->repository->find($id);

        if ($this->isCsrfTokenValid('delete' . $id, $request->get('_token'))) {

            //Je m'assure qu'une image est liée à l'utilisateur
            if ($user->getPhoto() !== null) {
                //J'appelle le gestionnaire de fichier
                $filesystem = new Filesystem();
                //Je vérifie si le fichier existe, si oui, symfony le supprime
                if ($filesystem->exists($imageURL = $this->getParameter('photos_directory') . "/" . $user->getPhoto())) {
                    $filesystem->remove([$imageURL]);
                }
            }

            $this->em->remove($user);
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/user/update/{id}", name="update_user")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateUser($id, Request $request)
    {
        $user = $this->repository->find($id);

        // On crée le FormBuilder en appelant le formtype
        $form = $this->createForm(UserType::class, $user);

        // Si la requête est en POST
        if ($request->isMethod('POST')) {
            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $user contient les valeurs entrées dans le formulaire par le visiteur
            $form->handleRequest($request);

            // On vérifie que les valeurs entrées sont correctes
            if ($form->isValid()) {

                //On traite l'ajout d'image
                /** @var UploadedFile $image */
                $image = $form['photo']->getData();

                // On vérifie la présence d'un fichier uploadé
                if ($image) {
                    $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                    //Je vérifie si une image est liée à l'utilisateur
                    if ($user->getPhoto() !== null) {
                        //J'appelle le gestionnaire de fichier
                        $filesystem = new Filesystem();
                        //Je vérifie si le fichier existe, si oui, symfony le supprime
                        if ($filesystem->exists($imageURL = $this->getParameter('photos_directory') . "/" . $user->getPhoto())) {
                            $filesystem->remove([$imageURL]);
                        }
                    }

                    //On place le fichier dans le dossier prévu sur le serveur
                    try {
                        $image->move(
                            $this->getParameter('photos_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('warning', 'L\'envoi de la photo a échoué');
                    }

                    // On ajoute le nom de l'image à l'utilisateur concerné
                    $user->setPhoto($newFilename);
                }

                //On traite l'encodage du mot de passe
                $password = $form['password']->getData();
                $user->setPassword($this->encoder->encodePassword($user, $password));

                $this->addFlash('success', 'Mise à jour de contact terminée');

                // On enregistre notre objet $user dans la base de données, par exemple
                $this->em->persist($user);
                $this->em->flush();

                // On redirige vers la page de connexion
                return $this->redirectToRoute('login', [
                    'current_menu' => 'login'
                ]);
            }
        }

        // À ce stade, le formulaire n'est pas valide car :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau
        return $this->render('/pages/form.html.twig', [
            'form' => $form->createView(),
            'current_menu' => 'login'
        ]);
    }

    /**
     * @Route("/pwup/user/addRole/{id}", name="add_role")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addRole($id, Request $request, ValidatorInterface $validator)
    {
        $user = $this->repository->find($id);

        //On récupère les données du formulaire
        $role = $request->get('role');

        //On insert les données dans l'objet avant la validation
        $user->setRoles([$role]);

        //On vérifie si les données sont conformes en listant les erreurs
        $listErrors = $validator->validate($user);

        //Les données sont conformes
        if (count($listErrors) === 0) {
            //On vérifie le token pour valider le flush
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

    /**
     * @Route("pages/reservation/{userID}/{detailID}/{event}", name="reservation")
     * @param $id
     * @param Details $details
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addReservation($userID, $detailID, DetailsRepository $detailsRepository, $event)
    {
        $details = $detailsRepository->find($detailID);
        $user= $this->repository->find($userID);
        $fragment = ($event) ? 'events':'ateliers';
        $places = $details->getPlaces();

        if ((isset($places) && (count($details->getParticipants()) < $places)) || (empty($places))) {
            $user->setReservations($details);
            $details->setParticipants($user);

            $this->em->flush();
        }

        return $this->redirectToRoute('showActivites', ['_fragment' => $fragment]);
    }

    /**
 * @Route("pages/annulation/{userID}/{detailID}/{event}", name="annulation")
 */
    public function delReservation($userID, $detailID, DetailsRepository$detailsRepository, $event)
    {
        $participant = $this->repository->find($userID);
        $reservation = $detailsRepository->find($detailID);
        $fragment = ($event) ? 'events':'ateliers';

        $participant->removeReservation($reservation);
        $reservation->removeParticipant($participant);

        $this->em->flush();

        return $this->redirectToRoute('showActivites', ['_fragment' => $fragment]);
    }

    /**
     * @Route("pwup/annulation/{userID}/{detailID}", name="admin_annulation")
     */
    public function rmvReservation($userID, $detailID, DetailsRepository$detailsRepository)
    {
        $participant = $this->repository->find($userID);
        $reservation = $detailsRepository->find($detailID);

        $participant->removeReservation($reservation);
        $reservation->removeParticipant($participant);

        $this->em->flush();

        $this->addFlash('success', 'Participant retiré');

        return $this->redirectToRoute('getDetails', [
            'id'=> $detailID
        ]);
    }
}