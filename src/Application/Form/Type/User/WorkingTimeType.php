<?php

namespace Application\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WorkingTimeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $years = array(2015, 2016);

        $builder->add('timeStarted', 'datetime', array(
            'years' => $years,
            'data' => new \Datetime('now'),
        ));

        $builder->add('timeEnded', 'datetime', array(
            'required' => false,
            'years' => $years,
        ));

        $builder->add('notes', 'textarea', array(
            'required' => false,
        ));

        $builder->add('location', 'textarea', array(
            'required' => false,
        ));

        $builder->add('Save', 'submit', array(
            'attr' => array(
                'class' => 'btn-primary btn-lg btn-block',
            ),
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Application\Entity\WorkingTimeEntity',
            'validation_groups' => array('newAndEdit'),
            'csrf_protection' => true,
            'csrf_field_name' => 'csrf_token',
        ));
    }

    public function getName()
    {
        return 'workingTime';
    }
}
