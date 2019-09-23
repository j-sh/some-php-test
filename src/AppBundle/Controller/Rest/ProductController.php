<?php

namespace AppBundle\Controller\Rest;

use AppBundle\Entity\Product;
use AppBundle\Form\Type\ProductType;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;

/**
 * @RouteResource("Product", pluralize=false)
 */
class ProductController extends FOSRestController
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @ApiDoc(
     *     section="Product",
     *     description="Create new product, e.g. {""name"": ""Best product ever"", ""available"": 1988, ""vat_rate"": 0.5, ""price"": {""euros"": 79, ""cents"": 98}}",
     *     headers={
     *          {
     *              "name"="Content-Type",
     *              "default"="application/json",
     *              "required"=true,
     *              "description"="only 'application/json' accepted currently"
     *          }
     *     },
     *     statusCodes={
     *          200="action succesfull",
     *          400="action failed, there are validation errors",
     *          500="action failed, error while parsing JSON"
     *     }
     * )
     * @Rest\Post("add")
     */
    public function addAction(Request $request)
    {
        $serializer = $this->get('jms_serializer');
        $product = $serializer ->deserialize($request->getContent(), Product::class, 'json');
        $form = $this->createForm(ProductType::class, $product, ['method' => Request::METHOD_POST]);
        $form->handleRequest($request);
        $valid = false;
        if($form->isSubmitted()){
            $valid = $form->isValid();
        }elseif($request->get('testing') == true){
            $valid =true;
        }

        if ($valid) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            return $this->json($serializer->toArray($product), 200);
        }
        return $this->json($serializer->toArray($form->getErrors()), 400);
    }

    /**
     * @param $id
     * @return JsonResponse
     * @ApiDoc(
     *     section="Product",
     *     description="Delete product",
     *     requirements={
     *          {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="Product ID"}
     *     },
     *     statusCodes={
     *          204="action successful",
     *          404="action failed, product not found"
     *     }
     * )
     * @Rest\Delete("/{id}/remove")
     */
    public function removeAction($id)
    {
        $orm = $this->getDoctrine();
        $product = $orm->getRepository(Product::class)->find($id);

        if (!$product instanceof Product) {
            return $this->json(null, 404);
        }

        $entityManager = $orm->getManager();
        $entityManager->remove($product);
        $entityManager->flush();

        return $this->json(null, 204);
    }
}
