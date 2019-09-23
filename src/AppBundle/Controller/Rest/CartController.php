<?php

namespace AppBundle\Controller\Rest;

use AppBundle\Entity\Interfaces\MoneyInterface;
use AppBundle\Entity\Cart;
use AppBundle\Entity\CartProducts;
use AppBundle\Entity\Product;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;

/**
 * @RouteResource("Cart", pluralize=false)
 */
class CartController extends FOSRestController
{
    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @ApiDoc(
     *     section="Cart",
     *     description="Create new cart",
     *     statusCodes={
     *          200="action succesful"
     *     }
     * )
     * @Rest\Post("add")
     */
    public function addAction()
    {
        $cart = new Cart();
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($cart);
        $entityManager->flush();
        return $this->json(['cartId' => $cart->getId()], 200);
    }

    /**
     * @param integer $id
     * @return JsonResponse
     * @ApiDoc(
     *     section="Cart",
     *     description="Delete cart",
     *     requirements={
     *          {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="Cart ID"}
     *     },
     *     statusCodes={
     *          204="action succesful",
     *          404="action failed, either cart or product is not found"
     *     }
     * )
     * @Rest\Delete("/{id}/remove")
     */
    public function removeAction($id)
    {
        $cart = $this->getDoctrine()->getRepository(Cart::class)->find($id);
        if (!$cart instanceof Cart) {
            return new JsonResponse(null, 404);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($cart);
        $entityManager->flush();

        return $this->json(null, 204);
    }

    /**
     * @param         $id
     * @param         $productId
     * @return JsonResponse
     * @ApiDoc(
     *     section="Cart",
     *     description="Add product to cart",
     *     requirements={
     *          {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="Cart ID"},
     *          {"name"="productId", "dataType"="integer", "requirement"="\d+", "description"="Product ID"},
     *     },
     *     statusCodes={
     *          200="action succesful",
     *          404="action failed, either cart or product is not found"
     *     }
     * )
     * @Rest\Get("/{id}/add/{productId}")
     */
    public function addProductAction($id, $productId)
    {
        $orm = $this->getDoctrine();
        $cart = $orm->getRepository(Cart::class)->find($id);
        $product= $orm->getRepository(Product::class)->find($productId);

        if (!$cart instanceof Cart || !$product instanceof Product) {
            return new JsonResponse(null, 404);
        }

        $cartProduct = $orm->getRepository(CartProducts::class)
            ->findOneBy(['cart' => $id, 'product' => $productId]);

        $entityManager = $orm->getManager();

        // If product is present in cart then increase amount bought
        if (!empty($cartProduct)) {
            $cartProduct->increaseCount();
            $entityManager->persist($cartProduct);
        } else {
            //else add product to cart
            $cartProduct = new CartProducts();
            $cartProduct->setCart($cart);
            $cartProduct->setProduct($product);
            $cart->addProduct($product);
            $entityManager->persist($cart);
        }

        $entityManager->flush();

        $allCartProducts = $cart->getProducts();
        $cartProducts = [];

        // Create array with all cart products and their counts
        foreach ($allCartProducts as $cartProduct) {
            $cartProducts[] = [
                'product' => $cartProduct->getProduct(),
                'count'   => $cartProduct->getCount(),
            ];
        }

        $serializer = $this->get('jms_serializer');
        return $this->json([
            'cart' => [
                'id' => $cart->getId(),
                'products' => $serializer->toArray($cartProducts),
            ],
        ], 200);
    }

    /**
     * @param $id
     * @param $productId
     * @return JsonResponse
     * @ApiDoc(
     *     section="Cart",
     *     description="Remove product from cart",
     *     requirements={
     *          {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="Cart ID"},
     *          {"name"="productId", "dataType"="integer", "requirement"="\d+", "description"="Product ID"},
     *     },
     *     statusCodes={
     *          204="action succesful",
     *          404="action failed, either cart or product is not found"
     *     }
     * )
     * @Rest\Delete("/{id}/remove/{productId}")
     */
    public function removeProductAction($id, $productId)
    {
        $orm = $this->getDoctrine();
        $cart= $orm->getRepository(Cart::class)->find($id);
        $product = $orm->getRepository(Product::class)->find($productId);

        if (!$cart instanceof Cart || !$product instanceof Product) {
            return new JsonResponse(null, 404);
        }

        $cartProduct = $orm->getRepository(CartProducts::class)
            ->findOneBy(['cart' => $id, 'product' => $productId]);
        $entityManager = $orm->getManager();

        if (!empty($cartProduct)) {
            // if multiple items in cart  of single product, decrease cnt
            if ($cartProduct->getCount() > 1) {
                $cartProduct->decreaseCount();
                $entityManager->persist($cartProduct);
            } else {
                // if single unit of product in cart, remove product  from cart
                $entityManager->remove($cartProduct);
            }
        }
        $cart->removeProduct($product);
        $entityManager->persist($cart);
        $entityManager->flush();

        return $this->json(null, 204);
    }

    /**
     * @param $id
     * @return JsonResponse
     * @ApiDoc(
     *     section="Cart",
     *     description="Get all cart products",
     *     requirements={
     *          {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="Cart ID"}
     *     },
     *     statusCodes={
     *          200="action succesful",
     *          404="action failed, cart is not found"
     *     }
     * )
     * @Rest\Get("/{id}/products")
     */
    public function getProductsAction($id)
    {
        $cart = $this->getDoctrine()
            ->getRepository(Cart::class)
            ->find($id);

        if (!$cart instanceof Cart) {
            return new JsonResponse(null, 404);
        }

        $cartProducts = $cart->getProducts();
        $result = [];

        foreach ($cartProducts as $prod) {
            $result[] = [
                'product' => $prod->getProduct(),
                'amount'   => $prod->getCount(),
            ];
        }

        $serializer = $this->get('jms_serializer');
        return $this->json($serializer->toArray($result), 200);
    }

    /**
     * @param $id
     * @return JsonResponse
     * @ApiDoc(
     *     section="Cart",
     *     description="Get cart total",
     *     requirements={
     *          {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="Cart ID"}
     *     },
     *     statusCodes={
     *          200="action successful",
     *          404="action failed, cart is not found"
     *     }
     * )
     * @Rest\Get("/{id}/total")
     */
    public function getTotalAction($id)
    {
        $cart = $this->getDoctrine()
            ->getRepository(Cart::class)
            ->find($id);

        if (!$cart instanceof Cart) {
            return new JsonResponse(null, 404);
        }
        $total = $cart->getTotal();
        return $this->json([
            'euros' => $total->getEuros(),
            'cents' => $total->getCents(),
        ], 200);
    }

    /**
     * @param $id
     * @return JsonResponse
     * @ApiDoc(
     *     section="Cart",
     *     description="Get cart subtotal",
     *     requirements={
     *          {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="Cart ID"}
     *     },
     *     statusCodes={
     *          200="action successful",
     *          404="action failed, cart is not found"
     *     }
     * )
     * @Rest\Get("/{id}/subtotal")
     */
    public function getSubtotalAction($id)
    {
        $cart = $this->getDoctrine()
            ->getRepository(Cart::class)
            ->find($id);
        if (!$cart instanceof Cart) {
            return new JsonResponse(null, 404);
        }
        $total = $cart->getSubtotal();
        return $this->json([
            'euros' => $total->getEuros(),
            'cents' => $total->getCents(),
        ], 200);
    }

    /**
     * @param $id
     * @return JsonResponse
     * @ApiDoc(
     *     section="Cart",
     *     description="Get cart VAT amount",
     *     requirements={
     *          {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="Cart ID"}
     *     },
     *     statusCodes={
     *          200="action successful",
     *          404="action failed, cart is not found"
     *     }
     * )
     * @Rest\Get("/{id}/vat")
     */
    public function getVatAmountAction($id)
    {
        $cart = $this->getDoctrine()->getRepository(Cart::class)->find($id);
        if (!$cart instanceof Cart) {
            return new JsonResponse(null, 404);
        }
        /**@var MoneyInterface $vat */
        $vat = $cart->getVatAmount();
        return $this->json([
            'euros' => $vat->getEuros(),
            'cents' => $vat->getCents(),
        ], 200);
    }
}
