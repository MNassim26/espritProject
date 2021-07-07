<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
  * Require ROLE_ADMIN for *every* controller method in this class.
  *
  * @IsGranted("ROLE_USER")
 */

class UserController extends AbstractController
{
    #[Route('/user', name: 'user')]
    public function index(): Response
    {   
        // $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function addUser(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('listUsers');
        }

        return $this->render('user/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/listUsers", name="listUsers")
     */
    public function listUsers()
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        return $this->render('user/index.html.twig', array("users" => $users));
    }

    /**
     * @Route("/deleteUser/{id}", name="deleteUser")
     */
    public function deleteUser($id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        $this->addFlash('userDeleted', 'The user has been deleted');
        return $this->redirectToRoute("listUsers");
    }

    /**
     * @Route("/updateUser/{id}", name="updateUser")
     */
    public function updateUser(Request $request,$id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(RegistrationFormType::class,$user);
        $form->add('Modifier', SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('userUpdated', 'The user has been updated');
            return $this->redirectToRoute("listUsers");
        }
        return $this->render('user/update.html.twig', array("form" => $form->createView()));

    }

}
