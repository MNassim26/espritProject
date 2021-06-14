<?php

namespace App\Controller;

use App\Entity\Supplier;
use App\Form\SupplierFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        if($supplier->getProducts() !=null){
            $this->addFlash('supplierDeleteError', 'This supplier can not be deleted, it has already supplied some products !');
            return $this->redirectToRoute("listSuppliers");
        }
        else {
        $em = $this->getDoctrine()->getManager();
        $em->remove($supplier);
        $em->flush();
        $this->addFlash('supplierDeleted', 'The supplier has been deleted');
        return $this->redirectToRoute("listSuppliers");
        }
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
}
