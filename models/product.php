<?php

namespace XmlImportExtension\Models;

use Serializable;

class Product implements Serializable {

	/** @var string */
	public $id;

	/** @var int */
	public $categoryId;

	/** @var int */
	public $groupId;

	/** @var string */
	public $category;

	/** @var string */
	public $link;

	/** @var string */
	public $ean;

	/** @var string */
	public $title;

	/** @var float */
	public $price;

	/** @var string */
	public $currency;

	/** @var boolean */
	public $available;

	/** @var string */
	public $description;

	/** @var string */
	public $brand;

	/** @var string */
	public $imageUrl;

	/** @var string */
	public $thumbnailName;

	/** @var array */
	public $productDetails;

	/** @var boolean */
	public $importProduct;

	/** @var boolean */
	public $variant;

	/** @var string */
	public $feedUrl;

	/**
	 * Make model from assoc. array
	 *
	 * @param array $data
	 * @return \XmlImportExtension\Models\Product
	 */
	public static function fromArray($data) {
		$self = new Product();
		foreach ($data as $key => $value) {
			$self->$key = $value;
		}
		return $self;
	}

	/**
	 * The model constructor
	 *
	 * @param string $id
	 * @param int $categoryId
	 * @param string $category
	 * @param string $link
	 * @param string $ean
	 * @param string $title
	 * @param float $price
	 * @param boolean $available
	 * @param string $description
	 * @param string $brand
	 * @param string $imageUrl
	 * @param string $thumbnailName
	 * @param array $productDetails
	 * @param boolean $importProduct
	 * @param boolean $variant
	 * @param string $feedUrl
	 */
	function __construct(
		$id = null,
		$categoryId = null,
		$groupId = null,
		$category = null,
		$link = null,
		$ean = null,
		$title = null,
		$price = null,
		$currency = null,
		$available = null,
		$description = null,
		$brand = null,
		$imageUrl = null,
		$thumbnailName = null,
		$productDetails = null,
		$importProduct = null,
		$variant = null,
		$feedUrl = null
	) {
		$this->id = $id;
		$this->categoryId = $categoryId;
		$this->groupId = $groupId;
		$this->category = $category;
		$this->link = $link;
		$this->ean = $ean;
		$this->title = $title;
		$this->price = $price;
		$this->currency = $currency;
		$this->available = $available;
		$this->description = $description;
		$this->brand = $brand;
		$this->imageUrl = $imageUrl;
		$this->thumbnailName = $thumbnailName;
		$this->productDetails = $productDetails;
		$this->importProduct = $importProduct;
		$this->variant = $variant;
		$this->feedUrl = $feedUrl;
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
		var_dump($data); die();
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}
}
