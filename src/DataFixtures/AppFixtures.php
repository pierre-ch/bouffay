<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Category;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\ProductImage;
use App\Entity\Review;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $slugger = new AsciiSlugger();

        // 1. Create Categories
        $categoriesData = [
            'Snacks Américains' => 'Classiques US : Oreo, Cheetos, Twinkies et plus.',
            'Snacks Asiatiques' => 'Spécialités du Japon, Corée, Chine et Asie du Sud-Est.',
            'Snacks Latino & Mexicains' => 'Takis, Sabritas, dulces mexicanos et snacks d\'Amérique latine.',
            'Boissons & Sodas' => 'Sodas, jus, thés et boissons énergisantes du monde.',
            'Bonbons & Confiseries' => 'Bonbons, gummies, caramels et confiseries internationales.',
            'Nouilles & Plats instantanés' => 'Ramen, nouilles instantanées et repas rapides asiatiques.',
            'Chocolats & Biscuits' => 'Tablettes, barres chocolatées et biscuits importés.',
            'Sauces & Condiments' => 'Sauces piquantes, pâtes de curry, condiments exotiques.',
        ];
        $categories = [];
        foreach ($categoriesData as $name => $desc) {
            $cat = new Category();
            $cat->setName($name);
            $cat->setSlug(strtolower($slugger->slug($name)));
            $cat->setDescription($desc);
            $manager->persist($cat);
            $categories[$name] = $cat;
        }

        // 2. Create Tags
        $tagsData = ['Épicé', 'Sucré', 'Salé', 'USA', 'Japon', 'Mexique', 'Corée', 'Boisson'];
        $tags = [];
        foreach ($tagsData as $name) {
            $tag = new Tag();
            $tag->setName($name);
            $manager->persist($tag);
            $tags[$name] = $tag;
        }

        // 3. Create Users
        $users = [];

        // Admin
        $admin = new User();
        $admin->setEmail('admin@bouffay.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('Bouffay');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'password'));
        $admin->setTheme('light');
        $admin->setLocale('fr');
        $admin->setCreatedAt(new \DateTimeImmutable('-1 year'));
        $manager->persist($admin);

        // Sellers
        $sellersData = [
            ['email' => 'vendeur1@bouffay.com', 'first' => 'Jean', 'last' => 'Dupont'],
            ['email' => 'vendeur2@bouffay.com', 'first' => 'Maria', 'last' => 'Garcia'],
        ];
        $sellers = [];
        foreach ($sellersData as $data) {
            $seller = new User();
            $seller->setEmail($data['email']);
            $seller->setFirstName($data['first']);
            $seller->setLastName($data['last']);
            $seller->setRoles(['ROLE_VENDEUR']);
            $seller->setPassword($this->hasher->hashPassword($seller, 'password'));
            $seller->setTheme('light');
            $seller->setLocale('fr');
            $seller->setCreatedAt(new \DateTimeImmutable('-6 months'));
            $manager->persist($seller);
            $sellers[] = $seller;
            $users[] = $seller;
        }

        // Buyers
        $buyersData = [
            ['email' => 'client1@bouffay.com', 'first' => 'Alice', 'last' => 'Martin'],
            ['email' => 'client2@bouffay.com', 'first' => 'Bob', 'last' => 'Léponge'],
            ['email' => 'client3@bouffay.com', 'first' => 'Charlie', 'last' => 'Chaplin'],
        ];
        $buyers = [];
        $addresses = [];
        foreach ($buyersData as $data) {
            $buyer = new User();
            $buyer->setEmail($data['email']);
            $buyer->setFirstName($data['first']);
            $buyer->setLastName($data['last']);
            $buyer->setRoles(['ROLE_CLIENT']);
            $buyer->setPassword($this->hasher->hashPassword($buyer, 'password'));
            $buyer->setTheme('light');
            $buyer->setLocale('fr');
            $buyer->setCreatedAt(new \DateTimeImmutable('-3 months'));
            $manager->persist($buyer);
            $buyers[] = $buyer;
            $users[] = $buyer;

            $addr = new Address();
            $addr->setStreet('123 rue de la Paix');
            $addr->setCity('Paris');
            $addr->setZipCode('75001');
            $addr->setCountry('France');
            $addr->setIsDefault(true);
            $addr->setUser($buyer);
            $manager->persist($addr);
            $addresses[] = $addr;
        }

        // 4. Create Coherent Products
        $productsData = [
            ['name' => 'Takis Fuego', 'cat' => 'Snacks Latino & Mexicains', 'tags' => ['Épicé', 'Salé', 'Mexique'], 'price' => '4.50', 'origin' => 'Mexique', 'img' => 'takis.png'],
            ['name' => 'Oreo Golden', 'cat' => 'Snacks Américains', 'tags' => ['Sucré', 'USA'], 'price' => '3.00', 'origin' => 'USA', 'img' => 'oreo.png'],
            ['name' => 'Cheetos Flamin Hot', 'cat' => 'Snacks Américains', 'tags' => ['Épicé', 'Salé', 'USA'], 'price' => '5.50', 'origin' => 'USA', 'img' => 'cheetos.png'],
            ['name' => 'Pocky Matcha', 'cat' => 'Snacks Asiatiques', 'tags' => ['Sucré', 'Japon'], 'price' => '2.50', 'origin' => 'Japon', 'img' => 'pocky.png'],
            ['name' => 'Ramen Shin Noodle', 'cat' => 'Nouilles & Plats instantanés', 'tags' => ['Épicé', 'Salé', 'Corée'], 'price' => '1.50', 'origin' => 'Corée du Sud', 'img' => 'ramen.png'],
            ['name' => 'Kit Kat Sakura', 'cat' => 'Chocolats & Biscuits', 'tags' => ['Sucré', 'Japon'], 'price' => '6.00', 'origin' => 'Japon', 'img' => 'kitkat.png'],
            ['name' => 'Monster Energy Ultra', 'cat' => 'Boissons & Sodas', 'tags' => ['Boisson', 'USA'], 'price' => '2.80', 'origin' => 'USA', 'img' => 'monster.png'],
            ['name' => 'Sour Patch Kids', 'cat' => 'Bonbons & Confiseries', 'tags' => ['Sucré', 'USA'], 'price' => '3.20', 'origin' => 'USA', 'img' => 'sourpatch.png'],
            ['name' => 'Mochi Ice Cream', 'cat' => 'Snacks Asiatiques', 'tags' => ['Sucré', 'Japon'], 'price' => '4.00', 'origin' => 'Japon', 'img' => 'mochi.png'],
            ['name' => 'Twinkies Original', 'cat' => 'Snacks Américains', 'tags' => ['Sucré', 'USA'], 'price' => '3.50', 'origin' => 'USA', 'img' => 'twinkies.png'],
            ['name' => 'Buldak Ramen', 'cat' => 'Nouilles & Plats instantanés', 'tags' => ['Épicé', 'Salé', 'Corée'], 'price' => '2.00', 'origin' => 'Corée du Sud', 'img' => 'buldak.png'],
            ['name' => 'Doritos Cool Ranch', 'cat' => 'Snacks Américains', 'tags' => ['Salé', 'USA'], 'price' => '3.80', 'origin' => 'USA', 'img' => 'doritos.png'],
        ];

        $products = [];
        foreach ($productsData as $i => $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setSlug(strtolower($slugger->slug($data['name'])) . '-' . rand(1000, 9999));
            $product->setDescription('Délicieux produit importé directement de ' . $data['origin'] . ' !');
            $product->setPrice($data['price']);
            $product->setStock(rand(5, 50));
            $product->setWeight(0.5);
            $product->setOrigin($data['origin']);
            $product->setStatus('active');
            $product->setSoldCount(rand(0, 20));
            $product->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 30) . ' days'));
            $product->setCategory($categories[$data['cat']]);
            $product->setSeller($sellers[$i % count($sellers)]);

            foreach ($data['tags'] as $tagName) {
                $product->addTag($tags[$tagName]);
            }

            // Image
            $image = new ProductImage();
            $image->setFilename($data['img']);
            $image->setPosition(1);
            $product->addImage($image);
            $manager->persist($image);

            $manager->persist($product);
            $products[] = $product;
        }

        // 5. Create Orders (and link reviews)
        $statuses = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
        for ($i = 0; $i < 15; $i++) {
            $order = new Order();
            $buyer = $buyers[array_rand($buyers)];
            $address = null;
            foreach ($addresses as $addr) {
                if ($addr->getUser() === $buyer) {
                    $address = $addr;
                    break;
                }
            }
            $order->setBuyer($buyer);
            $order->setAddress($address);
            $order->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 60) . ' days'));

            $numItems = rand(1, 3);
            $total = 0;
            $orderProducts = (array) array_rand(array_flip(array_map(fn($p) => spl_object_id($p), $products)), $numItems);
            
            $globalStatus = $statuses[array_rand($statuses)];
            foreach ($orderProducts as $productId) {
                $product = null;
                foreach ($products as $p) {
                    if (spl_object_id($p) === $productId) {
                        $product = $p;
                        break;
                    }
                }

                $item = new OrderItem();
                $item->setProduct($product);
                $item->setQuantity(rand(1, 3));
                $item->setUnitPrice($product->getPrice());
                $item->setStatus($globalStatus);
                
                $order->addOrderItem($item);
                $manager->persist($item);
                
                $total += $item->getQuantity() * $product->getPrice();

                // If delivered, maybe add a review for the seller!
                if ($globalStatus === 'delivered' && rand(1, 100) > 30) {
                    // Check if buyer already reviewed this seller
                    $alreadyReviewed = false;
                    foreach ($product->getSeller()->getReviewsReceived() as $rev) {
                        if ($rev->getAuthor() === $buyer) {
                            $alreadyReviewed = true;
                            break;
                        }
                    }
                    if (!$alreadyReviewed) {
                        $review = new Review();
                        $review->setAuthor($buyer);
                        $review->setSeller($product->getSeller());
                        $review->setRating(rand(3, 5));
                        $review->setContent('Super vendeur, envoi rapide et soigné !');
                        $review->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 10) . ' days'));
                        $manager->persist($review);
                        
                        // Necessary for inverse side without flush
                        $product->getSeller()->addReviewReceived($review);
                    }
                }
            }
            
            $order->setTotalPrice((string) $total);
            // In case the Order entity has globalStatus setter (we saw ChoiceField globalStatus in admin)
            if (method_exists($order, 'setGlobalStatus')) {
                $order->setGlobalStatus($globalStatus);
            }
            
            $manager->persist($order);
        }

        $manager->flush();
    }
}
