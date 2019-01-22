<?php

namespace DataHub\OAuthBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use DataHub\OAuthBundle\Document\Client;

class ClientDeleteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cancelDeleteClientBtn', SubmitType::class, [
                'label' => 'Cancel action', 
                'attr' => ['class' => 'btn btn-lg btn-default btn-block']
            ])
            ->add('deleteClientBtn', SubmitType::class, [
                'label' => 'Delete this client', 
                'attr' => ['class' => 'btn btn-lg btn-danger btn-block']
            ]);
    }
}

