<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Detailcommande;
use App\Entity\Velos;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


class PanierController extends AbstractController
{
    /**
     * @Route("/panier", name="page_panier")
     */
    public function panier(SessionInterface $session, ManagerRegistry $doctrine): Response
    {
        $panier = $session->get('panier', []);

        $panierData = [];
        foreach ($panier as $id => $quantite) {
            $velo = $doctrine->getRepository(Velos::class)->find($id);
            $detailCommande = $doctrine->getRepository(Detailcommande::class)->findOneBy(['quantite' => $velo]);

            $panierData[] = [
                "velos" => $velo,
                "quantite" => $quantite,
                "imagename" => $velo->getImagename(),
                "detailcommande" => $detailCommande,
            ];
        }

        $total = 0;
        foreach ($panierData as $item) {
            $total += $item['velos']->getPrix() * $item['quantite'];
        }

        $totalQuantity = 0;
        foreach ($panierData as $item) {
            $totalQuantity += $item['quantite'];
        }

        return $this->render('panier/index.html.twig', [
            "items" => $panierData,
            "total" => $total,
            "totalQuantity" => $totalQuantity,
        ]);
    }

    /**
     * @Route("/panier/add/{id}", name="panier_add")
     */
    public function panierAdd($id, SessionInterface $session)
    {
        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('page_panier');
    }

    /**
     * @Route("/panier/delete/{id}", name="panier_delete")
     */
    public function delete($id, SessionInterface $session)
    {
        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            $panier[$id]--;

            if ($panier[$id] <= 0) {
                unset($panier[$id]); //unset for removing from the session
            }
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('page_panier');
    }

    /**
     * @Route("/panier/clear", name="panier_clear")
     */
    public function clearCart(SessionInterface $session)
    {
        $session->remove('panier');

        return $this->redirectToRoute('page_panier');
    }

    /**
     * @Route("/panier/ajouter/{id}", name="ajouter_panier")
     */
    public function ajouter(int $id, SessionInterface $session): RedirectResponse
    {
        $panier = $session->get('panier', []);

        if (isset($panier[$id])) {
            $panier[$id]++;
        }
        $session->set('panier', $panier);

        return $this->redirectToRoute('page_panier');
    }

    /**
     * @Route("/panier/enlever/{id}", name="enlever_panier")
     */
    public function enlever(int $id, SessionInterface $session)
    {
        $panier = $session->get('panier', []);

        if (!empty($panier[$id]) && $panier[$id] > 0) {
            $panier[$id]--;
        }
        $session->set('panier', $panier);

        return $this->redirectToRoute('page_panier');
    }
}
