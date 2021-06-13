<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function PHPUnit\Framework\equalTo;

class OrderController extends AbstractController
{   
    
    #[Route('/order', name: 'order')]
    public function index(): Response
    {
        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
        ]);
    }

    /**
     * @Route("/addOrder", name="addOrder")
     */
    public function addOrder(Request $request)
    {   
        $productController = new ProductController();
        $order = new Order();
        $form = $this->createForm(OrderFormType::class,$order);
        $form->add('Ajouter', SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $order->setDate(new \DateTime('now'));
            $totalPrice=$this->calculateTotalPrice($order->getProducts());
            $order->setTotalPrice($totalPrice);
            $productController->updateProductsQuantity($order->getProducts(),"addAction");
            $em = $this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();
            return $this->redirectToRoute('listOrders');
        }
        return $this->render('order/add.html.twig', array("form" => $form->createView()));
    }

    /**
     * @Route("/listOrders", name="listOrders")
     */
    public function listOrders()
    {
        $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();
        return $this->render('order/index.html.twig', array("orders" => $orders));
    }

    /**
     * @Route("/deleteOrder/{id}", name="deleteOrder")
     */
    public function deleteOrder($id)
    {   
        $productController = new ProductController();
        $order = $this->getDoctrine()->getRepository(Order::class)->find($id);
        $productController->updateProductsQuantity($order->getProducts(),"deleteAction");
        $em = $this->getDoctrine()->getManager();
        $em->remove($order);
        $em->flush();
        return $this->redirectToRoute("listOrders");
    }

    /**
     * @Route("/updateOrder/{id}", name="updateOrder")
     */
    public function updateOrder(Request $request,$id)
    {   
        $productController = new ProductController();
        $order = $this->getDoctrine()->getRepository(Order::class)->find($id);
        $productController->updateProductsQuantity($order->getProducts(),"deleteAction");
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(OrderFormType::class,$order);
        $form->add('Modifier', SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $totalPrice=$this->calculateTotalPrice($order->getProducts());
            $order->setTotalPrice($totalPrice);
            $productController->updateProductsQuantity($order->getProducts(),"addAction");   
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute("listOrders");
        }
        return $this->render('order/update.html.twig', array("form" => $form->createView()));

    }

    public function calculateTotalPrice($products) : float{
        $totalPrice=0;
        foreach($products as $product){
            $totalPrice=$totalPrice+$product->getPrice();
        }
        return $totalPrice;
    }
}
