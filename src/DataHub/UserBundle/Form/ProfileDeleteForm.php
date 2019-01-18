<?php

namespace DataHub\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use DataHub\UserBundle\Document\User;

class ProfileDeleteForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cancelDeleteUserBtn', SubmitType::class, [
                'label' => 'Cancel action', 
                'attr' => ['class' => 'btn btn-lg btn-default btn-block']
            ])
            ->add('deleteUserBtn', SubmitType::class, [
                'label' => 'Delete this user', 
                'attr' => ['class' => 'btn btn-lg btn-danger btn-block']
            ]);
    }
}

