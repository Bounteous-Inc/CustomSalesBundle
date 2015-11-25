<?php

namespace DemacMedia\Bundle\CustomSalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WufooCredentialsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'apiKey',
                'text',
                [
                    'required'    => true,
                    'label'       => 'Api Key',
                ]
            )
            ->add('apiUser',
                'text',
                [
                    'required'    => true,
                    'label'       => 'Api User',
                ]
            )
            ->add('formHash',
                'text',
                [
                    'required'    => true,
                    'label'       => 'Form Hash',
                ]
            )
            ->add('formName',
                'text',
                [
                    'required'    => true,
                    'label'       => 'Form Name',
                ]
            )
            ->add('formLabel',
                'text',
                [
                    'required'    => true,
                    'label'       => 'Form Label',
                ]
            )
            ->add('domainName',
                'url',
                [
                    'required'    => true,
                    'label'       => 'Domain',
                ]
            )
            ->add('active',
                'checkbox',
                [
                    'required'    => false,
                    'label'       => 'Active',
                ]
            )
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DemacMedia\Bundle\CustomSalesBundle\Entity\WufooCredentials',
        ));
    }

    public function getName()
    {
        return 'wufoo_credentials';
    }
}
