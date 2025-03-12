<?php
// src/Form/ProduitType.php
namespace App\Form;

use App\Entity\Produits;
use App\Entity\Rayon;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
            ])
            ->add('model', FileType::class, [
                'label' => 'Modèle 3D (.glb)',
                'mapped' => false, // Important
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'model/gltf-binary',
                            'application/octet-stream',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier .glb valide.',
                    ])
                ],
            ])
            ->add('rayon', EntityType::class, [
                'class' => Rayon::class,
                'choice_label' => 'nom',
                'label' => 'Rayon',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produits::class,
        ]);
    }
}
