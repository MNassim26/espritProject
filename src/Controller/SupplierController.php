<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Supplier;
use App\Form\SupplierFormType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
  * Require ROLE_ADMIN for *every* controller method in this class.
  *
  * @IsGranted("ROLE_USER")
 */

class SupplierController extends AbstractController
{
    #[Route('/supplier', name: 'supplier')]
    public function index(): Response
    {
        return $this->render('supplier/index.html.twig', [
            'controller_name' => 'SupplierController',
        ]);
    }

    /**
     * @Route("/addSupplier", name="addSupplier")
     */
    public function addSupplier(Request $request)
    {   
        $supplier = new Supplier();
        $form = $this->createForm(SupplierFormType::class,$supplier);
        $form->add('Ajouter', SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($supplier);
            $em->flush();
            $this->addFlash('supplierAdded', 'The supplier has been added');
            return $this->redirectToRoute('listSuppliers');
        }
        return $this->render('supplier/add.html.twig', array("form" => $form->createView()));
    }

    /**
     * @Route("/listSuppliers", name="listSuppliers")
     */
    public function listSuppliers()
    {
        $suppliers = $this->getDoctrine()->getRepository(Supplier::class)->findAll();
        return $this->render('supplier/index.html.twig', array("suppliers" => $suppliers));
    }

    /**
     * @Route("/deleteSupplier/{id}", name="deleteSupplier")
     */
    public function deleteSupplier($id)
    {
        $supplier = $this->getDoctrine()->getRepository(Supplier::class)->find($id);
        $products= $this->getDoctrine()->getRepository(Product::class)->findAll();
        foreach($products as $product){
            if($product->getSupplier() == $supplier){
                $this->addFlash('supplierDeleteError', 'This supplier can not be deleted, it has already supplied some products !');
                return $this->redirectToRoute("listSuppliers");
            }
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($supplier);
        $em->flush();
        $this->addFlash('supplierDeleted', 'The supplier has been deleted');
        return $this->redirectToRoute("listSuppliers");
    }

    /**
     * @Route("/updateSupplier/{id}", name="updateSupplier")
     */
    public function updateSupplier(Request $request,$id)
    {
        $supplier = $this->getDoctrine()->getRepository(Supplier::class)->find($id);
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(SupplierFormType::class,$supplier);
        $form->add('Modifier', SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('supplierUpdated', 'The supplier has been updated');
            return $this->redirectToRoute("listSuppliers");
        }
        return $this->render('supplier/update.html.twig', array("form" => $form->createView()));

    }

    /**
    * @Route("/uploadSuppliers", name="uploadSuppliers")
    */
    public function uploadSuppliers(Request $request)
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
            $adress = $Row['B']; 

            $supplier_existant = $em->getRepository(Supplier::class)->findOneBy(array('name' => $name)); 
                // make sure that the user does not already exists in your db 
            if (!$supplier_existant) 
             {  
                $supplier = new Supplier(); 
                $supplier->setName($name);
                $supplier->setAdress($adress);            
                $em->persist($supplier); 
                $em->flush(); 
                 // here Doctrine checks all the fields of all fetched data and make a transaction to the database.
                 if($succes ==0){
                    $this->addFlash('supplierDataUploaded', 'The supplier data from the excel file has been added');
                    $succes=1;
                 }
             } 
        }  
        return $this->redirectToRoute("listSuppliers"); 
        }      
}
