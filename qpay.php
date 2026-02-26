<?php

if (!defined('_PS_VERSION_')) exit;

require_once __DIR__ . '/classes/QPayApi.php';

class QPay extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'qpay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'QPay SDK';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('QPay Payment');
        $this->description = $this->l('Accept payments via QPay V2');
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('paymentOptions')
            && $this->registerHook('paymentReturn');
    }

    public function uninstall()
    {
        Configuration::deleteByName('QPAY_BASE_URL');
        Configuration::deleteByName('QPAY_USERNAME');
        Configuration::deleteByName('QPAY_PASSWORD');
        Configuration::deleteByName('QPAY_INVOICE_CODE');
        Configuration::deleteByName('QPAY_CALLBACK_URL');
        return parent::uninstall();
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitQPayModule')) {
            Configuration::updateValue('QPAY_BASE_URL', Tools::getValue('QPAY_BASE_URL'));
            Configuration::updateValue('QPAY_USERNAME', Tools::getValue('QPAY_USERNAME'));
            Configuration::updateValue('QPAY_PASSWORD', Tools::getValue('QPAY_PASSWORD'));
            Configuration::updateValue('QPAY_INVOICE_CODE', Tools::getValue('QPAY_INVOICE_CODE'));
            Configuration::updateValue('QPAY_CALLBACK_URL', Tools::getValue('QPAY_CALLBACK_URL'));
            $output .= $this->displayConfirmation($this->l('Settings saved'));
        }
        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $fields = [
            'form' => [
                'legend' => ['title' => $this->l('QPay Settings')],
                'input' => [
                    ['type' => 'text', 'label' => $this->l('API Base URL'), 'name' => 'QPAY_BASE_URL', 'required' => true],
                    ['type' => 'text', 'label' => $this->l('Username'), 'name' => 'QPAY_USERNAME', 'required' => true],
                    ['type' => 'password', 'label' => $this->l('Password'), 'name' => 'QPAY_PASSWORD', 'required' => true],
                    ['type' => 'text', 'label' => $this->l('Invoice Code'), 'name' => 'QPAY_INVOICE_CODE', 'required' => true],
                    ['type' => 'text', 'label' => $this->l('Callback URL'), 'name' => 'QPAY_CALLBACK_URL'],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],
        ];

        $helper = new HelperForm();
        $helper->submit_action = 'submitQPayModule';
        $helper->fields_value = [
            'QPAY_BASE_URL' => Configuration::get('QPAY_BASE_URL') ?: 'https://merchant.qpay.mn',
            'QPAY_USERNAME' => Configuration::get('QPAY_USERNAME'),
            'QPAY_PASSWORD' => Configuration::get('QPAY_PASSWORD'),
            'QPAY_INVOICE_CODE' => Configuration::get('QPAY_INVOICE_CODE'),
            'QPAY_CALLBACK_URL' => Configuration::get('QPAY_CALLBACK_URL'),
        ];

        return $helper->generateForm([$fields]);
    }

    public function hookPaymentOptions($params)
    {
        $option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $option->setModuleName($this->name)
            ->setCallToActionText($this->l('QPay-ээр төлөх'))
            ->setAction($this->context->link->getModuleLink($this->name, 'payment', [], true));

        return [$option];
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) return '';
        $this->smarty->assign('status', 'ok');
        return $this->fetch('module:qpay/views/templates/hook/payment_return.tpl');
    }
}
