<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\AsciiSlugger;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $slugger = new AsciiSlugger();

        $categories = [
            'Chips & Snacks salés'         => 'Crackers, chips, pop-corn et snacks salés du monde entier.',
            'Bonbons & Confiseries'        => 'Bonbons, gummies, caramels et confiseries internationales.',
            'Chocolats & Biscuits'         => 'Tablettes, barres chocolatées et biscuits importés.',
            'Boissons & Sodas'             => 'Sodas, jus, thés et boissons énergisantes du monde.',
            'Nouilles & Plats instantanés' => 'Ramen, nouilles instantanées et repas rapides asiatiques.',
            'Sauces & Condiments'          => 'Sauces piquantes, pâtes de curry, condiments exotiques.',
            'Snacks Asiatiques'            => 'Spécialités du Japon, Corée, Chine et Asie du Sud-Est.',
            'Snacks Américains'            => 'Classiques US : Oreo, Cheetos, Twinkies et plus.',
            'Snacks Européens'             => 'Produits d\'importation européens introuvables en France.',
            'Snacks Latino & Mexicains'    => 'Takis, Sabritas, dulces mexicanos et snacks d\'Amérique latine.',
        ];

        foreach ($categories as $name => $description) {
            $category = new Category();
            $category->setName($name);
            $category->setSlug(strtolower($slugger->slug($name)));
            $category->setDescription($description);
            $manager->persist($category);
        }

        $tags = [
            'Épicé', 'Sucré', 'Salé', 'Veggie', 'Sans gluten',
            'Halal', 'Japon', 'Corée', 'USA', 'Mexique',
            'Inde', 'Thaïlande', 'UK', 'Nouveau', 'Édition limitée',
        ];

        foreach ($tags as $name) {
            $tag = new Tag();
            $tag->setName($name);
            $manager->persist($tag);
        }

        $manager->flush();
    }
}
