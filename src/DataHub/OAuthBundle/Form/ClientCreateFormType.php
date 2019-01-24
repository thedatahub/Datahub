<?php

namespace DataHub\OAuthBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
// use Symfony\Component\Form\Extension\Core\Type\EmailType;
// use Symfony\Component\Form\Extension\Core\Type\PasswordType;
// use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use DataHub\OAuthBundle\DTO\ClientCreateData;
use OAuth2\OAuth2;

class ClientCreateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'applicationName', null, [
                    'label' => 'Application name',
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            )
            ->add(
                'redirectUris', null, [
                    'label' => 'Redirect URIs',
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            )
            ->add(
                'allowedGrantTypes', ChoiceType::class, [
                    'choices'  => [
                        'Authorization code' => OAuth2::GRANT_TYPE_AUTH_CODE,
                        'Implicit' => OAuth2::GRANT_TYPE_IMPLICIT,
                        'User credentials' => OAuth2::GRANT_TYPE_USER_CREDENTIALS,
                        'Client credentials' => OAuth2::GRANT_TYPE_CLIENT_CREDENTIALS
                    ],
                    'required' => true,
                    'empty_data' => 'ROLE_CONSUMER',
                    'multiple' => true,
                    // *this line is important*
                    'choices_as_values' => true,
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            )
            ->add(
                'newClientBtn', SubmitType::class, [
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
                'data_class' => ClientCreateData::class,
                'create'     => true,
                'submitLabel' => 'New client',
            ]
        );
    }
}


