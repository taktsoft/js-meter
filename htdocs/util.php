<?php
	/**
	 * Kürzt Text automatisch auf die angegebene Länge und fügt
	 * ein <abbr>-Tag mit passendem Tooltip dazu.
	 * @param object $str zu kürzender String
	 * @param object $max[optional] Maximallänge (Standard: 30)
	 */
	function shorten($str, $max = 30) {
		if (strlen($str) > $max) {
			$short = htmlentities(substr($str, 0, $max));
			return '<abbr title="' . htmlentities($str) . '">' . $short . '&hellip;</abbr>';
		} else {
			return htmlentities($str);
		}
	}
	
	/**
	 * Prozentwerte erreichnen und schön formatieren. Bsp: 87.6%
	 * @return string
	 * @param object $value auszugebener Wert
	 * @param object $max Maximalwert (100%)
	 */
	function percent($value, $max) {
		$value = intval($value);
		$max = intval($max);
		
		if ($max == 0) {
			return '0.0%';
		} else {
			return sprintf('%.1f', 100 * $value / $max) . '%';
		}
	}	
	
	/**
	 * Gibt einen Schalter zum Ein- und Ausblenden der Details zurück.
	 * @return string
	 * @param string $class CSS-Klassenname des Detailblocks
	 */
	function detailsToggle($class) {
		return '<input type="button" value="+" class="detailToggle" />';
	}	
?>