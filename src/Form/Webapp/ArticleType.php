<?php

namespace App\Form\Webapp;

use App\Entity\Webapp\Article;
use App\Entity\Webapp\Category;
use App\Entity\Webapp\Page;
use App\Repository\Webapp\PageRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('content')
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'choice_label' => 'name',
            ])
            ->add('articleFrontFile', VichImageType::class, [
                'label' => "Image illustrant l'article",
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Supprimer',
                'download_label' => 'Télecharger',
            ])
            ->add('state', ChoiceType::class, [
                'choices'  => [
                    'Brouillon' => 'draft',
                    'Publiée' => 'publish',
                ],
            ])
            ->add('articleFrontPosition', ChoiceType::class, [
                'label' => "Position de l'image suivant le contenu",
                'choices'  => [
                    'à droite du contenu' => 'right',
                    'à gauche du contenu' => 'left',
                    'au dessus du contenu' => 'down',
                    'en dessous du contenu' => 'up',
                    'sans texte' => 'none'
                ],
            ])
            ->add('isReadMore')
            ->add('isShowtitle')
            ->add('isShowdate')
            ->add('isLink')
            ->add('linkPage', EntityType::class, [
                'class' => Page::class,
                'choice_label' => 'name',
                'placeholder' => 'aucun lien',
                'required' => false,
                'query_builder' => function(PageRepository $pageRepository){
                    return $pageRepository->createQueryBuilder('p')->orderBy('p.name', 'ASC');
                }
            ])
            ->add('linktext')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
