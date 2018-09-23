<?php

namespace DataHub\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use DataHub\UserBundle\Document\User;

class ProfileForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'username', null, [
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            )
            ->add(
                'firstName', null, [
                    'label' => 'First name',
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            )
            ->add(
                'lastName', null, [
                    'label' => 'Last name',
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            )
            ->add(
                'email', EmailType::class, [
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            )
            ->add(
                'roles', ChoiceType::class, [
                    'choices'  => [
                        'super administrator' => 'ROLE_SUPER_ADMIN',
                        'Administrator' => 'ROLE_ADMIN',
                        'User' => 'ROLE_USER',
                    ],
                    'required' => false,
                    'empty_data' => 'ROLE_USER',
                    'multiple' => true,
                    // *this line is important*
                    'choices_as_values' => true,
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            )
            ->add(
                'plainPassword', RepeatedType::class, [
                    'options' => [
                        'attr' => [
                            'class' => 'form-control'
                        ]
                    ],
                    'type' => PasswordType::class,
                    'required' => $options['create'],
                    'first_options'  => [
                        'label' => 'Password'
                    ],
                    'second_options' => [
                        'label' => 'Repeat Password'
                    ]
                ]
            )
            ->add(
                'newUserBtn', SubmitType::class, [
                    'label' => $options['submitLabel'], 
                    'attr' => [
                        'class' => 'btn btn-lg btn-success btn-block'
                    ]
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
                'create'     => true,
                'submitLabel' => 'New user',
                'validation_groups' => function (FormInterface $form) {
                    $data = $form->getData();

                    if ($data->getPlainPassword() == '' && $form->getConfig()->getOption('create')) {
                        return ['Default'];
                    }

                    return ['Default', 'Update'];
                }
            ]
        );
    }
}


