<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class RegistrationType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('email', TextType::class, [
				'label' => 'Email address',
				'attr' => ['placeholder' => 'address@mail.com'],
				'required' => true,
			])
			->add('lastName', TextType::class, [
				'label' => 'Last name',
				'attr' => ['placeholder' => 'Doe'],
				'required' => true,
			])
			->add('firstName', TextType::class, [
				'label' => 'First name',
				'attr' => ['placeholder' => 'John'],
				'required' => true,
			])
			->add('phone', IntegerType::class, [
				'label' => 'Phone number',
				'attr' => ['placeholder' => '0123456789'],
				'required' => true,
			])
			->add('password', RepeatedType::class, [
				'type' => PasswordType::class,
				'invalid_message' => "Passwords do not match!",
				'first_options' => [
					'label' => 'Password',
					'attr' => ['placeholder' => 'Password'],
				],
				'second_options' => [
					'label' => 'Confirm password',
					'attr' => ['placeholder' => 'Confirm password'],
				]
			])
			->add('agreeTerms', CheckboxType::class, [
				'mapped' => false,
				'label' => 'I agree to the terms of use',
				'constraints' => [
					new IsTrue([
						'message' => 'You must agree to the terms of use!',
					]),
				],
			])
			->add('save', SubmitType::class, ['label' => 'Register']);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => User::class,
		]);
	}
}
