<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Form\ProductFormType;
use App\Form\ProductSearchType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductController extends AbstractController
{
    #[Route('/produits', name: 'app_product_index')]
    public function index(Request $request, ProductRepository $repository): Response
    {
        $form = $this->createForm(ProductSearchType::class, ['sort' => 'trending']);
        $form->handleRequest($request);

        $filters = $form->isSubmitted() && $form->isValid()
            ? $form->getData()
            : ['sort' => 'trending'];

        return $this->render('product/index.html.twig', [
            'products'    => $repository->findByFilters($filters),
            'searchForm'  => $form,
        ]);
    }

    #[Route('/produits/{slug}', name: 'app_product_show')]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/mes-annonces', name: 'app_product_my')]
    #[IsGranted('ROLE_VENDEUR')]
    public function myProducts(ProductRepository $repository): Response
    {
        return $this->render('product/my_products.html.twig', [
            'products' => $repository->findBy(['seller' => $this->getUser()], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/mes-annonces/nouvelle', name: 'app_product_new')]
    #[IsGranted('ROLE_VENDEUR')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
    ): Response {
        $product = new Product();
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setSeller($this->getUser());
            $product->setSlug($this->generateSlug($slugger, $product->getName()));
            $product->setCreatedAt(new \DateTimeImmutable());

            $this->handleImageUploads($form->get('imageFiles')->getData(), $product, $em);

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'flash.ad_published');

            return $this->redirectToRoute('app_product_show', ['slug' => $product->getSlug()]);
        }

        return $this->render('product/new.html.twig', ['form' => $form]);
    }

    #[Route('/mes-annonces/{id}/modifier', name: 'app_product_edit')]
    #[IsGranted('ROLE_VENDEUR')]
    public function edit(
        Product $product,
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
    ): Response {
        if ($product->getSeller() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setSlug($this->generateSlug($slugger, $product->getName()));
            $product->setUpdateAt(new \DateTime());

            $this->handleImageUploads($form->get('imageFiles')->getData(), $product, $em);

            $em->flush();

            $this->addFlash('success', 'flash.ad_updated');

            return $this->redirectToRoute('app_product_show', ['slug' => $product->getSlug()]);
        }

        return $this->render('product/edit.html.twig', ['form' => $form, 'product' => $product]);
    }

    #[Route('/mes-annonces/{id}/supprimer', name: 'app_product_delete', methods: ['POST'])]
    #[IsGranted('ROLE_VENDEUR')]
    public function delete(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        if ($product->getSeller() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete-product-' . $product->getId(), $request->request->get('_token'))) {
            $em->remove($product);
            $em->flush();
            $this->addFlash('success', 'flash.ad_deleted');
        }

        return $this->redirectToRoute('app_product_my');
    }

    private function generateSlug(SluggerInterface $slugger, string $name): string
    {
        return strtolower($slugger->slug($name)) . '-' . uniqid();
    }

    private function handleImageUploads(array $files, Product $product, EntityManagerInterface $em): void
    {
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/products';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $position = count($product->getImages());

        foreach (array_slice($files, 0, 5) as $file) {
            $filename = uniqid() . '.' . $file->guessExtension();
            $file->move($uploadDir, $filename);

            $image = new ProductImage();
            $image->setFilename($filename);
            $image->setPosition(++$position);
            $image->setProduct($product);

            $em->persist($image);
            $product->addImage($image);
        }
    }
}
