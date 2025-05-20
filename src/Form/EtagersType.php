<?php

namespace App\Form;

use App\Entity\Etagers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Rayon;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class EtagersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('num')
            ->add('nbrPlaces')
            ->add('rayon', EntityType::class, [
                'class' => Rayon::class,
                'choice_label' => 'nom', // ou 'id' si tu n'as pas de champ nom
                'placeholder' => 'Sélectionnez un rayon',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Etagers::class,
        ]);
    }
}
