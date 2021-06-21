<?php

namespace App\Controller;
use App\Entity\Facture;
use App\Entity\Order;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Validator\Constraints\Date;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
  * Require ROLE_ADMIN for *every* controller method in this class.
  *
  * @IsGranted("ROLE_USER")
 */

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
        $this->addFlash('factureAdded', 'The order has been confirmed and a facture has been created');
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
        $this->addFlash('factureDeleted', 'The facture
         has been deleted');
        return $this->redirectToRoute("listFactures");
    }

    private function getData(): array
    {
        $list = [];
        $listProducts="";
        $factures = $this->getDoctrine()->getRepository(Facture::class)->findAll();

        foreach ($factures as $facture) {
            foreach($facture->getOrder()->getProducts() as $product){
                $listProducts=$listProducts." \n ".$product;
            }
            $list[] = [
                $facture->getId(),
                $facture->getDate(),
                $listProducts,
                $facture->getOrder()->getTotalPrice(),
                ];
            }
        return $list;
    }
    
    /**
     * @Route("/exportFactures", name="exportFacture")
     */
    public function exportFactures()
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Factures List');
        $sheet->getCell('A1')->setValue('Id');
        $sheet->getCell('B1')->setValue('Date');
        $sheet->getCell('C1')->setValue('Products');
        $sheet->getCell('D1')->setValue('Total price');
        
        $sheet->fromArray($this->getData(),null, 'A2', true);

        $writer = new Xlsx($spreadsheet);
        $fileDate= new DateTime();
        $fileDate = $fileDate->format('d-m-Y');
        $writer->save('listFactures -'.$fileDate.'.xlsx');
        $this->addFlash('ExcelFileSaved', 'Excel file saved');
        return $this->redirectToRoute('listFactures');
    }
}
