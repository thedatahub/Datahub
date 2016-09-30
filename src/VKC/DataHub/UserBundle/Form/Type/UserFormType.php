<?php

namespace VKC\DataHub\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('email')
            ->add('roles', 'choice', array(
                'required'    => true,
                'empty_value' => false,
                'multiple'    => true,
                'attr'        => array(
                    'class' => 'chosen-select',
                ),
                'choices'     => array(
                    'ROLE_ADMIN'       => 'Admin',
                    'ROLE_USER'        => 'User',
                    'ROLE_SUPER_ADMIN' => 'Super admin',
                ),
            ))
        ;

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                $required = !$data || !$data->getId();
                $isActivated = $data && $data->getLastLogin();

                if ($isActivated) {
                    $form->add('enabled', null, [
                        'required' => false,
                    ]);

                    $form->add('plainPassword', 'repeated', [
                        'required'          => $required,
                        'type'              => 'password',
                        'first_options'     => ['label' => 'Password'],
                        'second_options'    => ['label' => 'Verify password'],
                        'invalid_message'   => 'The two password fields don\'t match.',
                    ]);
                }

                $form->add('submit', 'submit');

                // if ($required) {
                //     return;
                // }
            });
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VKC\DataHub\UserBundle\Document\User',
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
