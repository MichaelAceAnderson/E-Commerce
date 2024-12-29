<?php
namespace App\Form;

use App\Entity\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class MediaType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('alt', TextType::class, [
				'label' => 'Alternative Text',
				'required' => true,
				'attr' => [
					'placeholder' => 'Title/Description of the image',
				],
			])
			->add('file', FileType::class, [
				'label' => 'File',
				'required' => false,
				'attr' => [
					'accept' => '.jpg, .jpeg, .png, .gif, .svg, .webp, .mp4, .flv, .avi, .wmv, .mov, .mpg, .mpeg, .mkv, .3gp, .webm',
					'maxsize' => '8000',
				],
				'constraints' => [
					new File([
						'maxSize' => '8M',
						'maxSizeMessage' => 'The maximum file size is 8 MB.',
					]),
				]
			]);
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => Media::class,
		]);
	}
}