<?php

namespace Lucena\Cielo\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Illuminate\Support\Facades\Log;
use Webkul\Sales\Models\Order;

/**
 * Cielo
 *
 * @author    Jitendra Singh <jitendra@webkul.com>
 * @copyright 2018 Webkul Software Pvt Ltd (http://www.webkul.com)
 */
class StandardController extends Controller
{
    /**
     * OrderRepository object
     *
     * @var array
     */
    protected $orderRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Attribute\Repositories\OrderRepository  $orderRepository
     * @return void
     */
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Redirects to the paypal.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirect()
    {
        return view('cielo::standard-redirect');
    }

    /**
     * Cancel payment from paypal.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel()
    {
        session()->flash('error', 'Cielo payment has been canceled.');

        return redirect()->route('shop.checkout.cart.index');
    }

    public function notification(){
        $request = request()->all();
        $cart_number = $request['order_number'];
        $payment_status = $request['payment_status'];

        $Order = Order::where('cart_id', $cart_number)->first();
        if($Order == null){
            abort(404);
        }
        switch($payment_status){
            case 1:
                // pagamento pendente
                    $Order->status = 'pending_payment';
                    $Order->save();  

            case 2:
                //pago
                    $Order->status = 'paid';
                    $Order->save();    
            break;
    
            case 3:
                //negado
                    $this->orderRepository->cancel($Order->id);
                    $Order->status = 'denied';
                    $Order->save();
            break;

            case 4:
                //Expirado

                    $this->orderRepository->cancel($Order->id);
                    $Order->status = 'expired';
                    $Order->save();
            break;

            case 5:
                //cancelado
                    $this->orderRepository->cancel($Order->id);
            break; 

            case 6:
                //Erro, contatar cielo
                    $Order->status = 'error';
                    $Order->save();
            break; 

            case 7:
                //Autorizado
                    $Order->status = 'processing';
                    $Order->save();
     
            break;    
            
            case 8:
                //chargeback
                    $Order->status = 'chargeback';
                    $Order->save();
            break; 

            
            default:
                Log::info("DEFAULT");
        }
        return ;
    }

    /**
     * Success payment
     *
     * @return \Illuminate\Http\Response
     */
    public function success()
    {
        $order = $this->orderRepository->create(Cart::prepareDataForOrder());

        Cart::deActivateCart();

        session()->flash('order', $order);

        return redirect()->route('shop.checkout.success');
    }

}