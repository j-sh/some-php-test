<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Interfaces\MoneyInterface;
use AppBundle\Entity\Money;
use AppBundle\Entity\Product;
use AppBundle\Form\PriceTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('available')
            ->add('vatRate');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'csrf_protection' => false,
        ]);

    }

    public function getBlockPrefix()
    {
        return null;
    }
}
