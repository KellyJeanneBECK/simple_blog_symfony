<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/article')]
#[IsGranted('ROLE_USER')]
final class ArticleController extends AbstractController
{
    #[Route(name: 'app_article')]
    public function index(ArticleRepository $articleRepo): Response
    {
        $user = $this->getUser();
        $articleList = $articleRepo->findBy(['user'=>$user], ['title'=>'ASC']);

        return $this->render('article/index.html.twig', [
            'articleList' => $articleList
        ]);
    }

    #[Route('/new', name:'app_article_new', methods: ['GET', 'POST'])]
    public function newArticle(Request $request, EntityManagerInterface $em): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        $user = $this->getUser();

        if($form->isSubmitted() && $form->isValid()) {
            $article->setCreatedAt(new DateTimeImmutable());
            $article->setUser($user);

            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('app_article');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form,
        ]);
    }
}