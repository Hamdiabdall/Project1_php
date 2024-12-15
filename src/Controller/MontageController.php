<?php

namespace App\Controller;

use App\Entity\Montage;
use App\Form\MontageType;
use App\Repository\MontageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/montage')]
class MontageController extends AbstractController
{
    #[Route('/', name: 'app_montage_index', methods: ['GET'])]
    public function index(MontageRepository $montageRepository): Response
    {
        return $this->render('montage/index.html.twig', [
            'montages' => $montageRepository->findAll(),
        ]);
    }

    #[Route('/addM', name: 'app_montage_addM', methods: ['GET', 'POST'])]
    public function addM(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $montage = new Montage();
        $form = $this->createForm(MontageType::class, $montage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('montages_directory'),
                        $newFilename
                    );
                    $montage->setImage($newFilename);
                } catch (FileException $e) {
                    // Handle exception
                }
            }

            $entityManager->persist($montage);
            $entityManager->flush();

            return $this->redirectToRoute('app_montage_index');
        }

        return $this->render('montage/new.html.twig', [
            'montage' => $montage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/editAll', name: 'app_montage_editAll', methods: ['GET', 'POST'])]
    public function editAll(Request $request, Montage $montage, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MontageType::class, $montage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_montage_index');
        }

        return $this->render('montage/edit.html.twig', [
            'montage' => $montage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_montage_delete', methods: ['POST'])]
    public function delete(Request $request, Montage $montage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$montage->getId(), $request->request->get('_token'))) {
            $entityManager->remove($montage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_montage_index');
    }
} 