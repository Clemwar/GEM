<?php


namespace App\Notification;


use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class ResettingMailer extends AbstractController
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function notify(User $user){

        $email = (new TemplatedEmail())
            ->from('gem.bassinarcachon@gmail.com')
            ->to($user->getEmail())
            ->subject('GEM : réinitialisation du mot de passe')
            ->htmlTemplate('emails/resetting.html.twig')
            ->context([
                'user' => $user
            ])
        ;

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {

        }
    }
}