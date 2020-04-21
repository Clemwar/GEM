<?php


namespace App\Controller;


use App\Entity\Ateliers;
use App\Entity\User;
use App\Repository\DetailsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageController extends AbstractController
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
     * @var Filesystem
     */
    private $fileSystem;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $em, DetailsRepository $detailsRepository, Filesystem $fileSystem)
    {
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->detailsRepository = $detailsRepository;
        $this->fileSystem = $fileSystem;
    }

    public function deleteImage ($object){

        if ($object instanceof User) {
            if ($object->getPhoto() !== null) {
                if ($this->fileSystem->exists($imageURL = $this->getParameter('photos_directory') . "/" . $object->getPhoto())) {
                    $this->fileSystem->remove([$imageURL]);

                    $object->setPhoto(null);
                    $this->em->flush();
                }
            }
        }elseif ($object instanceof Ateliers){
            if ($object->getCover() !== null) {
                if ($this->fileSystem->exists($imageURL = $this->getParameter('photos_directory') . "/" . $object->getCover())) {
                    $this->fileSystem->remove([$imageURL]);

                    $object->setCover(null);
                    $this->em->flush();
                }
            }
        }
    }

    public function addImage(UploadedFile $image, $object){
        if ($object instanceof User || $object instanceof Ateliers){
            $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

            $this->deleteImage($object);

            //On place le fichier dans le dossier prévu sur le serveur
            try {
                $image->move(
                    $this->getParameter('photos_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                $this->addFlash('warning', 'L\'envoi de la photo a échoué');
            }

            // On ajoute le nom de l'image en bdd
            if ($object instanceof User){
                $object->setPhoto($newFilename);
            }elseif($object instanceof Ateliers){
                $object->setCover($newFilename);
            }
        }
    }
}