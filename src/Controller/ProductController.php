<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'product')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }

    public function home(): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }
    

    /**
     * @Route("/listProducts", name="listProducts")
     */
    public function listProducts()
    {
        $products = $this->getDoctrine()->getRepository(Product::class)->findAll();
        return $this->render('product/index.html.twig', array("products" => $products));
    }

    /**
     * @Route("/addCategory", name="addCategory")
     */
    public function addProduct(Request $request)
    {   
        $product = new Product();
        $form = $this->createForm(ProductFormType::class,$product);
        $form->add('Ajouter', SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            return $this->redirectToRoute('listProducts');
        }
        return $this->render('product/add.html.twig', array("form" => $form->createView()));
    }

    /**
     * @Route("/deleteProduct/{id}", name="deleteProduct")
     */
    public function deleteProduct($id)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();
        return $this->redirectToRoute("listProducts");
    }

    /**
     * @Route("/updateProduct/{id}", name="updateProduct")
     */
    public function updateProduct(Request $request,$id)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(ProductFormType::class,$product);
        $form->add('Modifier', SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute("listProducts");
        }
        return $this->render('product/add.html.twig', array("form" => $form->createView()));

    }
    


}
