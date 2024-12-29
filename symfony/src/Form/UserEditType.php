<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEditType extends AbstractType
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
				],
			])
			->add('roles', ChoiceType::class, [
				'label' => 'Roles',
				'choices' => [
					'User' => 'ROLE_USER',
					'Administrator' => 'ROLE_ADMIN',
				],
				'expanded' => false,
				'multiple' => false
			])
			->add('isVerified', CheckboxType::class, [
				'label' => 'Verified Account',
				'required' => false
			])
			->add('save', SubmitType::class, ['label' => 'Save']);
		;

		// Necessary to transform the roles array into a string to display and vice versa
		$builder->get('roles')
			->addModelTransformer(
				new CallbackTransformer(
					function ($rolesArray) {
						// Transforms the array into a string
						return is_array($rolesArray) ? (count($rolesArray) ? $rolesArray[0] : null) : '';
					},
					function ($rolesString) {
						// Transforms the string into an array
						return [$rolesString];
					}
				)
			);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => User::class,
		]);
	}
}