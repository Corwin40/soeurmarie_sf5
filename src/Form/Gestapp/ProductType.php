<?php

namespace App\Form\Gestapp;

use App\Entity\Gestapp\Product;
use App\Entity\Gestapp\ProductCategory;
use App\Entity\Gestapp\ProductNature;
use App\Repository\Gestapp\ProductCategoryRepository;
use App\Repository\GestApp\ProductNatureRepository;
use App\Entity\Gestapp\ProductUnit;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => new NotBlank(['message' => 'veuillez entrer un nom de produit.'])
            ])
            ->add('ref')
            ->add('description')
            ->add('details')
            ->add('productFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Supprimer',
                'download_label' => 'Télecharger',
            ])
            ->add('productNature', EntityType::class, [
                'placeholder' => 'Choisir une nature',
                'class' => ProductNature::class,
                'choice_label' => 'name',
                'query_builder' => function (ProductNatureRepository $productNatureRepository) {
                    return $productNatureRepository->createQueryBuilder('pn')->orderBy('pn.name', 'ASC');
                },

            ])
            ->add('productCategory', EntityType::class, [
                'placeholder' => 'Choisir une catégorie',
                'class' => ProductCategory::class,
                //'disabled' => true,
                'choice_label' => 'name',
                'query_builder' => fn(ProductCategoryRepository $productCategoryRepository)=> $productCategoryRepository->createQueryBuilder('pc')->orderBy('pc.name', 'ASC')
            ])
            ->add('productUnit', EntityType::class, [
                'placeholder' => 'Choisir une unité de tarif',
                'class' => ProductUnit::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name', 'ASC');
                },
                'choice_label' => 'name',
            ])
            ->add('price')
            ->add('quantity')
            ->add('isDisponible')
            ->add('producer', EntityType::class, [
                'class' => \App\Entity\Admin\Member::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->where('m.type = :type')
                        ->setParameter('type', 'producteur')
                        ->orderBy('m.id', 'ASC');
                },
                'choice_label' => 'structure',
            ])
            ->add('format', ChoiceType::class, [
                'choices'  => [
                    'Carte 10*15' => "card10-15",
                    'Carte double' => 'card_double',
                    'Autres' => 'other',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}