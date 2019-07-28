<?php
namespace woo\utility;

class CdataXml
{
	public static function fromArray($input, $options = array()) {
		if (!is_array($input) || count($input) !== 1) {
			die('Invalid input.');
		}
		$key = key($input);
		if (is_int($key)) {
			die('The key of input must be alphanumeric');
		}

		if (!is_array($options)) {
			$options = array('format' => (string)$options);
		}
		$defaults = array(
			'format' => 'tags',
			'version' => '1.0',
			'encoding' => 'utf-8',
			'return' => 'simplexml',
			'pretty' => false
		);
		$options = array_merge($defaults, $options);

		$dom = new \DOMDocument($options['version'], $options['encoding']);
		if ($options['pretty']) {
			$dom->formatOutput = true;
		}
		self::_fromArray($dom, $dom, $input, $options['format']);

		$options['return'] = strtolower($options['return']);
		if ($options['return'] === 'simplexml' || $options['return'] === 'simplexmlelement') {
			return new \SimpleXMLElement($dom->saveXML());
		}
		return $dom;
	}
	
	protected static function _fromArray($dom, $node, &$data, $format) {
		if (empty($data) || !is_array($data)) {
			return;
		}
		foreach ($data as $key => $value) {
			if (is_string($key)) {
				if (!is_array($value)) {
					if (is_bool($value)) {
						$value = (int)$value;
					} elseif ($value === null) {
						$value = '';
					}
					$isNamespace = strpos($key, 'xmlns:');
					if ($isNamespace !== false) {
						$node->setAttributeNS('http://www.w3.org/2000/xmlns/', $key, $value);
						continue;
					}
					if ($key[0] !== '@' && $format === 'tags') {
						$child = null;
						if (!is_numeric($value)) {
							// Escape special characters
							// http://www.w3.org/TR/REC-xml/#syntax
							// https://bugs.php.net/bug.php?id=36795
							$child = $dom->createElement($key, '');
							$child->appendChild(new \DOMText($value));
						} else {
							$child = $dom->createElement($key, $value);
						}
						$node->appendChild($child);
					} else {
						if ($key[0] === '@') {
							$key = substr($key, 1);
						}
						$attribute = $dom->createAttribute($key);
						$attribute->appendChild($dom->createTextNode($value));
						$node->appendChild($attribute);
					}
				} else {
					if ($key[0] === '@') {
						die('Invalid array');
					}
					if (is_numeric(implode('', array_keys($value)))) { // List
						foreach ($value as $item) {
							$itemData = compact('dom', 'node', 'key', 'format');
							$itemData['value'] = $item;
							self::_createChild($itemData);
						}
					} else { // Struct
						self::_createChild(compact('dom', 'node', 'key', 'value', 'format'));
					}
				}
			} else {
				die('Invalid array');
			}
		}
	}
	
	protected static function _createChild($data) {
		extract($data);
		$childNS = $childValue = null;
		if (is_array($value)) {
			if (isset($value['@'])) {
				$childValue = (string)$value['@'];
				unset($value['@']);
			}
			if (isset($value['xmlns:'])) {
				$childNS = $value['xmlns:'];
				unset($value['xmlns:']);
			}
			if (isset($value['$'])) {
				$childValue = (string)$value['$'];
				unset($value['$']);
				$isCDATA=true;
			}
		} elseif (!empty($value) || $value === 0) {
			$childValue = (string)$value;
		}

		$child = $dom->createElement($key);
		if ($childValue !== null) {
			if($isCDATA){
				$child->appendChild($dom->createCDATASection($childValue));
			}
			else{
				$child->appendChild($dom->createTextNode($childValue));
			}
		}
		if ($childNS) {
			$child->setAttribute('xmlns', $childNS);
		}

		self::_fromArray($dom, $child, $value, $format);
		$node->appendChild($child);
	}
}
