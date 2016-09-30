<?php

namespace VKC\DataHub\OAuthBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use OAuth2\OAuth2;

/**
 * {@inheritDoc}
 */
class ClientFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', null, array(
                'required' => true,
            ))
            ->add('allowedGrantTypes', 'choice', array(
                'required'    => true,
                'empty_value' => false,
                'multiple'    => true,
                'attr'        => array(
                    'class' => 'chosen-select',
                ),
                'choices'     => array(
                    OAuth2::GRANT_TYPE_AUTH_CODE          => 'authorization code',
                    OAuth2::GRANT_TYPE_IMPLICIT           => 'token',
                    OAuth2::GRANT_TYPE_USER_CREDENTIALS   => 'password',
                    OAuth2::GRANT_TYPE_CLIENT_CREDENTIALS => 'client credentials',
                    OAuth2::GRANT_TYPE_REFRESH_TOKEN      => 'refresh token',
                    OAuth2::GRANT_TYPE_EXTENSIONS         => 'extensions',
                ),
            ))
            ->add('redirectUris', 'bootstrap_collection', array(
                'allow_add'    => true,
                'allow_delete' => true,
                'required'     => true,
            ))
            ->add('submit', 'submit')
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VKC\DataHub\OAuthBundle\Document\Client',
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return '';
    }
}
