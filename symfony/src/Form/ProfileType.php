<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('lastName', TextType::class, [
				'label' => 'Last Name',
				'attr' => ['placeholder' => 'Last Name']
			])
			->add('firstName', TextType::class, [
				'label' => 'First Name',
				'attr' => ['placeholder' => 'First Name']
			])
			->add('phone', TelType::class, [
				'label' => 'Phone',
				'attr' => ['placeholder' => 'Phone']
			])
			->add('email', EmailType::class, [
				'label' => 'Email',
				'attr' => ['placeholder' => 'Email']
			])
			->add('password', RepeatedType::class, [
				'type' => PasswordType::class,
				'invalid_message' => "Passwords do not match!",
				'first_options' => [
					'label' => 'Password',
					'attr' => ['placeholder' => 'Password'],
				],
				'second_options' => [
					'label' => 'Confirm Password',
					'attr' => ['placeholder' => 'Confirm Password'],
				]
			])
			->add('save', SubmitType::class, [
				'label' => 'Save'
			]);
		;
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => User::class,
		]);
	}
}