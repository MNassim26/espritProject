<?php


namespace App\Controller;

use App\Entity\Category;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Supplier;
use App\Form\ProductFormType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


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
        $orders= $this->getDoctrine()->getRepository(Order::class)->findAll();
        foreach($orders as $order){
            foreach($order->getProducts() as $oProduct){
                if($oProduct == $product){
                    $this->addFlash('productDeleteError', 'This product can not be deleted, it is already ordered !');
                    return $this->redirectToRoute("listProducts");
                }
            }
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();
        $this->addFlash('productDeleted', 'The product has been deleted');
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

    /**
    * @Route("/uploadProducts", name="uploadProducts")
    */
    public function uploadProducts(Request $request)
    {
    $file = $request->files->get('myfile'); // get the file from the sent request
    $fileFolder = __DIR__ . '/../../public/upload/';  //choose the folder in which the uploaded file will be stored
    $fileDate= new DateTime();
    $fileDate = $fileDate->format('d-m-Y');
    $filePathName = $fileDate."-". $file->getClientOriginalName();
   // apply md5 function to generate an unique identifier for the file and concat it with the file extension  
         try {
             $file->move($fileFolder, $filePathName);
         } catch (FileException $e) {
             dd($e);
         }
    $spreadsheet = IOFactory::load($fileFolder . $filePathName); 
    $row = $spreadsheet->getActiveSheet()->removeRow(1); // I added this to be able to remove the first file line 
    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true); // here, the read data is turned into an array
    $em = $this->getDoctrine()->getManager(); 
    $succes=0;
    foreach ($sheetData as $Row) 
        { 

            $name = $Row['A'];
            $price = $Row['B']; 
            $quantity = $Row['C'];
            $categoryName = $Row['D'];
            $supplierName = $Row['E'];    

            $product_existant = $em->getRepository(Product::class)->findOneBy(array('name' => $name)); 
                // make sure that the user does not already exists in your db 
            if (!$product_existant) 
             {  
                $product = new Product(); 
                $product->setName($name);
                $product->setPrice($price); 
                $product->setQuantity($quantity);
                $category_existant = $em->getRepository(Category::class)->findOneBy(array('name' => $categoryName));
                if (!$category_existant){
                    $category = new Category();
                    $category->setName($categoryName);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($category);
                    $em->flush();
                    $product->setCategory($category);
                } 
                else {
                    $product->setCategory($category_existant);
                }
                $supplier_existant = $em->getRepository(Category::class)->findOneBy(array('name' => $supplierName));
                if (!$supplier_existant){
                    $supplier = new Supplier();
                    $supplier->setName($supplierName);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($supplier);
                    $em->flush();
                    $product->setSupplier($supplier);
                } 
                else{
                    $product->setSupplier($supplier_existant);
                }
                $product->setOrders(null);
                $em->persist($product); 
                $em->flush(); 
                 // here Doctrine checks all the fields of all fetched data and make a transaction to the database.
                 if($succes ==0){
                    $this->addFlash('productDataUploaded', 'The product data from the excel file has been added');
                    $succes=1;
                 }
             } 
        }  
        return $this->redirectToRoute("listProducts"); 
        }     


}
