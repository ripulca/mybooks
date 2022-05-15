<?php

namespace App\Form;

use App\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('author')
            ->add('cover', FileType::class, [
                'label' => 'cover (img file)',

                // неотображенное означает, что это поле не ассоциировано ни с одним свойством сущности
                'mapped' => false,

                // сделайте его необязательным, чтобы вам не нужно было повторно загружать PDF-файл
                // каждый раз, когда будете редактировать детали Product
                'required' => false,

                // неотображенные полля не могут определять свою валидацию используя аннотации
                // в ассоциированной сущности, поэтому вы можете использовать органичительные классы PHP
                'constraints' => [
                    new File([
                        'maxSize' => '20024k',
                        'mimeTypes' => [
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid jpeg/jpg/png document',
                    ])
                ],
            ])
            ->add('file', FileType::class, [
                'label' => 'file (PDF file)',

                // неотображенное означает, что это поле не ассоциировано ни с одним свойством сущности
                'mapped' => false,

                // сделайте его необязательным, чтобы вам не нужно было повторно загружать PDF-файл
                // каждый раз, когда будете редактировать детали Product
                'required' => false,

                // неотображенные полля не могут определять свою валидацию используя аннотации
                // в ассоциированной сущности, поэтому вы можете использовать органичительные классы PHP
                'constraints' => [
                    new File([
                        'maxSize' => '100024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],
            ])
            ->add('last_reading_date')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
