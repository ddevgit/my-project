<?php

namespace App\Controller\Api;

use App\Repository\ProductRepository;
use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ProductController extends AbstractFOSRestController
{
    /**
     * @Rest\Get(path="/products")
     * @Rest\View(serializerGroups={"product"}, serializerEnableMaxDepthChecks=true)
     */
    public function getAction(
        ProductRepository $productRepository
    ) {
        return $productRepository->findAll();
    }

    /**
     * @Rest\Get(path="/product/{id}")
     * @Rest\View(serializerGroups={"product"}, serializerEnableMaxDepthChecks=true)
     */
    public function getSingleAction(string $id, $getProduct)
    {
        try {
            $product = ($getProduct)($id);
        } catch (Exception $exception) {
            return View::create('Product not found', Response::HTTP_BAD_REQUEST);
        }
        return $product;
    }

    /**
     * @Rest\Post(path="/product")
     * @Rest\View(serializerGroups={"product"}, serializerEnableMaxDepthChecks=true)
     */
    public function postAction(
        BookFormProcessor $bookFormProcessor,
        Request $request
    ) {
        [$book, $error] = ($bookFormProcessor)($request);
        $statusCode = $book ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
        $data = $book ?? $error;
        return View::create($data, $statusCode);
    }

    /**
     * @Rest\Put(path="/books/{id}")
     * @Rest\View(serializerGroups={"book"}, serializerEnableMaxDepthChecks=true)
     */
    public function editAction(
        string $id,
        BookFormProcessor $bookFormProcessor,
        Request $request
    ) {
        try {
            [$book, $error] = ($bookFormProcessor)($request, $id);
            $statusCode = $book ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
            $data = $book ?? $error;
            return View::create($data, $statusCode);
        } catch (Throwable $t) {
            return View::create('Book not found', Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Rest\Patch(path="/books/{id}")
     * @Rest\View(serializerGroups={"book"}, serializerEnableMaxDepthChecks=true)
     */
    public function patchAction(
        string $id,
        GetBook $getBook,
        Request $request
    ) {
        $book = ($getBook)($id);
        $data = json_decode($request->getContent(), true);
        $book->patch($data);
        return View::create($book, Response::HTTP_OK);
    }

    /**
     * @Rest\Delete(path="/books/{id}")
     * @Rest\View(serializerGroups={"book"}, serializerEnableMaxDepthChecks=true)
     */
    public function deleteAction(string $id, DeleteBook $deleteBook)
    {
        try {
            ($deleteBook)($id);
        } catch (Throwable $t) {
            return View::create('Book not found', Response::HTTP_BAD_REQUEST);
        }
        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}
