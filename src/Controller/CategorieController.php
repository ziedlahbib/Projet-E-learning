<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategorieController extends AbstractController
{
    /**
     * @Route("/categorie", name="categorie")
     */
    public function index(): Response
    {
        return $this->render('categorie/show.html.twig', [
            'controller_name' => 'CategorieController',
        ]);
    }

    /**
     * @Route("/afficheCategorie",name="affichecategorie")
     */
    public function AfficheCategorie(Request $request,CategorieRepository $repository, PaginatorInterface $paginator){
        $tablecategorie=$repository->findAll();
        $tablecategorie = $paginator->paginate(
            $tablecategorie,
            $request->query->getInt('page', 1),
            5
        );



        return $this->render('categorie/afficheCategorie.html.twig'
            ,['tableCategorie'=>$tablecategorie]);

    }
    /**
     * @Route("/ajoutcategorie",name="ajoutcategorie")
     */
    public function addCategorie(EntityManagerInterface $em,Request $request){
        $categorie= new Categorie();

        $form= $this->createForm(CategorieType::class,$categorie);
        $form->add('Ajouter',SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $imageFile = $form->get('categorieimage')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        'front\web\images',
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $categorie->setCategorieimage($newFilename);
            }
            $em->persist($categorie);
            $em->flush();
            $this->addFlash(
                'info','Ajouté avec succées '
            );
            return $this->redirectToRoute("affichecategorie");

        }
        return $this->render("categorie/ajoutcategorie.html.twig",array("formulaire"=>$form->createView()));
    }

    /**
     * @Route("/supprimercategorie/{id}",name="supprimercategorie")
     */
    public function deleteCategorie($id,EntityManagerInterface $em ,CategorieRepository $repository){
        $categorie=$repository->find($id);
        $em->remove($categorie);
        $em->flush();
        $this->addFlash(
            'info','Supprimé avec succées '
        );
        return $this->redirectToRoute('affichecategorie');
    }


    /**
     * @Route("/{id}/modifiercategorie", name="modifiercategorie", methods={"GET","POST"})
     */
    public function edit(Request $request, Categorie $categorie): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->add('Confirmer',SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('categorieimage')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        'front\web\images',
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $categorie->setCategorieimage($newFilename);
            }
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                'info',
                'Vos modifications ont été enregistrées!'
            );

            return $this->redirectToRoute('affichecategorie');
        }

        return $this->render('categorie/Modifiercategorie.html.twig', [
            'categoriemodif' => $categorie,
            'form' => $form->createView(),
        ]);
    }


}
