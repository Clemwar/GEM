<?php


namespace App\Notification;


use App\Entity\Contact;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;

class ContactNotification extends AbstractController
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function notify(Contact $contact){

        $email = (new TemplatedEmail())
            ->from($contact->getEmail())
            ->to('clement.jean@lapiscine.pro')
            ->subject('Contact depuis le site GEM')
            ->htmlTemplate('emails/contact.html.twig')
            ->context([
                'contact' => $contact
            ])
        ;

        $sentMail =$this->mailer->send($email);
    }
}