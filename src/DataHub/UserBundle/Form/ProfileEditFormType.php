<?php

namespace DataHub\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use DataHub\UserBundle\Document\User;
use DataHub\UserBundle\Form\ProfileCreateFormType;
use DataHub\UserBundle\DTO\ProfileEditData;

class ProfileEditFormType extends AbstractType
{
    private $authChecker;

    private $tokenStorage;

    public function __construct(AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage)
    {
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
    }

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
            );

            $isGranted = $this->authChecker->isGranted('ROLE_ADMIN');

            $builder->addEventListener(
                FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($isGranted) {
                    if ($isGranted) {
                        $form = $event->getForm();
                        $form->add(
                            'roles', ChoiceType::class, [
                                'choices'  => [
                                    'Administrator' => 'ROLE_ADMIN',
                                    'Manager' => 'ROLE_MANAGER',
                                    'Consumer' => 'ROLE_CONSUMER',
                                ],
                                'required' => false,
                                'empty_data' => 'ROLE_CONSUMER',
                                'multiple' => true,
                                // *this line is important*
                                'choices_as_values' => true,
                                'attr' => [
                                    'class' => 'form-control'
                                ]
                            ]
                        );          
                    }
                }
            );

            $builder->add(
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
                'data_class' => ProfileEditData::class,
                'create'     => true,
                'submitLabel' => 'Edit user',
            ]
        );
    }
}


