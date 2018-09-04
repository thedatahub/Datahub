<?php

namespace DataHub\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
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
            ->add('username')
            ->add('email', EmailType::class)
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => $options['create'],
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ])
            ->add('newUserBtn', SubmitType::class, [
                'label' => $options['submitLabel'], 
                'attr' => ['class' => 'btn btn-lg btn-success btn-block']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
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
        ]);
    }
}


