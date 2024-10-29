<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProductEditType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
		// Note: replacing *Type::class with null will automatically select
		// the correct input based on the data type of the input
		// Here, they have been specified to manually choose the input type
			->add('name', TextType::class, [
				'label' => 'Name',
				'attr' => ['autocomplete' => 'new-password', 'placeholder' => 'Product name'],
				'required' => true,
			])
			->add('description', TextareaType::class, [
				'label' => 'Description',
				'attr' => ['placeholder' => 'Product description'],
				'required' => true,
			])
			->add('price', NumberType::class, [
				'label' => 'Price',
				'attr' => ['placeholder' => 'Product price'],
				'required' => true,
			])
			->add('available', CheckboxType::class, [
				'label' => 'Availability',
				'required' => false,
			])
			->add('category', EntityType::class, [
				'class' => Category::class,
				// Determine the field to display in the dropdown list
				'choice_label' => 'name',
				'label' => 'Category',
				'placeholder' => 'Choose a category',
				'required' => true,
			])
			->add('medias', CollectionType::class, [
				'entry_type' => MediaType::class,
				'allow_add' => true,
				'allow_delete' => true,
				// To prevent Symfony from forcing the assignment of a field to a media,
				// we disable the reference to allow adding or removing media fields
				'by_reference' => false,
			])
			->add('save', SubmitType::class, ['label' => 'Save']);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Product::class,
		]);
	}
}
