<?php


namespace App\Form;


use App\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class ImageType extends AbstractType {

    private $t;

    public function __construct(TranslatorInterface $t) {
        $this->t = $t;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('image', FileType::class, [
                'label' => $this->t->trans('label.image_upload'),
            ])
            ->add('tags', TextType::class, [
                'label' => $this->t->trans('label.tags')
                , 'required' => false
            ]);
    }
}