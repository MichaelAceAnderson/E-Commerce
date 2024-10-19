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
                    'placeholder' => 'adresse@mail.com',
                    'autofocus' => true
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Mot de passe',
					'autocomplete' => 'password'
				]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Connexion'
            ]);
    }

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => User::class,
			// Ce champ est nécessaire pour sécuriser la requête du formulaire
			// Le champ _token est utilisé par la méthode d'authentification
			// dans src/Security/LoginFormAuthenticator.php
            'csrf_field_name' => 'auth_token',
			// Ce token est utilisé par le badge CsrfTokenBadge dans src/Security/LoginFormAuthenticator.php
            'csrf_token_id'   => 'authenticate',
		]);
	}
}
