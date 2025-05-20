<?php
namespace App\Form;

use App\Entity\Produits;
use App\Entity\Rayon;
use App\Entity\Etagers;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManagerInterface;


class ProduitType extends AbstractType
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom du produit'])
            ->add('prix', NumberType::class, ['label' => 'Prix'])
            ->add('stock', IntegerType::class, ['label' => 'Stock'])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('model', FileType::class, [
                'label' => 'Modèle 3D (.glb)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => ['model/gltf-binary', 'application/octet-stream'],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier .glb valide.',
                    ])
                ],
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo du produit',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/jpg'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, JPG ou PNG).',
                    ])
                ]
            ])
            ->add('rayon', EntityType::class, [
                'class' => Rayon::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir un rayon',
                'required' => true,
            ]);

        // Dynamically populate etagers
        $formModifier = function ($form, Rayon $rayon = null) {
            $etagers = $rayon ? $this->em->getRepository(Etagers::class)->findBy(['rayon' => $rayon]) : [];

            $form->add('etagers', EntityType::class, [
                'class' => Etagers::class,
                'choices' => $etagers,
                'choice_label' => 'num',
                'placeholder' => 'Sélectionnez un rayon d’abord',
                'required' => true,
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();
                $formModifier($event->getForm(), $data ? $data->getRayon() : null);
            }
        );

        $builder->get('rayon')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $rayon = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $rayon);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produits::class,
        ]);
    }
}

