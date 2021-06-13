<?php

namespace App\Controller;
use App\Entity\Facture;
use App\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FactureController extends AbstractController
{
    #[Route('/facture', name: 'facture')]
    public function index(): Response
    {
        return $this->render('facture/index.html.twig', [
            'controller_name' => 'FactureController',
        ]);
    }

     /**
     * @Route("/addFacture/{id}", name="addFacture")
     */
    public function addFacture(Request $request,$id)
    {   
        $facture = new Facture();
        $order = $this->getDoctrine()->getRepository(Order::class)->find($id);
        $facture->setOrder($order);
        $facture->setDate(new \DateTime('now'));
        $em = $this->getDoctrine()->getManager();
        $em->persist($facture);
        $em->flush();
        $factures = $this->getDoctrine()->getRepository(Facture::class)->findAll();
        return $this->redirectToRoute("listFactures",array("factures" => $factures));
    }

    /**
     * @Route("/listFactures", name="listFactures")
     */
    public function listFactures()
    {
        $factures = $this->getDoctrine()->getRepository(Facture::class)->findAll();
        return $this->render('facture/index.html.twig', array("factures" => $factures));
    }

    /**
     * @Route("/deleteFacture/{id}", name="deleteFacture")
     */
    public function deleteFacture($id)
    { 
        $facture = $this->getDoctrine()->getRepository(Facture::class)->find($id);
        $productController = new ProductController();
        $productController->updateProductsQuantity($facture->getOrder()->getProducts(),"deleteAction");
        $em = $this->getDoctrine()->getManager();
        $em->remove($facture);
        $em->flush();
        return $this->redirectToRoute("listFactures");
    }
}
