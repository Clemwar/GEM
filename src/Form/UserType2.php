<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UserType2 extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('adresse', TextType::class, [
                'required' => false,
                'label' => 'Adresse'
            ])
            ->add('complement', TextType::class, [
                'required' => false,
                'label' => 'Complément d\'adresse'
            ])
            ->add('codepostal', IntegerType::class, [
                'required' => false,
                'label' => 'Code postal'
            ])
            ->add('ville', TextType::class, [
                'required' => false,
                'label' => 'Ville'
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo (JPG file)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Merci de n\'envoyer que des jpeg'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
