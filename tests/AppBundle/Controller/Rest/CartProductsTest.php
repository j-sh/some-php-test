<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CartProductsTest extends WebTestCase
{
    /**
     * Test scenario:
     * 1. Create new cart
     * 2. Create product 1
     * 3. Create product 2
     * 4. Add product 1 to cart (2 units)
     * 5. Add product 2 to cart (1 unit)
     * 6. Get all products in cart
     * 7. Get cart totals
     * 8. Delete cart
     * 9. Delete product 1
     * 10. Delete product 2
     *
     * validate values returned
     */
    public function testAddRemoveProductFromCart()
    {
        $client = static::createClient();

        $client->request('POST', '/cart/add');

        $response = $client->getResponse();
        $content  = $response->getContent();
        $cartId   = json_decode($content)->cartId;

        // Create new product and check that response contains valid json content
        $productJson = '{"name": "Mighty duck", "available": 88, "vat_rate": 0.25, "price": {"euros": 40, "cents": 20}}';
        $client->request('POST', '/product/add', ['testing' => true], [], [], $productJson);
        $content = $client->getResponse()->getContent();
        $this->assertJson($content);

        $productAId = json_decode($content)->id;

        // Create another product
        $productJson = '{"name": "duck", "available": 2, "vat_rate": 0.5, "price": {"euros": 86, "cents": 44}}';
        $client->request('POST', '/product/add', ['testing' => true], [], [], $productJson);
        $content = $client->getResponse()->getContent();
        $this->assertJson($content);
        $productBId = json_decode($content)->id;

        // Add product to cart and check that response contains correct json for the new product
        $client->request('GET', '/cart/' . $cartId . '/add/' . $productAId);
        $validJSON = '{"cart": {"id": ' . $cartId . ', "products": [{"product": {"id": ' . $productAId . ', "name": "Mighty duck", "available": 88, "vat_rate": 0.25, "price": { "euros": 40, "cents": 20 }},"count": 1}]}}';
        $this->assertJsonStringEqualsJsonString($validJSON, $client->getResponse()->getContent());

        // Add the same product to cart one more time and check if response still contains correct json and product count increases
        $client->request('GET', '/cart/' . $cartId . '/add/' . $productAId);
        $validJSON = '{"cart": {"id": ' . $cartId . ', "products": [{"product": {"id": ' . $productAId . ', "name": "Mighty duck", "available": 88, "vat_rate": 0.25, "price": { "euros": 40, "cents": 20 }},"count": 2}]}}';
        $this->assertJsonStringEqualsJsonString($validJSON, $client->getResponse()->getContent());

        // Add different product to cart and check if cart contains both products
        $client->request('GET', '/cart/' . $cartId . '/add/' . $productBId);
        $validJSON = '{"cart": {"id": ' . $cartId . ', "products": [{"product": {"id": ' . $productAId . ', "name": "Mighty duck", "available": 88, "vat_rate": 0.25, "price": { "euros": 40, "cents": 20 }},"count": 2},{"product": {"id": ' . $productBId . ', "name": "duck", "available": 2, "vat_rate": 0.5, "price": { "euros": 86, "cents": 44 }},"count": 1}]}}';

        $response = $client->getResponse()->getContent();
        trim($response, "\n\t\r\s");
        $this->assertJsonStringEqualsJsonString($validJSON, $response);


        // Get cart subtotal and check if it is correct
        $client->request('GET', '/cart/' . $cartId . '/subtotal');
        $validJSON = '{"euros": 166, "cents": 84}';
        $this->assertJsonStringEqualsJsonString($validJSON, $client->getResponse()->getContent());

        // Get cart VAT amount and check if it is correct
        $client->request('GET', '/cart/' . $cartId . '/vat');
        $validJSON = '{"euros": 63, "cents": 32}';
        $this->assertJsonStringEqualsJsonString($validJSON, $client->getResponse()->getContent());

        // Get cart total and check if it is correct
        $client->request('GET', '/cart/' . $cartId . '/total');
        $validJSON = '{"euros": 230, "cents": 16}';
        $this->assertJsonStringEqualsJsonString($validJSON, $client->getResponse()->getContent());

        // Delete cart and check if response status code is 204
        $client->request('DELETE', '/cart/' . $cartId . '/remove');
        $this->assertEquals(204, $client->getResponse()->getStatusCode());

        // Delete both products and check if response status code is 204
        $client->request('DELETE', '/product/' . $productAId . '/remove');
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
        $client->request('DELETE', '/product/' . $productBId . '/remove');
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }
}
