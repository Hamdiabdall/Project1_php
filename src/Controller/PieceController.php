<?php

namespace App\Controller;

use App\Entity\Piece;
use App\Form\PieceType;
use App\Repository\PieceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/piece')]
class PieceController extends AbstractController
{
    #[Route('/', name: 'app_piece_index', methods: ['GET'])]
    public function index(PieceRepository $pieceRepository): Response
    {
        return $this->render('piece/index.html.twig', [
            'pieces' => $pieceRepository->findAll(),
        ]);
    }

    #[Route('/ajouter', name: 'app_piece_ajouter', methods: ['GET', 'POST'])]
    public function ajouter(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $piece = new Piece();
        $form = $this->createForm(PieceType::class, $piece);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('pieces_directory'),
                        $newFilename
                    );
                    $piece->setImage($newFilename);
                } catch (FileException $e) {
                    // Handle exception
                }
            }

            $entityManager->persist($piece);
            $entityManager->flush();

            return $this->redirectToRoute('app_piece_index');
        }

        return $this->render('piece/new.html.twig', [
            'piece' => $piece,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_piece_show', methods: ['GET'])]
    public function show(Piece $piece): Response
    {
        return $this->render('piece/show.html.twig', [
            'piece' => $piece,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_piece_modifier', methods: ['GET', 'POST'])]
    public function modifier(Request $request, Piece $piece, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(PieceType::class, $piece);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('pieces_directory'),
                        $newFilename
                    );
                    
                    // Supprimer l'ancienne image si elle existe
                    if ($piece->getImage()) {
                        $oldFile = $this->getParameter('pieces_directory').'/'.$piece->getImage();
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
                    
                    $piece->setImage($newFilename);
                } catch (FileException $e) {
                    // Gérer l'erreur si nécessaire
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'La pièce a été modifiée avec succès.');
            return $this->redirectToRoute('app_piece_index');
        }

        return $this->render('piece/edit.html.twig', [
            'piece' => $piece,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_piece_supprimer', methods: ['POST'])]
    public function supprimer(Request $request, Piece $piece, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$piece->getId(), $request->request->get('_token'))) {
            // Supprimer l'image si elle existe
            if ($piece->getImage()) {
                $imagePath = $this->getParameter('pieces_directory').'/'.$piece->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $entityManager->remove($piece);
            $entityManager->flush();
            $this->addFlash('success', 'La pièce a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_piece_index');
    }
} 