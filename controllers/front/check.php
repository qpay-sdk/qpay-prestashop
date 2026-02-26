<?php

class QPayCheckModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $invoiceId = $this->context->cookie->qpay_invoice_id ?? '';
        if (!$invoiceId) {
            $this->ajaxRender(json_encode(['paid' => false]));
            return;
        }

        $api = new QPayApi();
        $result = $api->checkPayment($invoiceId);
        $paid = !empty($result['rows']);

        $this->ajaxRender(json_encode(['paid' => $paid]));
    }
}
