<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderFormType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            $this->addFlash('orderAdded', 'The order has been added');
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
        $this->addFlash('orderDeleted', 'The order has been deleted');
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
            $this->addFlash('orderUpdated', 'The order has been updated');
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

    private function getData(): array
    {
        $list = [];
        $listProducts="";
        $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();

        foreach ($orders as $order) {
            if($order->getFacture()==null){
                foreach($order->getProducts() as $product){
                $listProducts=$listProducts." \n ".$product;
                }
                $list[] = [
                    $order->getId(),
                    $order->getDate(),
                    $listProducts,
                    $order->getTotalPrice(),
                ];
            }
        }
        return $list;
    }
    
    /**
     * @Route("/exportOrders", name="exportOrders")
     */
    public function exportOrders()
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Orders List');
        $sheet->getCell('A1')->setValue('Id');
        $sheet->getCell('B1')->setValue('Date');
        $sheet->getCell('C1')->setValue('Products');
        $sheet->getCell('D1')->setValue('Total price');
        
        $sheet->fromArray($this->getData(),null, 'A2', true);

        $writer = new Xlsx($spreadsheet);
        $fileDate= new DateTime();
        $fileDate = $fileDate->format('d-m-Y');
        $writer->save('listOrders -'.$fileDate.'.xlsx');
        $this->addFlash('ExcelFileSaved', 'Excel file saved');
        return $this->redirectToRoute('listOrders');
    }
}
