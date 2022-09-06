<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Product;
use App\Form\ProductFormType;
use Symfony\Component\HttpFoundation\Request;

/**
 * User products controller
 */
class UserProductsController extends AbstractController
{
    /**
     * Index
     *
     * @Route("/user/products", name="app_user_products")
     *
     * @return Response
     */
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('user/products/index.html.twig',
            [
                'products' => $user->getProducts()->toArray(),
                'isUserExperience' => true
            ]
        );
    }

    /**
     * Add action
     *
     * @Route("/user/product/add", name="app_add_user_product")
     * @param Request $request
     * @param ProductRepository $productRepository
     *
     * @return Response
     */
    public function add(Request $request, ProductRepository $productRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ProductFormType::class, new Product());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $data = $form->getData()) {
            $user->addProduct($data);
            $productRepository->add($data);

            $this->addFlash(
                'success',
                'Ad added successfully'
            );
            return $this->redirectToRoute('app_user_products');
        }

        return $this->renderForm('user/product/add.html.twig',
            ['form' => $form]
        );
    }

    /**
     * Edit action
     *
     * @Route("/user/product/edit/{id}", name="app_edit_user_product")
     * @param int $id
     * @param Request $request
     * @param ProductRepository $productRepository
     *
     * @return Response
     */
    public function edit(int $id, Request $request, ProductRepository $productRepository): Response
    {
        $product = $this->getProductUser($id, $this->getUser(), $productRepository);
        if (!$product) {
            return $this->redirectToRoute('app_add_user_product');
        }

        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $data = $form->getData()) {
            $productRepository->add($data);

            $this->addFlash(
                'success',
                'Ad edited successfully'
            );
            return $this->redirectToRoute('app_user_products');
        }

        return $this->renderForm('user/product/edit.html.twig',
            ['form' => $form]
        );
    }

    /**
     * Get product user
     *
     * @param int $id
     * @param $user
     * @param ProductRepository $productRepository
     *
     * @return Product|null
     */
    protected function getProductUser(int $id, $user, ProductRepository $productRepository): ?Product
    {
        return $productRepository->findOneBy(['id' => $id, 'user' => $user]);
    }

    /**
     * Delete action
     *
     * @Route("/user/product/delete", name="app_delete_user_product", methods="GET")
     * @param Request $request
     * @param ProductRepository $productRepository
     *
     * @return JsonResponse
     */
    public function delete(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $productId = $request->query->get('id');
        if ($product = $this->getProductUser($productId, $this->getUser(), $productRepository)) {
            $productRepository->remove($product);
            $this->addFlash(
                'success',
                'Ad deleted successfully'
            );
        }

        return $this->json([
            'reloadPage' => true
        ]);
    }
}