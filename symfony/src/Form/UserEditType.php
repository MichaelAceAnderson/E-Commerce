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
				'label' => 'Nom',
				'attr' => ['placeholder' => 'Nom']
			])
			->add('firstName', TextType::class, [
				'label' => 'Prénom',
				'attr' => ['placeholder' => 'Prénom']
			])
			->add('phone', TelType::class, [
				'label' => 'Téléphone',
				'attr' => ['placeholder' => 'Téléphone']
			])
			->add('email', EmailType::class, [
				'label' => 'Email',
				'attr' => ['placeholder' => 'Email']
			])
			->add('password', RepeatedType::class, [
				'type' => PasswordType::class,
				'invalid_message' => "Les mots de passe ne correspondent pas !",
				'first_options' => [
					'label' => 'Mot de passe',
					'attr' => ['placeholder' => 'Mot de passe'],
				],
				'second_options' => [
					'label' => 'Confirmer le mot de passe',
					'attr' => ['placeholder' => 'Confirmer le mot de passe'],
				],
			])
			->add('roles', ChoiceType::class, [
				'label' => 'Rôles',
				'choices' => [
					'Utilisateur' => 'ROLE_USER',
					'Administrateur' => 'ROLE_ADMIN',
				],
				'expanded' => false,
				'multiple' => false
			])
			->add('isVerified', CheckboxType::class, [
				'label' => 'Compte vérifié',
				'required' => false
			])
			->add('save', SubmitType::class, ['label' => 'Sauvegarder']);
		;

		// Nécessaire pour transformer le tableau des rôles en chaîne de caractères à afficher et vice-versa
		$builder->get('roles')
			->addModelTransformer(
				new CallbackTransformer(
					function ($rolesArray) {
						// Transforme le tableau en chaîne de caractères
						return is_array($rolesArray) ? (count($rolesArray) ? $rolesArray[0] : null) : '';
					},
					function ($rolesString) {
						// Transforme la chaîne de caractères en tableau
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