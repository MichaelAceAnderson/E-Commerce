<?php

namespace App\Form;

use App\Entity\AddressType;
use App\Entity\CustomerAddress;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerAddressType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('lastName', TextType::class, [
				'label' => 'Last Name',
				'attr' => [
					'placeholder' => 'Last Name'
				],
			])
			->add('firstName', TextType::class, [
				'label' => 'First Name',
				'attr' => [
					'placeholder' => 'First Name'
				],
			])
			->add('phone', TelType::class, [
				'label' => 'Phone',
				'attr' => [
					'placeholder' => 'Phone'
				],
			])
			->add('address', TextType::class, [
				'label' => 'Address',
				'attr' => [
					'placeholder' => 'Address'
				],
			])
			->add('type', EntityType::class, [
				'class' => AddressType::class,
				'choice_label' => 'type',
				'label' => 'Address Type',
				'attr' => [
					'placeholder' => 'Type'
				],
			])
			->add('city', TextType::class, [
				'label' => 'City',
				'attr' => [
					'placeholder' => 'City'
				],
			])
			->add('postalCode', IntegerType::class, [
				'label' => 'Postal Code',
				'attr' => [
					'placeholder' => 'Postal Code'
				],
			])
			->add('country', TextType::class, [
				'label' => 'Country',
				'attr' => [
					'placeholder' => 'Country'
				],
			])
			->add('submit', SubmitType::class, [
				'label' => 'Submit Order'
			])
		;
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => CustomerAddress::class,
		]);
	}
}
