<?php

namespace App\Form;

use App\Entity\Work;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('startTime', null, [
                'widget' => 'single_text',
            ])
            ->add('pauseStart', null, [
                'widget' => 'single_text',
            ])
            ->add('pauseEnd', null, [
                'widget' => 'single_text',
            ])
            ->add('endTime', null, [
                'widget' => 'single_text',
            ])
            ->add('numberOfTransport', null, [
                'label' => 'Nombre de transports',
                'attr' => [
                    'min' => 0,
                    'type' => 'number',
                ],
            ])
            ->add('comment',
            null,[
                    'label' => 'Commentaire',
                    'attr' => [
                        'rows' => 5,
                        'style' => 'resize: none;',
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Work::class,
        ]);
    }
}
