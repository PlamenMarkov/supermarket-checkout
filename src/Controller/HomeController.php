<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'page_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('home.html.twig');
    }

    #[Route('/admin', name: 'page_admin', methods: ['GET'])]
    public function admin(): Response
    {
        return $this->redirectToRoute('page_admin_orders');
    }

    #[Route('/admin/products', name: 'page_admin_products', methods: ['GET'])]
    public function adminProducts(): Response
    {
        return $this->render('admin/products.html.twig');
    }

    #[Route('/admin/orders', name: 'page_admin_orders', methods: ['GET'])]
    public function adminOrders(): Response
    {
        return $this->render('admin/orders.html.twig');
    }

    #[Route('/admin/promotions', name: 'page_admin_promotions', methods: ['GET'])]
    public function adminPromotions(): Response
    {
        return $this->render('admin/promotions.html.twig');
    }
}
