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

            $this->addFlash('success', "Your article was created with success");

            return $this->redirectToRoute('app_article');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name:'app_article_edit', methods:['GET', 'POST'])]
    public function editArticle(Request $request, EntityManagerInterface $em, Article $article):Response
    {
        $user = $this->getUser();

        // if the user isn't the author or the article is published
        // the user can't access the edit form
        if($user != $article->getUser()) {
            $this->addFlash('info', "You cannot edit an article you don't own");
            return $this->redirectToRoute('app_article');

        } elseif($article->isPublished() == 1) {
            $this->addFlash('info', "You cannot edit a published article");
            return $this->redirectToRoute('app_article');
        }

        // the form has to be bellow the first if contition
        // otherwise it won't update the 'published' property of Article entity
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', "The article was edited with success");

            return $this->redirectToRoute('app_article');
        }
        
        return $this->render('article/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/unpublish', name:'app_article_unpublish')]
    public function unpublishArticle(EntityManagerInterface $em, Article $article):Response
    {
        $user = $this->getUser();

        if($user != $article->getUser()) {
            $this->addFlash('info', "You cannot unpublish an article you don't own");
            return $this->redirectToRoute('app_article');

        } else {
            $article->setPublished(0);
            $em->flush();


            $this->addFlash('success', "The article is not published anymore");
            return $this->redirectToRoute('app_article');
        }
    }

    #[Route('/{id}/publish', name:'app_article_publish')]
    public function publishArticle(EntityManagerInterface $em, Article $article):Response
    {
        $user = $this->getUser();

        if($user != $article->getUser()) {
            $this->addFlash('info', "You cannot unpublish an article you don't own");
            return $this->redirectToRoute('app_article');

        } else {
            $article->setPublished(1);
            $em->flush();

            $this->addFlash('success', "The article is published");
            return $this->redirectToRoute('app_article');
        }
    }
}