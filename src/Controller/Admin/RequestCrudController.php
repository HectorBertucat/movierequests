<?php

namespace App\Controller\Admin;

use App\Entity\Request;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RequestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Request::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('movie'),

            // set date created to read only
            // set the date_created to the current date and time
            DateTimeField::new('date_created', 'Date created')
                ->setFormTypeOption('data', new \DateTime()),
            //    ->setFormTypeOption('disabled', true),

            DateTimeField::new('date_fullfilled', 'Date fullfilled'),

            // status is a option field, 1 = 'En attente', 2 = 'Complétée', 3 = 'Refusée', use ChoicesField, default is 1
            ChoiceField::new('status', 'Status')
                ->setChoices([
                    'En attente' => 1,
                    'Complétée' => 2,
                    'Refusée' => 3,
                ])
                ->setFormTypeOption('data', 1),

            AssociationField::new('madeBy'),
        ];
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
