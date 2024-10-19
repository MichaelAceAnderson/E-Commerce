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
				'label' => 'Nom',
				'attr' => [
					'placeholder' => 'Nom'
				],
			])
            ->add('firstName', TextType::class, [
				'label' => 'Prénom',
				'attr' => [
					'placeholder' => 'Prénom'
				],
			])
            ->add('phone', TelType::class, [
				'label' => 'Téléphone',
				'attr' => [
					'placeholder' => 'Téléphone'
				],
			])
            ->add('address', TextType::class, [
				'label' => 'Adresse',
				'attr' => [
					'placeholder' => 'Adresse'
				],
			])
            ->add('type', EntityType::class, [
                'class' => AddressType::class,
				'choice_label' => 'type',
				'label' => 'Type d\'adresse',
				'attr' => [
					'placeholder' => 'Type'
				],
            ])
            ->add('city', TextType::class, [
				'label' => 'Ville',
				'attr' => [
					'placeholder' => 'Ville'
				],
			])
            ->add('postalCode', IntegerType::class, [
				'label' => 'Code postal',
				'attr' => [
					'placeholder' => 'Code postal'
				],
			])
            ->add('country', TextType::class, [
				'label' => 'Pays',
				'attr' => [
					'placeholder' => 'Pays'
				],
			])
			->add('submit', SubmitType::class, [
				'label' => 'Valider la commande'
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
