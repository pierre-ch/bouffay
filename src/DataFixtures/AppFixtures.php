<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $faker   = Factory::create('fr_FR');
        $slugger = new AsciiSlugger();

        // --- Catégories ---
        $categoriesData = [
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

        $categories = [];
        foreach ($categoriesData as $name => $description) {
            $category = new Category();
            $category->setName($name);
            $category->setSlug(strtolower($slugger->slug($name)));
            $category->setDescription($description);
            $manager->persist($category);
            $categories[] = $category;
        }

        // --- Tags ---
        $tagsData = [
            'Épicé', 'Sucré', 'Salé', 'Veggie', 'Sans gluten',
            'Halal', 'Japon', 'Corée', 'USA', 'Mexique',
            'Inde', 'Thaïlande', 'UK', 'Nouveau', 'Édition limitée',
        ];

        $tags = [];
        foreach ($tagsData as $name) {
            $tag = new Tag();
            $tag->setName($name);
            $manager->persist($tag);
            $tags[] = $tag;
        }

        // --- Vendeurs ---
        $sellers = [];
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setEmail($faker->unique()->safeEmail());
            $user->setRoles(['ROLE_VENDEUR']);
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
            $user->setTheme('light');
            $user->setLocale('fr');
            $user->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($user);
            $sellers[] = $user;
        }

        // --- Produits ---
        $productNames = [
            'Takis Fuego', 'Oreo Golden', 'Cheetos Flamin Hot', 'Pocky Matcha',
            'Ramen Shin Noodle', 'Doritos Cool Ranch', 'Kit Kat Sakura', 'Pringles BBQ',
            'Hi-Chew Strawberry', 'Haribo Goldbären', 'Lays Wasabi', 'Pepero Almond',
            'Indomie Goreng', 'Twinkies Original', 'Buldak Ramen', 'Sour Patch Kids',
            'Mochi Ice Cream', 'Spam Lite', 'Haw Flakes', 'Tiger Biscuits',
            'Kinder Bueno White', 'Monster Energy Ultra', 'Jarritos Mandarin',
            'Flamin Hot Cheetos Puffs', 'Meiji Melty Kiss', 'Calbee Shrimp Crackers',
            'Skittles Tropical', 'Starburst Original', 'Nongshim Bowl', 'Pejoy Chocolate',
        ];

        $origins = ['Japon', 'Corée du Sud', 'USA', 'Mexique', 'Allemagne', 'UK', 'Thaïlande', 'Inde', 'Chine'];

        $createdProducts = [];
        foreach ($productNames as $name) {
            $product = new Product();
            $product->setName($name);
            $product->setSlug(strtolower($slugger->slug($name)) . '-' . $faker->unique()->numerify('####'));
            $product->setDescription($faker->paragraph(2));
            $product->setPrice((string) $faker->randomFloat(2, 1.5, 25));
            $product->setStock($faker->numberBetween(0, 50));
            $product->setWeight($faker->randomFloat(1, 0.1, 2.5));
            $product->setOrigin($faker->randomElement($origins));
            $product->setStatus('active');
            $product->setSoldCount($faker->numberBetween(0, 500));
            $product->setCreatedAt(new \DateTimeImmutable($faker->dateTimeBetween('-6 months')->format('Y-m-d')));

            // Date d'expiration : ~70% des produits en ont une (certaines dans le passé, certaines dans le futur)
            if ($faker->boolean(70)) {
                $product->setExpiresAt($faker->dateTimeBetween('-1 month', '+18 months'));
            }

            $product->setSeller($faker->randomElement($sellers));
            $product->setCategory($faker->randomElement($categories));

            // 1 à 3 tags aléatoires
            $shuffled = $faker->randomElements($tags, $faker->numberBetween(1, 3));
            foreach ($shuffled as $tag) {
                $product->addTag($tag);
            }

            $manager->persist($product);
            $createdProducts[] = $product;
        }

        // --- Clients (Acheteurs) & Adresses ---
        $buyers = [];
        $addresses = [];
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setEmail($faker->unique()->safeEmail());
            $user->setRoles(['ROLE_CLIENT']);
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
            $user->setTheme('light');
            $user->setLocale('fr');
            $user->setCreatedAt(new \DateTimeImmutable($faker->dateTimeBetween('-1 year')->format('Y-m-d H:i:s')));
            $manager->persist($user);
            $buyers[] = $user;

            $address = new \App\Entity\Address();
            $address->setStreet($faker->streetAddress());
            $address->setCity($faker->city());
            // max 10 chars for zipcode
            $address->setZipCode(substr($faker->postcode(), 0, 10));
            $address->setCountry('France');
            $address->setIsDefault(true);
            $address->setUser($user);
            $manager->persist($address);
            $addresses[$user->getId() ?? spl_object_id($user)] = $address;
        }

        // --- Commandes ---
        $statuses = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
        for ($i = 0; $i < 25; $i++) {
            $order = new \App\Entity\Order();
            $buyer = $faker->randomElement($buyers);
            $order->setBuyer($buyer);
            $order->setAddress($addresses[spl_object_id($buyer)]);
            $order->setCreatedAt(new \DateTimeImmutable($faker->dateTimeBetween('-6 months')->format('Y-m-d H:i:s')));

            $numItems = $faker->numberBetween(1, 4);
            $total = 0;
            $orderProducts = $faker->randomElements($createdProducts, $numItems);
            
            foreach ($orderProducts as $product) {
                $item = new \App\Entity\OrderItem();
                $item->setProduct($product);
                $item->setQuantity($faker->numberBetween(1, 3));
                $item->setUnitPrice($product->getPrice());
                $item->setStatus($faker->randomElement($statuses));
                
                $order->addOrderItem($item);
                $manager->persist($item);
                
                $total += $item->getQuantity() * $product->getPrice();
            }
            
            $order->setTotalPrice((string) $total);
            $manager->persist($order);
        }

        $manager->flush();
    }
}
