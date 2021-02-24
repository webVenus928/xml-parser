<?php

namespace XmlImportExtension\Models;

use SimpleXMLElement;

class MergadoFeed extends AbstractFeed implements FeedInterface {

	const KEY_CATEGORIES = "//CATEGORY";
	const KEY_ID = "ITEM_ID";
	const KEY_CATEGORY = "CATEGORY";
	const KEY_LINK = "URL";
	const KEY_TITLE = "NAME_EXACT";
	const KEY_PRICE = "PRICE_VAT";
	const KEY_AVAILABILITY = "AVAILABILITY";
	const KEY_DESCRIPTION = "description";
	const KEY_BRAND = "PRODUCER";
	const KEY_IMAGES = "IMAGE_ALTERNATIVE";
	const KEY_VARIANT_ATTR = "attributes";
	const KEY_VARIANT_GRP = "ITEMGROUP_ID";
	const KEY_MPN = "mpn";
	const KEY_IMAGE_URL = "IMAGE";
	const KEY_DETAILS = "product_detail";
	const KEY_ATTR_NAME = "attribute_name";
	const KEY_ATTR_VAL = "attribute_value";
	const VAL_NOT_AVAILABLE = "out of stock";
	const KEY_PREFIX = "g:";

    /**
     * Construct model from cURL response body
     *
     * @param string $response
     * @return $this
     */
    public static function parseFromResponse($response) {
//var_dump($response); die();
//echo "<pre>";
        $response = new SimpleXMLElement($response);
        $self = new mergadoFeed();

        // Extract categories
				$self->categories = [];
				foreach ($response->ITEM as $item) {
					var_dump($item->CATEGORY);
					$category = $item->xpath(self::KEY_CATEGORY);
					var_dump($category);
				if (array_search($category, $self->categories) === false) {
					$self->categories[] = $category;
				}
			}
var_dump($self->categories);
		// Extract products
		//var_dump($response); die();
		foreach ($response->ITEM as $item) {

			//var_dump($item);

			//if ($item->self::KEY_VARIANT_GRP != $item->self::KEY_ID) continue;
			//$keyVariant = self::getElementValueOrNull($item, self::KEY_VARIANT_GRP);
			//var_dump("<br>keyVariant: ".$keyVariant." end");
			$category = $item->self::KEY_CATEGORY;
			//var_dump("<br>category: ".$category);
			$price = $item->self::KEY_PRICE;
			//var_dump("<br>price: ".$price);
			$price = explode(" ", $price);

			$imageUrl = $item->self::KEY_IMAGE_URL;

//die();
/*
			if (empty($imageUrl)) {
				$images = $item->xpath(self::KEY_IMAGES) ?: $item->xpath(self::KEY_IMAGES);
				if (is_array($images) && array_key_exists(0, $images) ) {
					$imageUrl = self::getElementValueOrNull($images[0], self::KEY_IMAGE_URL) ?: self::getElementValueOrNull($images[0], self::KEY_IMAGE_URL);
				}
			}
			$x_productDetails = $item->xpath(self::KEY_DETAILS) ?: $item->xpath(self::KEY_DETAILS);
			$y_productDetails = array();
			if (is_array($x_productDetails)) {
				foreach ($x_productDetails as $key => $value) {
					$y_productDetails[$key]["name"] = self::getElementValueOrNull($value[0], self::KEY_ATTR_NAME) ?: self::getElementValueOrNull($value[0], self::KEY_ATTR_NAME);
					$y_productDetails[$key]["value"] = self::getElementValueOrNull($value[0], self::KEY_ATTR_VAL) ?: self::getElementValueOrNull($value[0], self::KEY_ATTR_VAL);
				}
			}*/
			$y_productDetails = array();
			/*
			echo "<pre>";
			var_dump($y_productDetails);
			echo "</pre>";
			die();
			*/
			$available_product = false;
			if (self::getElementValueOrNull($item, self::KEY_AVAILABILITY) != NULL ) {
				$available_product = self::getElementValueOrNull($item, self::KEY_AVAILABILITY)	!= self::VAL_NOT_AVAILABLE;
			} elseif (self::getElementValueOrNull($item, self::KEY_AVAILABILITY) != NULL) {
				$available_product = self::getElementValueOrNull($item, self::KEY_AVAILABILITY)	!= self::VAL_NOT_AVAILABLE;
			}
			//var_dump($price[0]);

			$self->products[] = new Product(
				self::getElementValueOrNull($item, self::KEY_ID),
				array_search($category, $self->categories),
				$keyVariant,
				$category,
				self::getElementValueOrNull($item, self::KEY_LINK),
				null,
				self::getElementValueOrNull($item, self::KEY_TITLE),
				floatval($price[0]),
				array_key_exists(1, $price) ? $price[1] : null,
				$available_product,
				self::getElementValueOrNull($item, self::KEY_DESCRIPTION),
				self::getElementValueOrNull($item, self::KEY_BRAND),
				$imageUrl,
				null,
				null //$y_productDetails
			);

		}

//var_dump($self);

      return $self;
    }

    /**
	 * Extract value by property key
	 *
	 * @param SimpleXMLElement $item
	 * @param string $key
	 * @return mixed
	 */
	private static function getElementValueOrNull(SimpleXMLElement $item, string $key) {
		$res = $item->xpath($key);
		//var_dump($key);
		return array_key_exists(0, $res) && !empty((string)$res[0]) ? (string)$res[0] : null;
	}
}
