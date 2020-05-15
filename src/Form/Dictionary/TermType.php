<?php

namespace App\Form\Dictionary;

use App\Entity\Term;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TermType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('word', null, array('label' => false))
            ->add('translation', null, array('label' => false,
                'attr' => ['class' => 'mt-2']))
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'btn btnRegister btn-primary mt-2'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Term::class,
        ]);
    }
}