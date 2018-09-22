<?php

namespace DataHub\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class LoginForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_username', null, array(
                'label' => false,
                'required' => true,
                'attr' => array(
                    'placeholder' => 'username',
                    'class' => 'form-control'
                )
            ))
            ->add('_password', PasswordType::class, array(
                'label' => false,
                'required' => true,
                'attr' => array(
                    'placeholder' => 'password',
                    'class' => 'form-control'
                )
            ));
    }
}