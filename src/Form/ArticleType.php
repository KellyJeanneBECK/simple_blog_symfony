<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'constraints' => [
                    new NotBlank(message: 'Enter a title'),
                    new Length(
                        min: 3,
                        minMessage: 'Your title should be at least 3 characters',
                        max: 1000,
                        maxMessage: "Your title shouldn't be longer than 1000 characters"
                    )
                ]
            ])
            ->add('content', null, [
                'constraints' => [
                    new NotBlank(message: 'Write your article content'),
                    new Length(
                        max: 10000,
                        maxMessage: "Your title shouldn't be longer than {{ limit }} characters"
                    )
                ]
            ])
            ->add('published', CheckboxType::class)
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}