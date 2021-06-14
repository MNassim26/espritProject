<?php


namespace App\Controller;


use App\Entity\Product;
use App\Form\ProductFormType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


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
            $this->addFlash('productAdded', 'The product has been added');
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
        if($product->getOrders() !=null){
                $this->addFlash('productDeleteError', 'This product can not be deleted, it is already ordered !');
                return $this->redirectToRoute("listProducts");
        } 
        else{
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();
            $this->addFlash('productDeleted', 'The product has been deleted');
            return $this->redirectToRoute("listProducts");
        }
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
            $this->addFlash('productUpdated', 'The product has been updated');
            return $this->redirectToRoute("listProducts");
        }
        return $this->render('product/update.html.twig', array("form" => $form->createView()));

    }

    public function updateProductsQuantity($products, $action){
        if($action =="deleteAction"){
            foreach($products as $product){
                $product->setQuantity($product->getQuantity()+1);
            }
        } elseif($action=="addAction"){
            foreach($products as $product){
                $product->setQuantity($product->getQuantity()-1);
            } 
        }
    }

    private function getData(): array
    {
        $list = [];
        $products = $this->getDoctrine()->getRepository(Product::class)->findAll();

        foreach ($products as $product) {
            $list[] = [
                $product->getId(),
                $product->getName(),
                $product->getPrice(),
                $product->getQuantity(),
                $product->getCategory(),
                $product->getSupplier()
            ];
        }
        return $list;
    }
    
    /**
     * @Route("/exportProducts", name="exportProducts")
     */
    public function exportProducts()
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Products List');
        $sheet->getCell('A1')->setValue('Id');
        $sheet->getCell('B1')->setValue('Name');
        $sheet->getCell('C1')->setValue('Price');
        $sheet->getCell('D1')->setValue('Quantity');
        $sheet->getCell('E1')->setValue('Category');
        $sheet->getCell('F1')->setValue('Supplier');
        
        $sheet->fromArray($this->getData(),null, 'A2', true);

        $writer = new Xlsx($spreadsheet);
        $fileDate= new DateTime();
        $fileDate = $fileDate->format('d-m-Y');
        $writer->save('listProducts -'.$fileDate.'.xlsx');
        $this->addFlash('ExcelFileSaved', 'Excel file saved');
        return $this->redirectToRoute('listProducts');
    }


}
