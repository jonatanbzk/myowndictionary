<?php


namespace App\Form\Dictionary;


use App\Entity\Tag;
use App\Controller\Dictionary\DictionaryController;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('language_1', ChoiceType::class, [
                'choices' => [
                    'English' => 0,
                    'French' => 1,
                    'German' => 2,
                    'Polish' => 3,
                    'Russian' => 4,
                    'Italian' => 5,
                    'Portuguese' => 6,
                    'Spanish' => 7,
                    'Esperanto' => 8
                ],
                'required' => true,
                'placeholder' => 'Select the first language',
                'label' => false
            ])
            ->add('language_2', ChoiceType::class, [
                'choices' => [
                    'English' => 0,
                    'French' => 1,
                    'German' => 2,
                    'Polish' => 3,
                    'Russian' => 4,
                    'Italian' => 5,
                    'Portuguese' => 6,
                    'Spanish' => 7,
                    'Esperanto' => 8
                ],
                'required' => true,
                'placeholder' => 'Select the second language',
                'label' => false
            ]);
    }
}