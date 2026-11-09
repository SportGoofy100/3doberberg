<?php
namespace Opencart\Catalog\Controller\Product;
/**
 * Class Frame
 *
 * Konfigurator für Schattenfugenrahmen nach Maß (gedruckt oder nur STL-Dateien).
 *
 * @package Opencart\Catalog\Controller\Product
 */
class Frame extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('product/frame');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setDescription($this->language->get('meta_description'));

		$language = $this->config->get('config_language');

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $language)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('product/frame', 'language=' . $language)
		];

		$this->load->model('catalog/frame');

		// Beide Produkte müssen im Admin angelegt sein
		$data['available'] = $this->model_catalog_frame->getProductId(\Opencart\Catalog\Model\Catalog\Frame::MODEL_PRINT) && $this->model_catalog_frame->getProductId(\Opencart\Catalog\Model\Catalog\Frame::MODEL_STL);

		$data['limits'] = \Opencart\Catalog\Model\Catalog\Frame::LIMITS;
		$data['print_bed'] = \Opencart\Catalog\Model\Catalog\Frame::PRINT_BED_DEFAULT;

		$data['calculate'] = $this->url->link('product/frame.calculate', 'language=' . $language);
		$data['add'] = $this->url->link('product/frame.add', 'language=' . $language);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('product/frame', $data));
	}

	/**
	 * Calculate
	 *
	 * Live-Berechnung für die Anzeige im Konfigurator (JSON).
	 *
	 * @return void
	 */
	public function calculate(): void {
		$this->load->language('product/frame');
		$this->load->model('catalog/frame');

		$json = [];

		[$values, $error] = $this->model_catalog_frame->validate($this->request->post);

		if ($error) {
			$json['error'] = $this->errorTexts($error);
		} else {
			$result = $this->model_catalog_frame->calculate($values);

			$json['result'] = $result;
			$json['price_text'] = $this->formatPrice($values['variante'], $result['price']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Add
	 *
	 * Legt den konfigurierten Rahmen in den Warenkorb. Der Preis wird hier neu berechnet und
	 * als Override an der Warenkorbzeile gespeichert, Werte aus dem Browser werden nicht übernommen.
	 *
	 * @return void
	 */
	public function add(): void {
		$this->load->language('product/frame');
		$this->load->model('catalog/frame');
		$this->load->model('catalog/product');

		$json = [];

		[$values, $error] = $this->model_catalog_frame->validate($this->request->post);

		if ($error) {
			$json['error'] = $this->errorTexts($error);
		} else {
			$model = ($values['variante'] == 'stl') ? \Opencart\Catalog\Model\Catalog\Frame::MODEL_STL : \Opencart\Catalog\Model\Catalog\Frame::MODEL_PRINT;

			$product_id = $this->model_catalog_frame->getProductId($model);

			if (!$product_id) {
				$json['error']['warning'] = $this->language->get('error_product');
			} else {
				$result = $this->model_catalog_frame->calculate($values);

				// Zeilen mit unterschiedlichen Maßen dürfen nicht zusammengelegt werden -> Maße als Schlüssel im Option-Feld
				$key = implode('x', [$values['variante'], $values['laenge'], $values['breite'], $values['tiefe'], $values['fuge'], $values['druckbett']]);

				$this->cart->add($product_id, 1, ['frame' => $key], 0, [
					'name'  => $this->model_catalog_frame->describe($values),
					'price' => $result['price']
				]);

				unset($this->session->data['order_id']);
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);

				$json['success'] = $this->language->get('text_success');
				$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Format Price
	 *
	 * Preis so anzeigen, wie er im Warenkorb erscheint (inkl. Steuer, falls an der Steuerklasse des Produkts eingestellt).
	 *
	 * @param string $variant
	 * @param float  $price
	 *
	 * @return string
	 */
	protected function formatPrice(string $variant, float $price): string {
		$model = ($variant == 'stl') ? \Opencart\Catalog\Model\Catalog\Frame::MODEL_STL : \Opencart\Catalog\Model\Catalog\Frame::MODEL_PRINT;

		$this->load->model('catalog/product');

		$product_id = $this->model_catalog_frame->getProductId($model);
		$product_info = $product_id ? $this->model_catalog_product->getProduct($product_id) : [];

		$tax_class_id = $product_info ? (int)$product_info['tax_class_id'] : 0;

		return $this->currency->format($this->tax->calculate($price, $tax_class_id, $this->config->get('config_tax')), $this->session->data['currency']);
	}

	/**
	 * Error Texts
	 *
	 * @param array<string, string> $error
	 *
	 * @return array<string, string>
	 */
	protected function errorTexts(array $error): array {
		$texts = [];

		foreach ($error as $field => $type) {
			if ($type == 'range') {
				[$min, $max] = \Opencart\Catalog\Model\Catalog\Frame::LIMITS[$field];

				$texts[$field] = sprintf($this->language->get('error_range'), $min, $max);
			} else {
				$texts[$field] = $this->language->get('error_required');
			}
		}

		return $texts;
	}
}
