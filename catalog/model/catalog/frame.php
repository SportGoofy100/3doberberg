<?php
namespace Opencart\Catalog\Model\Catalog;
/**
 * Class Frame
 *
 * Rechenmodell des Schattenfugenrahmen-Konfigurators.
 * Bildet die Geometrie-Berechnung des OpenSCAD-Generators nach (Segmentaufteilung, Teileanzahl)
 * und berechnet den Preis. Der Preis wird IMMER hier auf dem Server berechnet, nie aus dem Browser übernommen.
 *
 * Can be called using $this->load->model('catalog/frame');
 *
 * @package Opencart\Catalog\Model\Catalog
 */
class Frame extends \Opencart\System\Engine\Model {
	/**
	 * Modellnummern der beiden Produkte, die im Admin angelegt werden müssen
	 * (Katalog > Produkte, Feld "Modell"). Der Preis des Produkts selbst ist egal, er wird überschrieben.
	 */
	public const MODEL_PRINT = 'SFR-DRUCK';
	public const MODEL_STL = 'SFR-STL';

	/**
	 * PREISE - hier anpassen. Alle Werte sind Platzhalter und müssen von dir überprüft werden!
	 * Beträge wie beim normalen Produktpreis: gilt Steuer (Steuerklasse am Produkt), kommt sie obendrauf.
	 */
	public const PRICE_PRINT_BASE = 12.00;       // Grundpreis gedruckter Rahmen (Aufwand, Verpackung, Schrauben ...) in EUR
	public const PRICE_PER_GRAM = 0.07;          // Preis pro Gramm verbrauchtes Filament inkl. Maschinenzeit in EUR
	public const FILL_FACTOR = 0.45;             // Anteil des Vollvolumens, der real als Filament verbraucht wird (Infill, Wände)
	public const FILAMENT_DENSITY = 1.24;        // g/cm³ (PLA)
	public const PRICE_PRINT_MIN = 29.00;        // Mindestpreis gedruckter Rahmen in EUR
	public const PRICE_STL = 14.90;              // Pauschalpreis STL-Dateien in EUR

	/**
	 * Feste Konstanten des Generators (wie in der .scad-Datei)
	 */
	public const WALL = 8.0;                     // WAND_DICKE
	public const FRONT_EDGE = 40.0;              // RAHMEN_VORDERKANTE_NETTO
	public const DEPTH_TOLERANCE = 1.0;          // BILD_TIEFEN_TOLERANZ
	public const PRINT_BED_DEFAULT = 256;        // eigener Drucker für die gedruckte Variante

	/**
	 * Zulässige Bereiche (mm)
	 */
	public const LIMITS = [
		'laenge'   => [100, 2000],
		'breite'   => [100, 2000],
		'tiefe'    => [5, 80],
		'fuge'     => [0, 30],
		'druckbett' => [150, 400]
	];

	/**
	 * Get Product Id
	 *
	 * @param string $model
	 *
	 * @return int 0 wenn kein aktives Produkt mit dieser Modellnummer existiert
	 */
	public function getProductId(string $model): int {
		$query = $this->db->query("SELECT `p`.`product_id` FROM `" . DB_PREFIX . "product` `p` LEFT JOIN `" . DB_PREFIX . "product_to_store` `p2s` ON (`p`.`product_id` = `p2s`.`product_id`) WHERE `p`.`model` = '" . $this->db->escape($model) . "' AND `p`.`status` = '1' AND `p`.`date_available` <= NOW() AND `p2s`.`store_id` = '" . (int)$this->config->get('config_store_id') . "' LIMIT 1");

		return $query->num_rows ? (int)$query->row['product_id'] : 0;
	}

