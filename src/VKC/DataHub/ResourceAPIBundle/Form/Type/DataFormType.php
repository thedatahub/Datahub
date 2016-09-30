<?php

namespace VKC\DataHub\ResourceAPIBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

use VKC\DataHub\ResourceBundle\Service\DataConvertersService;

/**
 * Form type for creating/updating data resources.
 *
 * @author Kalman Olah <kalman@inuits.eu>
 */
class DataFormType extends AbstractType
{
    /**
     * @var DataConvertersService
     */
    protected $dataConverters;

    /**
     * Set dataConverters.
     *
     * @param DataConvertersService $dataConverters
     * @return DataFormType
     */
    public function setDataConverters(DataConvertersService $dataConverters)
    {
        $this->dataConverters = $dataConverters;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formats = isset($this->dataConverters) ? $this->dataConverters->getConverterList() : [];
        $formats = array_combine($formats, $formats);

        $builder
            ->add('data', TextareaType::class, [
                'mapped'      => false,
                'required'    => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('format', ChoiceType::class, [
                'mapped'      => false,
                'required'    => true,
                'choices'     => $formats,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Choice(['choices' => $formats]),
                ],
            ])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
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
