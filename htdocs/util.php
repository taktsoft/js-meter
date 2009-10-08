<?php
	/**
	 * K�rzt Text automatisch auf die angegebene L�nge und f�gt
	 * ein <abbr>-Tag mit passendem Tooltip dazu.
	 * @param object $str zu k�rzender String
	 * @param object $max[optional] Maximall�nge (Standard: 30)
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
	 * Prozentwerte erreichnen und sch�n formatieren. Bsp: 87.6%
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
	 * Gibt einen Schalter zum Ein- und Ausblenden der Details zur�ck.
	 * @return string
	 * @param string $class CSS-Klassenname des Detailblocks
	 */
	function detailsToggle($class) {
		return '<input type="button" value="+" class="detailToggle" />';
	}	
?>