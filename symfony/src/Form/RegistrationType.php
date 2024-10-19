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
				'label' => 'Adresse email',
				'attr' => ['placeholder' => 'adresse@mail.com'],
				'required' => true,
			])
			->add('lastName', TextType::class, [
				'label' => 'Nom',
				'attr' => ['placeholder' => 'Durand'],
				'required' => true,
			])
			->add('firstName', TextType::class, [
				'label' => 'Prénom',
				'attr' => ['placeholder' => 'Martin'],
				'required' => true,
			])
			->add('phone', IntegerType::class, [
				'label' => 'N° de téléphone',
				'attr' => ['placeholder' => '0123456789'],
				'required' => true,
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
				]
			])
			->add('agreeTerms', CheckboxType::class, [
				'mapped' => false,
				'label' => 'J\'accepte les termes d\'utilisation',
				'constraints' => [
					new IsTrue([
						'message' => 'Vous devez accepter les termes d\'utilisation !',
					]),
				],
			])
            ->add('save', SubmitType::class, ['label' => 'S\'enregistrer']);
		;
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => User::class,
		]);
	}
}
