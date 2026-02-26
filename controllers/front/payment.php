<?php

class QPayPaymentModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $total = (float) $cart->getOrderTotal();

        $api = new QPayApi();
        $invoice = $api->createInvoice([
            'invoice_code' => Configuration::get('QPAY_INVOICE_CODE'),
            'sender_invoice_no' => 'PS-' . $cart->id,
            'invoice_receiver_code' => $customer->email,
            'invoice_description' => 'PrestaShop Order',
            'amount' => $total,
            'callback_url' => Configuration::get('QPAY_CALLBACK_URL') ?: $this->context->link->getModuleLink('qpay', 'validation'),
        ]);

        if (!$invoice || empty($invoice['invoice_id'])) {
            $this->errors[] = 'QPay invoice creation failed';
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
            return;
        }

        $this->context->cookie->qpay_invoice_id = $invoice['invoice_id'];
        $this->context->cookie->qpay_cart_id = $cart->id;

        $this->context->smarty->assign([
            'invoice' => $invoice,
            'check_url' => $this->context->link->getModuleLink('qpay', 'check'),
            'return_url' => $this->context->link->getModuleLink('qpay', 'validation'),
        ]);

        $this->setTemplate('module:qpay/views/templates/front/payment.tpl');
    }
}
