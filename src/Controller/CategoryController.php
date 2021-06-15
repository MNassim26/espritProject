<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\CategoryFormType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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

    /**
    * @Route("/uploadCategories", name="uploadCategories")
    */
    public function uploadCategories(Request $request)
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

            $category_existant = $em->getRepository(Category::class)->findOneBy(array('name' => $name)); 
                // make sure that the user does not already exists in your db 
            if (!$category_existant) 
             {  
                $category = new Category(); 
                $category->setName($name);          
                $em->persist($category); 
                $em->flush(); 
                 // here Doctrine checks all the fields of all fetched data and make a transaction to the database.
                 if($succes ==0){
                    $this->addFlash('categoryDataUploaded', 'The category data from the excel file has been added');
                    $succes=1;
                 }
             } 
        }  
        return $this->redirectToRoute("listCategories"); 
        }      
}
