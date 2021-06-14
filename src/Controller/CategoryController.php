<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\CategoryFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'category')]
    public function index(): Response
    {
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

    /**
     * @Route("/addCategory", name="addCategory")
     */
    public function addCategory(Request $request)
    {   
        $category = new Category();
        $form = $this->createForm(CategoryFormType::class,$category);
        $form->add('Ajouter', SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            $this->addFlash('categoryAdded', 'The category has been added');
            return $this->redirectToRoute('listCategories');
        }
        return $this->render('category/add.html.twig', array("form" => $form->createView()));
    }

    /**
     * @Route("/listCategories", name="listCategories")
     */
    public function listCategories()
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        return $this->render('category/index.html.twig', array("categories" => $categories));
    }

    /**
     * @Route("/deleteCategory/{id}", name="deleteCategory")
     */
    public function deleteCategory($id)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);
        $products= $this->getDoctrine()->getRepository(Product::class)->findAll();
        foreach($products as $product){
            if($product->getCategory() == $category){
                $this->addFlash('categoryDeleteError', 'This category can not be deleted, it is already used by some products !');
            return $this->redirectToRoute("listCategories");
            }
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();
        $this->addFlash('categoryDeleted', 'The category has been deleted');
        return $this->redirectToRoute("listCategories");
    }

    /**
     * @Route("/updateCategory/{id}", name="updateCategory")
     */
    public function updateCategory(Request $request,$id)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(CategoryFormType::class,$category);
        $form->add('Modifier', SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('categoryUpdated', 'The category has been updated');
            return $this->redirectToRoute("listCategories");
        }
        return $this->render('category/update.html.twig', array("form" => $form->createView()));

    }
}
