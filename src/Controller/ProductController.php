<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    


}
