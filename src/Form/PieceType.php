<?php

namespace App\Form;

use App\Entity\Montage;
use App\Entity\Piece;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PieceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomPiece', TextType::class, [
                'label' => 'Nom de la pièce'
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité'
            ])
            ->add('unite', NumberType::class, [
                'label' => 'Unité',
                'scale' => 2
            ])
            ->add('fournisseur', TextType::class, [
                'label' => 'Fournisseur'
            ])
            ->add('montage', EntityType::class, [
                'class' => Montage::class,
                'choice_label' => 'nomMontage',
                'label' => 'Montage'
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG ou PNG)',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Piece::class,
        ]);
    }
} 