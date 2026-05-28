<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\User;
use App\Form\ReviewFormType;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SellerController extends AbstractController
{
    #[Route('/vendeurs/{id}', name: 'app_seller_show', requirements: ['id' => '\d+'])]
    public function show(
        User $seller,
        Request $request,
        ProductRepository $productRepo,
        ReviewRepository $reviewRepo,
        EntityManagerInterface $em,
    ): Response {
        $currentUser = $this->getUser();
        $canReview   = false;
        $userReview  = null;
        $form = null;

        if ($currentUser && $currentUser !== $seller) {
            $userReview = $reviewRepo->findOneBy(['author' => $currentUser, 'seller' => $seller]);
            $canReview = !$userReview;

            if ($canReview) {
                $review = new Review();
                $form = $this->createForm(ReviewFormType::class, $review);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $review->setAuthor($currentUser);
                    $review->setSeller($seller);
                    $review->setCreatedAt(new \DateTimeImmutable());

                    $em->persist($review);
                    $em->flush();

                    $this->addFlash('success', 'Votre avis a été publié.');

                    return $this->redirectToRoute('app_seller_show', ['id' => $seller->getId()]);
                }
            }
        }

        return $this->render('seller/show.html.twig', [
            'seller'          => $seller,
            'products'        => $productRepo->findBy(['seller' => $seller, 'status' => 'active'], ['createdAt' => 'DESC']),
            'reviews'         => $reviewRepo->findBy(['seller' => $seller], ['createdAt' => 'DESC']),
            'averageRating'   => $reviewRepo->getAverageRating($seller),
            'userReview'      => $userReview,
            'form'            => $form,
        ]);
    }

    #[Route('/avis/{id}/supprimer', name: 'app_review_delete', methods: ['POST'])]
    public function deleteReview(Review $review, Request $request, EntityManagerInterface $em): Response
    {
        if ($review->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('delete-review-' . $review->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $sellerId = $review->getSeller()->getId();
        $em->remove($review);
        $em->flush();

        $this->addFlash('success', 'Votre avis a été supprimé.');

        return $this->redirectToRoute('app_seller_show', ['id' => $sellerId]);
    }
}
