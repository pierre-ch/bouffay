<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('buyer'),
            NumberField::new('totalPrice'),
            ChoiceField::new('globalStatus')->setChoices([
                'En attente' => 'pending',
                'Payé' => 'paid',
                'Expédié' => 'shipped',
                'Livré' => 'delivered',
                'Annulé' => 'cancelled'
            ]),
            DateTimeField::new('createdAt')->hideOnForm(),
        ];
    }
}
