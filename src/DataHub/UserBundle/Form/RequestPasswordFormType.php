<?php

namespace DataHub\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RequestPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', null, array(
                'label' => false,
                'required' => true,
                'attr' => array(
                    'placeholder' => 'Your email-address',
                    'class' => 'form-control'
                )
            ));
    }
}