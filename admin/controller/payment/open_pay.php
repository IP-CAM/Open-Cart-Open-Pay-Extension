<?php
/**
 * Openpay Magento extension
 * @author Feather Mexico
 * @class ControllerPaymentOpenPay
 * Controller Class for configuring OpenPay's API params  
*/
class ControllerPaymentOpenPay extends Controller {
  private $error = array();

  public function index() {
    $this->load->language('payment/open_pay');

    $this->document->setTitle($this->language->get('heading_title'));

    $this->load->model('setting/setting');

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      $this->model_setting_setting->editSetting('open_pay', $this->request->post);

      $this->session->data['success'] = $this->language->get('text_success');

      $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
    }

    $data['heading_title'] = $this->language->get('heading_title');

    $data['text_edit'] = $this->language->get('text_edit');
    $data['text_enabled'] = $this->language->get('text_enabled');
    $data['text_disabled'] = $this->language->get('text_disabled');
    $data['text_all_zones'] = $this->language->get('text_all_zones');
    $data['text_yes'] = $this->language->get('text_yes');
    $data['text_no'] = $this->language->get('text_no');
    $data['text_authorization'] = $this->language->get('text_authorization');
    $data['text_sale'] = $this->language->get('text_sale');

    $data['entry_id'] = $this->language->get('entry_id');
    $data['entry_private_key'] = $this->language->get('entry_private_key');
    $data['entry_public_key'] = $this->language->get('entry_public_key');
    $data['entry_test'] = $this->language->get('entry_test');
    $data['entry_status'] = $this->language->get('entry_status');
    $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');

    $data['help_test'] = $this->language->get('help_test');
    $data['help_total'] = $this->language->get('help_total');

    $data['button_save'] = $this->language->get('button_save');
    $data['button_cancel'] = $this->language->get('button_cancel');

    if (isset($this->error['warning'])) {
      $data['error_warning'] = $this->error['warning'];
    } else {
      $data['error_warning'] = '';
    }

    if (isset($this->error['id'])) {
      $data['error_id'] = $this->error['id'];
    } else {
      $data['error_id'] = '';
    }

    if (isset($this->error['sk'])) {
      $data['error_sk'] = $this->error['sk'];
    } else {
      $data['error_sk'] = '';
    }

    if (isset($this->error['pk'])) {
      $data['error_pk'] = $this->error['pk'];
    } else {
      $data['error_pk'] = '';
    }

    $data['breadcrumbs'] = array();

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
    );

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_payment'),
      'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
    );

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link('payment/open_pay', 'token=' . $this->session->data['token'], 'SSL')
    );

    $data['action'] = $this->url->link('payment/open_pay', 'token=' . $this->session->data['token'], 'SSL');

    $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

    if (isset($this->request->post['open_pay_id'])) {
      $data['open_pay_id'] = $this->request->post['open_pay_id'];
    } else {
      $data['open_pay_id'] = $this->config->get('open_pay_id');
    }

    if (isset($this->request->post['open_pay_sk'])) {
      $data['open_pay_sk'] = $this->request->post['open_pay_sk'];
    } else {
      $data['open_pay_sk'] = $this->config->get('open_pay_sk');
    }

    if (isset($this->request->post['open_pay_pk'])) {
      $data['open_pay_pk'] = $this->request->post['open_pay_pk'];
    } else {
      $data['open_pay_pk'] = $this->config->get('open_pay_pk');
    }

    if (isset($this->request->post['open_pay_is_sandbox'])) {
      $data['open_pay_is_sandbox'] = $this->request->post['open_pay_is_sandbox'];
    } else {
      $data['open_pay_is_sandbox'] = $this->config->get('open_pay_is_sandbox');
    }

    if (isset($this->request->post['open_pay_geo_zone_id'])) {
      $data['open_pay_geo_zone_id'] = $this->request->post['open_pay_geo_zone_id'];
    } else {
      $data['open_pay_geo_zone_id'] = $this->config->get('open_pay_geo_zone_id');
    }

    $this->load->model('localisation/geo_zone');

    $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['open_pay_status'])) {
			$data['open_pay_status'] = $this->request->post['open_pay_status'];
		} else {
			$data['open_pay_status'] = $this->config->get('open_pay_status');
		}

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('payment/open_pay.tpl', $data));
  }

  protected function validate() {
    if (!$this->user->hasPermission('modify', 'payment/open_pay')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    if (!$this->request->post['open_pay_id']) {
      $this->error['id'] = $this->language->get('error_id');
    }

    if (!$this->request->post['open_pay_sk']) {
      $this->error['sk'] = $this->language->get('error_private_key');
    }

    if (!$this->request->post['open_pay_pk']) {
      $this->error['pk'] = $this->language->get('error_public_key');
    }

    return !$this->error;
  }
}
