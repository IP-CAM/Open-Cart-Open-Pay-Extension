<?php
/**
 * Openpay Magento extension
 * @author Feather Mexico
 * @class ControllerPaymentOpenPay
 * Front-End Controller class for handling OpenPay's form + API Connection
*/
class ControllerPaymentOpenPay extends Controller {
  public function index() {
    $this->language->load('payment/open_pay');

    $data['text_credit_card'] = $this->language->get('text_credit_card');
    $data['text_wait'] = $this->language->get('text_wait');
    $data['text_loading'] = $this->language->get('text_loading');

    $data['entry_cc_holder_name'] = $this->language->get('entry_cc_holder_name');
    $data['entry_cc_type'] = $this->language->get('entry_cc_type');
    $data['entry_cc_number'] = $this->language->get('entry_cc_number');
    $data['entry_cc_expire_date'] = $this->language->get('entry_cc_expire_date');
    $data['entry_cc_cvv2'] = $this->language->get('entry_cc_cvv2');

    $data['help_start_date'] = $this->language->get('help_start_date');
    $data['help_issue'] = $this->language->get('help_issue');

    $data['button_confirm'] = $this->language->get('button_confirm');

    $data['cards'] = array();

    $data['cards'][] = array(
      'text'  => 'Visa',
      'value' => 'VISA'
    );

    $data['cards'][] = array(
      'text'  => 'MasterCard',
      'value' => 'MASTERCARD'
    );

    $data['cards'][] = array(
      'text'  => 'American Express',
      'value' => 'AMEX'
    );

    $data['months'] = array();

    for ($i = 1; $i <= 12; $i++) {
      $data['months'][] = array(
        'text'  => strftime('%B', mktime(0, 0, 0, $i, 1, 2000)),
        'value' => sprintf('%02d', $i)
      );
    }

    $today = getdate();

    $data['year_expire'] = array();

    for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
      $data['year_expire'][] = array(
        'text'  => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
        'value' => strftime('%y', mktime(0, 0, 0, 1, 1, $i))
      );
    }

    if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/open_pay.tpl')) {
      return $this->load->view($this->config->get('config_template') . '/template/payment/open_pay.tpl', $data);
    } else {
      return $this->load->view('default/template/payment/open_pay.tpl', $data);
    }
  }

  /*
   * @method send
   * Handles AJAX request and creates an OpenPay Charge using PHP SDK
  */
  public function send() {
    // Load OpenPay Lib
    require_once(DIR_SYSTEM . '/library/openpay/Openpay.php');
    // load open cart order
    $this->load->model('checkout/order');
    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
    $json = array();
    // open cart config
    $mercant_id = $this->config->get('open_pay_id');
    $sk = $this->config->get('open_pay_sk');
    $order_id = $order_info['order_id'];
    $openpay = Openpay::getInstance($mercant_id, $sk);
    if (!$this->config->get('open_pay_is_sandbox')){
      Openpay::setProductionMode(true);
    }
    // OpenPay API Doc
    $card = array(
      'card_number' => str_replace(' ', '', $this->request->post['cc_number']),
      'holder_name' => $this->request->post['cc_holder_name'],
      'expiration_year' => $this->request->post['cc_expire_date_year'],
      'expiration_month' => $this->request->post['cc_expire_date_month'],
      'cvv2' => $this->request->post['cc_cvv2']
    );
    $metadata = array(
      'ip_addr' => $this->request->server['REMOTE_ADDR']
    );
    $customer = array(
      'name' => $order_info['payment_firstname'],
      'last_name' => $order_info['payment_lastname'],
      'phone_number' => $order_info['telephone'],
      'email' => $order_info['email']
    );
    $chargeRequest = array(
      'method' => 'card',
      'card' => $card,
      'amount' => $this->currency->format($order_info['total'], '', '', false),
      'currency' => $order_info['currency_code'],
      'description' => "Order {$order_id}",
      'order_id' => $order_id,
      'device_session_id' => session_id(),
      'customer' => $customer,
      'metadata' => $metadata
    );
    try{
        $charge = $openpay->charges->create($chargeRequest);
        // 5 - order status complete, no constant found
        $this->model_checkout_order->addOrderHistory($order_id, 5, $charge->id, true);
        $json['success'] = $this->url->link('checkout/success');
    }
    catch(Exception $e){
      $json['error'] = $e->getMessage();
    }
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));

  }
}
