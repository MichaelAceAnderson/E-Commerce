<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('email', EmailType::class, [
				'label' => 'Email',
				// 'mapped' => false,
				'required' => true,
				'attr' => [
					'placeholder' => 'email@example.com',
					'autofocus' => true
				]
			])
			->add('password', PasswordType::class, [
				'label' => 'Password',
				'required' => true,
				'attr' => [
					'placeholder' => 'Password',
					'autocomplete' => 'password'
				]
			])
			->add('submit', SubmitType::class, [
				'label' => 'Login'
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => User::class,
			// This field is necessary to secure the form request
			// The _token field is used by the authentication method
			// in src/Security/LoginFormAuthenticator.php
			'csrf_field_name' => 'auth_token',
			// This token is used by the CsrfTokenBadge in src/Security/LoginFormAuthenticator.php
			'csrf_token_id'   => 'authenticate',
		]);
	}
}
