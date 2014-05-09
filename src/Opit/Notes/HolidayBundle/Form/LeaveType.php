<?php

/*
 * The MIT License
 *
 * Copyright 2014 Marton Kaufmann <kaufmann@opit.hu>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Opit\Notes\HolidayBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of HolidyaRequestType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package Opit
 * @subpackage Notes
 */
class LeaveType extends AbstractType
{
     /**
     * Builds a form with given fields.
     *
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startDate', 'date', array(
            'widget' => 'single_text',
            'label'=>'Start date',
            'attr' => array('placeholder' => 'Start date', 'class' => 'start-date')
        ));
        
        $builder->add('endDate', 'date', array(
            'widget' => 'single_text',
            'label'=>'End date',
            'attr' => array('placeholder' => 'End date', 'class' => 'end-date')
        ));
        
        $builder->add('description', 'textarea', array(
            'label'=>'Description',
            'attr' => array(
                'placeholder' => 'Description',
                'class' => 'textarea-non-resizeable',
                'maxlength' => '35'
            )
        ));
        
        $builder->add('category', 'entity', array(
            'class'=>'OpitNotesHolidayBundle:HolidayCategory',
            'property' => 'name'
        ));
    }

   /**
     * Sets the default form options
     *
     * @param object $resolver An OptionsResolver interface object
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\Notes\HolidayBundle\Entity\Leave',
        ));
    }
    /**
     * Get the name
     *
     * @return string name
     */
    public function getName()
    {
        return 'leave';
    }
}
