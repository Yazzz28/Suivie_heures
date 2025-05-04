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
                'label' => 'Date',
            ])
            ->add('startTime', null, [
                'widget' => 'single_text',
                'label' => 'Heure de début',
            ])
            ->add('pauseStart', null, [
                'widget' => 'single_text',
                'label' => 'Heure de pause',
            ])
            ->add('pauseEnd', null, [
                'widget' => 'single_text',
                'label' => 'Heure de reprise',
            ])
            ->add('endTime', null, [
                'widget' => 'single_text',
                'label' => 'Heure de fin',
            ])
            ->add('numberOfTransport', null, [
                'label' => 'Nombre de transports',
                'attr' => [
                    'min' => 0,
                    'type' => 'number',
                ],
            ])
            ->add('dayOf', null, [
                'label' => 'Congé',
                'attr' => [
                    'type' => 'checkbox',
                ],
            ])
            ->add('dayOfWhitoutSolde', null, [
                'label' => 'Congé sans solde',
                'attr' => [
                    'type' => 'checkbox',
                ],
            ])
            ->add('isPublicHolidays', null, [
                'label' => 'Férié',
                'attr' => [
                    'type' => 'checkbox',
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
