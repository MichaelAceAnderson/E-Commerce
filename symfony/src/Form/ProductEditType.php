<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Media;
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
		// Note: le fait de remplacer *Type::class par null sélectionnera automatiquement
		// le bon input à partir du type de données concerné par l'input
		// Ici, ils ont été renseignés pour pouvoir choisir manuellement le type d'input
            ->add('name', TextType::class, [
				'label' => 'Nom',
                'attr' => ['autocomplete' => 'new-password', 'placeholder' => 'Nom du produit'],
				'required' => true,
			])
			->add('description', TextareaType::class, [
				'label' => 'Description',
				'attr' => ['placeholder' => 'Description du produit'],
				'required' => true,
			])
            ->add('price', NumberType::class, [
				'label' => 'Prix',
				'attr' => ['placeholder' => 'Prix du produit'],
				'required' => true,
			])
            ->add('available', CheckboxType::class, [
				'label' => 'Disponibilité',
				'required' => false,
			])
            ->add('category', EntityType::class, [
                'class' => Category::class,
				// Déterminer le champ à afficher dans la liste déroulante
				'choice_label' => 'name',
				'label' => 'Catégorie',
				'placeholder' => 'Choisir une catégorie',
				'required' => true,
            ])
			->add('medias', CollectionType::class, [
				'entry_type' => MediaType::class,
				'allow_add' => true,
				'allow_delete' => true,
				// Pour éviter que Symfony force l'attribution d'un champ à un média,
				// on désactive la référence pour permettre d'ajouter ou supprimer des champs de médias
				'by_reference' => false,
			])
            ->add('save', SubmitType::class, ['label' => 'Sauvegarder']);

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