	/**
	 * Validate
	 *
	 * @param array<string, mixed> $post Rohdaten aus dem Formular
	 *
	 * @return array{0: array<string, mixed>, 1: array<string, string>} [bereinigte Werte, Fehler je Feld]
	 */
	public function validate(array $post): array {
		$error = [];

		$values = [
			'variante' => (($post['variante'] ?? '') === 'stl') ? 'stl' : 'print'
		];

		foreach (self::LIMITS as $key => [$min, $max]) {
			$raw = str_replace(',', '.', trim((string)($post[$key] ?? '')));

			if ($key == 'druckbett' && $values['variante'] == 'print') {
				$values[$key] = self::PRINT_BED_DEFAULT;

				continue;
			}

			if ($raw === '' || !is_numeric($raw)) {
				$error[$key] = 'required';
			} else {
				$value = round((float)$raw, 1);

				if ($value < $min || $value > $max) {
					$error[$key] = 'range';
				}

				$values[$key] = $value;
			}
		}

		return [$values, $error];
	}

	/**
	 * Segment Info
	 *
	 * Entspricht segment_info() im Generator.
	 *
	 * @param float $total
	 * @param float $max
	 *
	 * @return array{0: int, 1: float} [Anzahl, Länge je Segment]
	 */
	protected function segmentInfo(float $total, float $max): array {
		if ($total <= 0) {
			return [0, 0.0];
		}

		$n = (int)ceil($total / $max);

		return [$n, $total / $n];
	}

	/**
	 * Calculate
	 *
	 * @param array<string, mixed> $v bereinigte Werte aus validate()
	 *
	 * @return array<string, mixed>
	 */
	public function calculate(array $v): array {
		$profile_width = self::WALL + $v['tiefe'] + self::DEPTH_TOLERANCE;
		$wall_width = self::FRONT_EDGE + self::WALL;

		$inner_x = $v['breite'] + 2 * (self::WALL + $v['fuge']);
		$inner_z = $v['laenge'] + 2 * (self::WALL + $v['fuge']);

		$corner = min($v['druckbett'] / 2.5, 80);

		[$n_long, $l_long] = $this->segmentInfo($inner_z - 2 * $corner, $v['druckbett'] - 10);
		[$n_short, $l_short] = $this->segmentInfo($inner_x - 2 * $corner, $v['druckbett'] - 10);

		// Profilfläche: Winkelprofil aus Flansch (Breite 48) und Außenwand (Dicke 8)
		$area = $wall_width * $profile_width - ($wall_width - self::WALL) * ($profile_width - self::WALL);
		$volume_cm3 = $area * 2 * ($inner_x + $inner_z) / 1000;
		$grams = $volume_cm3 * self::FILAMENT_DENSITY * self::FILL_FACTOR;

		if ($v['variante'] == 'stl') {
			$price = self::PRICE_STL;
		} else {
			$price = max(self::PRICE_PRINT_MIN, self::PRICE_PRINT_BASE + $grams * self::PRICE_PER_GRAM);
		}

		return [
			'outer_x'  => round($inner_x, 1),
			'outer_z'  => round($inner_z, 1),
			'corner'   => round($corner, 1),
			'n_long'   => $n_long,
			'l_long'   => round($l_long, 1),
			'n_short'  => $n_short,
			'l_short'  => round($l_short, 1),
			'parts'    => 4 + 2 * $n_long + 2 * $n_short,
			'grams'    => (int)round($grams),
			'price'    => round($price, 2)
		];
	}

	/**
	 * Describe
	 *
	 * Kurzbeschreibung für Warenkorb, Bestellung und E-Mail.
	 *
	 * @param array<string, mixed> $v
	 *
	 * @return string
	 */
	public function describe(array $v): string {
		$f = fn(float $n): string => rtrim(rtrim(number_format($n, 1, ',', ''), '0'), ',');

		$text = 'Schattenfugenrahmen nach Maß – Bild ' . $f($v['laenge']) . ' × ' . $f($v['breite']) . ' × ' . $f($v['tiefe']) . ' mm, Fugenabstand ' . $f($v['fuge']) . ' mm';

		if ($v['variante'] == 'stl') {
			$text .= ', STL-Dateien (Druckbett ' . $f($v['druckbett']) . ' mm)';
		} else {
			$text .= ', gedruckt';
		}

		return $text;
	}
}
