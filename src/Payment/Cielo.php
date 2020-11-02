<?php

namespace Lucena\Cielo\Payment;

use Illuminate\Support\Facades\Config;
use Webkul\Payment\Payment\Payment;
use DB;
use Webkul\Checkout\Models\CartAddress;

/**
 * Paypal class
 *
 * @author    Jitendra Singh <jitendra@webkul.com>
 * @copyright 2018 Webkul Software Pvt Ltd (http://www.webkul.com)
 */
abstract class Cielo extends Payment
{
    /**
     * PayPal web URL generic getter
     *
     * @param array $params
     * @return string
     */
    public function getCieloUrl($params = [])
    {

        $cart = $this->getCart();
        
        // ENVIA DADOS PRO CHECKOUT DA CIELO PRA CRIAR O CHECKOUT
//        file_put_contents('filename.txt', print_r($array, true));
        
        $items_data = array();
        foreach($cart->items as $item){
            $data_item = array(
                'Name' => $item->name,
                'UnitPrice' => $item->price * 100,
                'Quantity' => $item->quantity,
                'Type' => 'Asset',
                'Sku' => $item->sku
            );
            array_push($items_data, $data_item);
        }

        $cart_data = array(
            'Discount' => array(
                'Type' => "Amount",
                'Value' => $cart->discount_amount * 100
            ),
            'Items' => $items_data
        );

        // para descobrir endereço e o preço da entrega, precisa fazer uma query, buscando o endereço do carrinho com o address_type = shipping
        // depois buscar este id na tabela 	cart_shipping_rates
        $cart_shipping_rates = $cart->selected_shipping_rate;

            $cart_shipping = $cart->getShippingAddressAttribute();
            $address = explode("\n", $cart_shipping->address1);
            $shipping = array(
                "TargetZipCode"=> $cart_shipping->postcode,

                "Type" => "FixedAmount",
                'Services' => array(
                    array(
                        'Name' => $cart_shipping_rates->method_title,
                        'Price' => $cart_shipping_rates->price*100,
                        'Deadline' => 2,
                        "Carrier" => null
                    )
                    ),
                    // alterar o sistema para receber os dados de entrega. . 
                'Address' => array(
                    "Street" =>  $address[0],
                    "Number" => $address[1],
                    "Complement" => $address[3],
                    "District" => $address[2],
                    "City" => $cart_shipping->city,
                    "State" => $cart_shipping->state
                )
        );
      
        $payment = array(
            'BoletoDiscount' => 0,
            'DebitDiscount' => 0,
            'FirstInstallmentDiscount' => 0
        );

        $customerData = DB::table('customers')
        ->where('email',$cart->customer_email)
        ->first();
        $customer = array(
            'FullName' => $customerData->first_name . " " . $customerData->last_name,
            'Email' => $customerData->email,
            'Phone' => $customerData->phone
        );

        $options = array(
            'AntifraudEnabled' => true,
            'ReturnUrl' => route('cielo.standard.success')
        );
         
        $data = array(
            'OrderNumber' => $cart->id,
            'Cart' => $cart_data,
            'Shipping' => $shipping,
            'Payment' => $payment,
            'Customer' => $customer,
            'Options' => $options,
            'Settings' => null
        );

        $request = $this->Request($data,$this->getConfigData('merchant_id'));

        $url_cielo = $request['settings']['checkoutUrl'];

        return $url_cielo;
    }

    function Request($data,$merchant_id){
        $cabeçalhos = [
            "Content-Type: application/json",
            'MerchantId:'.$merchant_id,
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [

            // Define o método POST:
            CURLOPT_CUSTOMREQUEST => 'POST',

            /* Uma outra opção é utilizar:
            CURLOPT_POST => true,
            */
            // Define o URL:
            CURLOPT_URL => 'https://cieloecommerce.cielo.com.br/api/public/v1/orders',

            // Define os cabeçalhos:    
            CURLOPT_HTTPHEADER => $cabeçalhos,

            // Define corpo, em JSON:
            CURLOPT_POSTFIELDS => json_encode($data),

            // Habilita o retorno
            CURLOPT_RETURNTRANSFER => true

        ]);

        // Executa:
        $resposta = curl_exec($ch);

        $json = json_decode($resposta, true);
        // Encerra CURL:
        curl_close($ch);
        return $json;
    }

    /**
     * Add order item fields
     *
     * @param array $fields
     * @param int $i
     * @return void
     */
    protected function addLineItemsFields(&$fields, $i = 1)
    {
        $cartItems = $this->getCartItems();

        foreach ($cartItems as $item) {

            foreach ($this->itemFieldsFormat as $modelField => $paypalField) {
                $fields[sprintf($paypalField, $i)] = $item->{$modelField};
            }

            $i++;
        }
    }

    /**
     * Add billing address fields
     *
     * @param array $fields
     * @return void
     */
    protected function addAddressFields(&$fields)
    {
        $cart = $this->getCart();

        $billingAddress = $cart->billing_address;

        $fields = array_merge($fields, [
            'city'             => $billingAddress->city,
            'country'          => $billingAddress->country,
            'email'            => $billingAddress->email,
            'first_name'       => $billingAddress->first_name,
            'last_name'        => $billingAddress->last_name,
            'zip'              => $billingAddress->postcode,
            'state'            => $billingAddress->state,
            'address1'         => $billingAddress->address1,
            'address_override' => 1
        ]);
    }

    /**
     * Checks if line items enabled or not
     *
     * @param array $fields
     * @return void
     */
    public function getIsLineItemsEnabled()
    {
        return true;
    }
}