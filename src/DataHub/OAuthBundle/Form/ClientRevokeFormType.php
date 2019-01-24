<?php

namespace DataHub\OAuthBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use DataHub\OAuthBundle\Document\Client;

class ClientRevokeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cancelRevokeTokensBtn', SubmitType::class, [
                'label' => 'Cancel action', 
                'attr' => ['class' => 'btn btn-lg btn-default btn-block']
            ])
            ->add('revokeTokensBtn', SubmitType::class, [
                'label' => 'Revoke all tokens', 
                'attr' => ['class' => 'btn btn-lg btn-danger btn-block']
            ]);
    }
}
