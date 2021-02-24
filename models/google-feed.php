<?php

namespace XmlImportExtension\Models;

use SimpleXMLElement;

class GoogleFeed extends AbstractFeed implements FeedInterface {

	const KEY_CATEGORIES = "//g:google_product_category";
	const KEY_ID = "id";
	const KEY_CATEGORY = "g:google_product_category";
	const KEY_LINK = "link";
	const KEY_TITLE = "title";
	const KEY_PRICE = "price";
	const KEY_AVAILABILITY = "availability";
	const KEY_DESCRIPTION = "description";
	const KEY_BRAND = "brand";
	const KEY_IMAGES = "images";
	const KEY_VARIANT_ATTR = "attributes";
	const KEY_VARIANT_GRP = "item_group_id";
	const KEY_MPN = "mpn";
	const KEY_IMAGE_URL = "image_link";
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
        $response = new SimpleXMLElement($response);
        $self = new GoogleFeed();

        // Extract categories
				$self->categories = [];
				foreach ($response->channel->xpath(self::KEY_CATEGORIES) as $item) {
					$category = (string)$item;

			if (array_search($category, $self->categories) === false) {
				$self->categories[] = $category;
			}
		}

		// Extract products
		foreach ($response->channel->item as $item) {
			//if (self::getElementValueOrNull($item, self::KEY_PREFIX.self::KEY_VARIANT_GRP) != self::getElementValueOrNull($item, self::KEY_PREFIX.self::KEY_MPN)) continue;
			$keyVariant = self::getElementValueOrNull($item, self::KEY_PREFIX.self::KEY_VARIANT_GRP) ?: self::getElementValueOrNull($item, self::KEY_VARIANT_GRP);
			$category = self::getElementValueOrNull($item, self::KEY_CATEGORY);
			$price = self::getElementValueOrNull($item, self::KEY_PREFIX.self::KEY_PRICE) ?: self::getElementValueOrNull($item, self::KEY_PRICE);
			$price = explode(" ", $price);
			$imageUrl = self::getElementValueOrNull($item, self::KEY_PREFIX.self::KEY_IMAGE_URL) ?: self::getElementValueOrNull($item, self::KEY_IMAGE_URL);
			if (empty($imageUrl)) {
				$images = $item->xpath(self::KEY_PREFIX.self::KEY_IMAGES) ?: $item->xpath(self::KEY_IMAGES);
				if (is_array($images) && array_key_exists(0, $images) ) {
					$imageUrl = self::getElementValueOrNull($images[0], self::KEY_PREFIX.self::KEY_IMAGE_URL) ?: self::getElementValueOrNull($images[0], self::KEY_IMAGE_URL);
				}
			}
			$x_productDetails = $item->xpath(self::KEY_PREFIX.self::KEY_DETAILS) ?: $item->xpath(self::KEY_DETAILS);
			$z_productDetails = $item->xpath(self::KEY_PREFIX.self::KEY_VARIANT_ATTR) ?: $item->xpath(self::KEY_VARIANT_ATTR);
			$zz_productDetails = array();
			var_dump($z_productDetails);
			die();
 			if (empty($x_productDetails)) {
				if (is_array($z_productDetails)) {

					/*
					foreach ($z_productDetails as $key => $value) {
						$z_productDetails[$key]["name"] = self::getElementValueOrNull($value[0], self::KEY_PREFIX.self::KEY_ATTR_NAME) ?: self::getElementValueOrNull($value[0], self::KEY_ATTR_NAME);
						$z_productDetails[$key]["value"] = self::getElementValueOrNull($value[0], self::KEY_PREFIX.self::KEY_ATTR_VAL) ?: self::getElementValueOrNull($value[0], self::KEY_ATTR_VAL);
					}
					*/
				}
			}
			$y_productDetails = array();
			if (is_array($x_productDetails)) {
				foreach ($x_productDetails as $key => $value) {
					$y_productDetails[$key]["name"] = self::getElementValueOrNull($value[0], self::KEY_PREFIX.self::KEY_ATTR_NAME) ?: self::getElementValueOrNull($value[0], self::KEY_ATTR_NAME);
					$y_productDetails[$key]["value"] = self::getElementValueOrNull($value[0], self::KEY_PREFIX.self::KEY_ATTR_VAL) ?: self::getElementValueOrNull($value[0], self::KEY_ATTR_VAL);
				}
			}
			/*
			echo "<pre>";
			var_dump($y_productDetails);
			echo "</pre>";
			die();
			*/
			$available_product = false;
			if (self::getElementValueOrNull($item, self::KEY_PREFIX.self::KEY_AVAILABILITY) != NULL ) {
				$available_product = self::getElementValueOrNull($item, self::KEY_PREFIX.self::KEY_AVAILABILITY)	!= self::VAL_NOT_AVAILABLE;
			} elseif (self::getElementValueOrNull($item, self::KEY_AVAILABILITY) != NULL) {
				$available_product = self::getElementValueOrNull($item, self::KEY_AVAILABILITY)	!= self::VAL_NOT_AVAILABLE;
			}
			//var_dump($price[0]);

			$self->products[] = new Product(
				self::getElementValueOrNull($item, self::KEY_PREFIX.self::KEY_ID) ?: self::getElementValueOrNull($item, self::KEY_ID),
				array_search($category, $self->categories),
				$keyVariant,
				$category,
				self::getElementValueOrNull($item, self::KEY_PREFIX.self::KEY_LINK) ?: self::getElementValueOrNull($item, self::KEY_LINK),
				null,
				self::getElementValueOrNull($item, self::KEY_PREFIX.self::KEY_TITLE) ?: self::getElementValueOrNull($item, self::KEY_TITLE),
				floatval($price[0]),
				array_key_exists(1, $price) ? $price[1] : null,
				$available_product,
				self::getElementValueOrNull($item, self::KEY_PREFIX.self::KEY_DESCRIPTION) ?: self::getElementValueOrNull($item, self::KEY_DESCRIPTION),
				self::getElementValueOrNull($item, self::KEY_PREFIX.self::KEY_BRAND) ?: self::getElementValueOrNull($item, self::KEY_BRAND),
				$imageUrl,
				null,
				$y_productDetails,
				null,
				null,
				null
			);

		}


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
		return array_key_exists(0, $res) && !empty((string)$res[0]) ? (string)$res[0] : null;
	}
}
