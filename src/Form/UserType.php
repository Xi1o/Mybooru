<?php


namespace App\Form;


use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class UserType extends AbstractType {
    private $t;

    public function __construct(TranslatorInterface $t) {
        $this->t = $t;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
           'data_class' => User::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('email', TextType::class, [
                'label' => $this->t->trans('label.email'),
                'attr' => [
                    'placeholder' => $this->t->trans('label.email'),
                    'class' => 'form-control',
                ],
            ])
            ->add('username', TextType::class, [
                'label' => $this->t->trans('label.username'),
                'attr' => [
                    'placeholder' => $this->t->trans('label.username'),
                    'class' => 'form-control',
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => $this->t->trans('error.passwords_dont_match'),
                'first_options'  => [
                    'label' => $this->t->trans('label.password'),
                    'attr' => ['placeholder' => $this->t->trans('label.password'), 'class' => 'form-control'],
                ],
                'second_options' => [
                    'label' => $this->t->trans('label.password_repeat'),
                    'attr' => ['placeholder' => $this->t->trans('label.password_repeat'), 'class' => 'form-control'],
                ],
            ]);
    }
}