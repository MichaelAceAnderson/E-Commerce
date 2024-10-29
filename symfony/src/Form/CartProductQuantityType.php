<?php

namespace App\Form;

use App\Entity\CartProductQuantity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartProductQuantityType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('quantity', IntegerType::class, [
				'label' => 'Quantity',
			])
			->add('submit', SubmitType::class, [
				'label' => 'Update Quantity',
			])
		;
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => CartProductQuantity::class,
		]);
	}
}
