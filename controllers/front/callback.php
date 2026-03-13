<?php

class QPayCallbackModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $paymentId = Tools::getValue('qpay_payment_id');
        if (!$paymentId) {
            header('HTTP/1.1 400 Bad Request');
            die('MISSING_PAYMENT_ID');
        }

        $api = new QPayApi();
        $payment = $api->getPayment($paymentId);

        if (!$payment || empty($payment['payment_status']) || $payment['payment_status'] !== 'PAID') {
            header('HTTP/1.1 400 Bad Request');
            die('PAYMENT_NOT_CONFIRMED');
        }

        $senderInvoiceNo = $payment['sender_invoice_no'] ?? '';

        if (preg_match('/^PS-(\d+)$/', $senderInvoiceNo, $m)) {
            $cartId = (int) $m[1];
            $cart = new Cart($cartId);

            if (Validate::isLoadedObject($cart) && !$cart->orderExists()) {
                $customer = new Customer($cart->id_customer);
                $total = (float) $cart->getOrderTotal();

                $this->module->validateOrder(
                    $cartId,
                    Configuration::get('PS_OS_PAYMENT'),
                    $total,
                    $this->module->displayName,
                    'QPay Payment ID: ' . $paymentId,
                    [],
                    null,
                    false,
                    $customer->secure_key
                );
            }
        }

        die('SUCCESS');
    }
}
