<?php

namespace XmlImportExtension\Models;

use Serializable;

abstract class AbstractFeed implements Serializable {

	const KEY_CATEGORIES = "categories";
	const KEY_PRODUCTS = "products";

	/** @var array */
	public $categories;

	/** @var array */
	public $products;

	/**
	 * The model constructor
	 *
	 * @param array $categories
	 * @param array $products
	 */
	function __construct($categories = null, $products = null) {
		$this->categories = $categories;
		$this->products = $products;
	}

	/**
	 * Serialize implementation
	 *
	 * @return string
	 */
	public function serialize() {
		return json_encode($this, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
	}

	/**
	 * Unserialize implementation
	 *
	 * @param string $serialized
	 */
	public function unserialize($serialized) {
		$data = json_decode($serialized, true);

		if (array_key_exists(self::KEY_CATEGORIES, $data)) {
			$this->categories = $data[self::KEY_CATEGORIES];
		}

		if (array_key_exists(self::KEY_PRODUCTS, $data)) {
			foreach ($data[self::KEY_PRODUCTS] as $values) {
				$this->products[] = Product::fromArray($values);
			}
		}
	}

    /**
	 * Get all products
	 *
	 * @return array
	 */
	public function getProducts() {
		return $this->products;
	}

	public function updateProduct($id, $data) {
		foreach ($this->products as $keyProduct => $valueProduct) {
			if ($valueProduct->id == $id) {
				foreach ($data as $keyData => $valueData) {
					$valueProduct->$keyData = $valueData;
				}
				return $valueProduct->id;
			}
		}

	}

	/**
	 * Get all categories
	 *
	 * @return array
	 */
	public function getCategories() {
		return $this->categories;
	}

    /**
	 * Get products by category
	 *
	 * @param mixed Category ID
	 * @return array
	 */
	public function getProductsByCategory($category) {
		return array_filter($this->products, function($item) use ($category) {
			return $item->category == $category;
		});
	}

    /**
     * Construct model from HTTP response body
     *
     * @param string $response
     * @return $this
     */
    public abstract static function parseFromResponse($response);
}
