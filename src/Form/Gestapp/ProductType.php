<?php

namespace App\Form\Gestapp;

use App\Entity\Gestapp\Product;
use App\Entity\Admin\Member;
use App\Entity\Gestapp\ProductCategory;
use App\Entity\Gestapp\productFormat;
use App\Entity\Gestapp\ProductNature;
use App\Repository\Gestapp\ProductCategoryRepository;
use App\Repository\GestApp\ProductNatureRepository;
use App\Entity\Gestapp\ProductUnit;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
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
            ->add('versoFile', VichImageType::class, [
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
                'choice_attr' => function (ProductNature $product, $key, $index) {
                    return ['data-data' => $product->getName()];
                }
            ])
            ->add('productCategory',EntityType::class, [
                'placeholder' => 'Choisir une categorie',
                'class' => ProductCategory::class,
                'required' => false,
                'choice_label' => 'name',
                'query_builder' => function (ProductCategoryRepository $productCategoryRepository) {
                    return $productCategoryRepository->createQueryBuilder('pc')->orderBy('pc.name', 'ASC');
                },
                'choice_attr' => function (ProductCategory $product, $key, $index) {
                    return ['data-data' => $product->getName()];
                }
            ])
            ->add('productUnit', EntityType::class, [
                'placeholder' => 'Choisir une unité de tarif',
                'required' => false,
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
                'class' => Member::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->orderBy('m.firstName', 'ASC');
                },
                'choice_label' => 'firstName',
                'label' => 'Producteur',
                'choice_attr' => function (Member $product, $key, $index) {
                    return ['data-data' => $product->getFirstName() . " " . $product->getLastName()];
                }
            ])
            ->add('tva', ChoiceType::class, [
                'choices'  => [
                    'TVA 20%' => "20",
                    'TVA 19,6%' => '19.6',
                    'TVA 5,5%' => '5.5',
                    'TVA 2.1%' => '2.1',
                    'TVA 0%' => '0',
                ],
            ])
            ->add('isPersonalisable')
            ->add('otherCategory',EntityType::class, [
                'class' => ProductCategory::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('pc')
                        ->orderBy('pc.name', 'ASC');
                },
                'required' => false,
                'multiple' => true,
                'choice_label' => 'name',
                'label' => 'Autres évènements',
                'choice_attr' => function (ProductCategory $product, $key, $index) {
                    return ['data-data' => $product->getName()];
                }
            ])
            ->add('formats',EntityType::class, [
                'class' => productFormat::class,
                'multiple' => true,
                'choice_attr' => ChoiceList::attr($this, function (?productFormat $formats) {
                    return $formats ? ['data-data' => $formats->getName()] : [];
                }),
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
