<?php

class QPayValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $invoiceId = $this->context->cookie->qpay_invoice_id ?? '';
        $cartId = (int) ($this->context->cookie->qpay_cart_id ?? 0);

        if (!$invoiceId || !$cartId) {
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
            return;
        }

        $api = new QPayApi();
        $result = $api->checkPayment($invoiceId);

        if ($result && !empty($result['rows'])) {
            $cart = new Cart($cartId);
            $customer = new Customer($cart->id_customer);
            $total = (float) $cart->getOrderTotal();

            $this->module->validateOrder(
                $cartId,
                Configuration::get('PS_OS_PAYMENT'),
                $total,
                $this->module->displayName,
                'QPay Invoice: ' . $invoiceId,
                [],
                null,
                false,
                $customer->secure_key
            );

            unset($this->context->cookie->qpay_invoice_id, $this->context->cookie->qpay_cart_id);
            Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cartId . '&id_module=' . $this->module->id . '&key=' . $customer->secure_key);
        } else {
            $this->errors[] = 'Payment not confirmed yet';
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
        }
    }
}
