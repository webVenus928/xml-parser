<?php

namespace XmlImportExtension\Models;

interface FeedInterface {

  public static function parseFromResponse($response);

  public function getProducts();
	public function getCategories();
	public function getProductsByCategory($category);
}
