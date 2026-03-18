<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/category')]
#[IsGranted('ROLE_ADMIN')]
final class CategoryController extends AbstractController
{
    #[Route(name: 'app_category_manager')]
    public function index(CategoryRepository $categoryRepo): Response
    {
        $categoryList = $categoryRepo->findBy([], ['name' => 'ASC']);

        return $this->render('category/index.html.twig', [
            'categoryList' => $categoryList,
        ]);
    }

    #[Route('/new', name: 'app_category_new')]
    public function newCategory(Request $request, EntityManagerInterface $em):Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();

            $this->addFlash('success', "A new category was created");
            return $this->redirectToRoute('app_category_manager');
        }

        return $this->render('category/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'app_category_edit')]
    public function editCategory(Request $request, EntityManagerInterface $em, Category $category):Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', "The category was edited");
            return $this->redirectToRoute('app_category_manager');
        }

        return $this->render('category/new.html.twig', [
            'form' => $form
        ]);
    }
}