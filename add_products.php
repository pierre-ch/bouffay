<?php

require __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\ProductImage;
use App\Entity\Review;
use App\Entity\Category;
use App\Entity\Tag;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\String\Slugger\AsciiSlugger;

(new Dotenv())->bootEnv(__DIR__ . '/.env');
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();
$slugger = new AsciiSlugger();

// 1. Create a new seller
$seller = new User();
$seller->setEmail('vendeur3@bouffay.com');
$seller->setFirstName('Sophie');
$seller->setLastName('Martin');
$seller->setRoles(['ROLE_VENDEUR']);
$seller->setPassword('$2y$13$jAdf1CGk/rrwFRuK/iVGkuj.wg4eKztKziyNtfXY0sFB72AtG8XnW');
$seller->setTheme('light');
$seller->setLocale('fr');
$seller->setCreatedAt(new \DateTimeImmutable('-2 months'));

// 2. Fetch some existing buyers for reviews
$buyers = $em->getRepository(User::class)->findAll();
if (count($buyers) >= 3) {
    // Add 3 reviews for the new seller
    $review1 = new Review();
    $review1->setAuthor($buyers[0]);
    $review1->setSeller($seller);
    $review1->setRating(5);
    $review1->setContent('Vendeur fantastique, produits super originaux et très bien emballés !');
    $review1->setCreatedAt(new \DateTimeImmutable('-10 days'));
    $em->persist($review1);
    
    $review2 = new Review();
    $review2->setAuthor($buyers[1]);
    $review2->setSeller($seller);
    $review2->setRating(4);
    $review2->setContent('Très bonne sélection de snacks, je commanderai à nouveau.');
    $review2->setCreatedAt(new \DateTimeImmutable('-5 days'));
    $em->persist($review2);
    
    $review3 = new Review();
    $review3->setAuthor($buyers[2]);
    $review3->setSeller($seller);
    $review3->setRating(5);
    $review3->setContent('Livraison ultra rapide et produits conformes. Top !');
    $review3->setCreatedAt(new \DateTimeImmutable('-2 days'));
    $em->persist($review3);
}

$em->persist($seller);

// 3. Find Categories and Tags
$categoryRepo = $em->getRepository(Category::class);
$tagRepo = $em->getRepository(Tag::class);

$catChocolats = $categoryRepo->findOneBy(['name' => 'Chocolats & Biscuits']);
$catSauces = $categoryRepo->findOneBy(['name' => 'Sauces & Condiments']);
$catBoissons = $categoryRepo->findOneBy(['name' => 'Boissons & Sodas']);
$catAsie = $categoryRepo->findOneBy(['name' => 'Snacks Asiatiques']);
$catAmericains = $categoryRepo->findOneBy(['name' => 'Snacks Américains']);

$tagSucre = $tagRepo->findOneBy(['name' => 'Sucré']);
$tagSale = $tagRepo->findOneBy(['name' => 'Salé']);
$tagEpice = $tagRepo->findOneBy(['name' => 'Épicé']);
$tagUsa = $tagRepo->findOneBy(['name' => 'USA']);
$tagJapon = $tagRepo->findOneBy(['name' => 'Japon']);
$tagCoree = $tagRepo->findOneBy(['name' => 'Corée']);
$tagBoisson = $tagRepo->findOneBy(['name' => 'Boisson']);

// 4. Products Data
$productsData = [
    [
        'name' => "Reese's Peanut Butter Cups", 
        'cat' => $catChocolats, 
        'tags' => [$tagSucre, $tagUsa], 
        'price' => '2.50', 
        'origin' => 'USA', 
        'img' => 'reeses.png',
        'desc' => 'Le grand classique américain : de délicieuses coupelles de chocolat au lait fourrées au beurre de cacahuète fondant.'
    ],
    [
        'name' => 'Sriracha Hot Chili Sauce', 
        'cat' => $catSauces, 
        'tags' => [$tagEpice, $tagSale], 
        'price' => '6.90', 
        'origin' => 'USA', 
        'img' => 'sriracha.png',
        'desc' => 'La fameuse sauce piquante au piment jalapeño rouge, avec son célèbre bouchon vert. Parfaite pour relever tous vos plats !'
    ],
    [
        'name' => 'Ramune Original', 
        'cat' => $catBoissons, 
        'tags' => [$tagBoisson, $tagJapon, $tagSucre], 
        'price' => '3.50', 
        'origin' => 'Japon', 
        'img' => 'ramune.png',
        'desc' => 'La célèbre limonade japonaise avec sa bouteille en verre traditionnelle et sa bille de verre pour l\'ouvrir. Très rafraîchissant !'
    ],
    [
        'name' => 'Spicy Tteokbokki Snacks', 
        'cat' => $catAsie, 
        'tags' => [$tagEpice, $tagCoree, $tagSale], 
        'price' => '4.20', 
        'origin' => 'Corée du Sud', 
        'img' => 'tteokbokki.png',
        'desc' => 'Des snacks croustillants coréens au bon goût de Tteokbokki (gâteaux de riz) épicé et légèrement sucré. Très addictif.'
    ],
    [
        'name' => 'Jalapeño Cheddar Cheetos', 
        'cat' => $catAmericains, 
        'tags' => [$tagEpice, $tagSale, $tagUsa], 
        'price' => '4.80', 
        'origin' => 'USA', 
        'img' => 'jalapeno_cheetos.png',
        'desc' => 'Les fameux Cheetos américains au goût intense de fromage cheddar relevé par une pointe de piment jalapeño.'
    ],
];

foreach ($productsData as $data) {
    $product = new Product();
    $product->setName($data['name']);
    $product->setSlug(strtolower($slugger->slug($data['name'])) . '-' . rand(1000, 9999));
    $product->setDescription($data['desc']);
    $product->setPrice($data['price']);
    $product->setStock(rand(10, 50));
    $product->setWeight(0.2);
    $product->setOrigin($data['origin']);
    $product->setStatus('active');
    $product->setSoldCount(rand(0, 10));
    $product->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 10) . ' days'));
    $product->setCategory($data['cat']);
    $product->setSeller($seller);

    foreach ($data['tags'] as $tag) {
        if ($tag) {
            $product->addTag($tag);
        }
    }

    $image = new ProductImage();
    $image->setFilename($data['img']);
    $image->setPosition(1);
    $product->addImage($image);
    $em->persist($image);

    $em->persist($product);
}

$em->flush();
echo "New products and seller added successfully.\n";
