<?php

namespace App\Form;

use App\Entity\Movie;
use App\Entity\Request;
use App\Repository\MovieRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // the movies must be ones that are not already requested
        // for each movie, we need to join the request table and check if there is at least a request with status 1 or 2
        // if there is, we don't want to show it

        $builder
            ->add('movie', null, [
                'query_builder' => function (MovieRepository $movieRepository) {
                    return $movieRepository->createQueryBuilder('m')
                        // where either has no request or has a request(s) with status 3
                        ->leftJoin('m.requests', 'r')
                        ->andWhere('r.status = 3 OR r.id IS NULL')
                        ->orderBy('m.title', 'ASC');
                }]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Request::class,
        ]);
    }
}
