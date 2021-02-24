<?php

namespace XmlImportExtension\Services;

use Exception;
use XmlImportExtension\Models\FeedInterface;

class XmlFeedClient {

	const CURL_R_TIMEOUT_SEC = 30;
	const CURL_C_TIMEOUT_SEC = 10;
	const CACHE_LIFETIME_MIN = 7*24*60;
	const CACHE_PATH = "../../cache/";
	const CACHE_KEY_CATEGORIES = "categories";
	const CACHE_KEY_PRODUCTS = "products";

    protected const MODELS = [
        "google" => "XmlImportExtension\Models\GoogleFeed",
				"mergado" => "XmlImportExtension\Models\MergadoFeed"
    ];

    /**
     * @var string Feed URL
     */
    protected $feedUrl;

    /**
     * @var string Type of feed
     */
    protected $feedType;

    /**
	 * @var FeedInterface Feed content
	 */
	protected $feedContent;

    /**
     * Get available feed types
     *
     * @return array
     */
    public static function getFeedTypes() {
        return array_keys(self::MODELS);
    }

    /**
	 * Service constructor
	 *
	 * @param string $url
     * @throws Exception
     *
     */
	public function __construct($url, $type = "google") {
        if (!array_key_exists($type, self::MODELS)) {
            throw new Exception("Feed type " . $type . " not supported!");
        }
        $this->feedType = $type;
		$this->feedUrl = $url;
		//$this->load();
	}

	/**
	 * Get all products
	 *
	 * @return array
	 */
	public function getProducts() {
		return $this->feedContent != null ? $this->feedContent->getProducts() : [];
	}

	/**
	 * Update particular product
	 *
	 * @return array
	 */
	public function updateProduct($id, $data) {
		if ($this->feedContent != null) { $this->feedContent->updateProduct($id, $data); }
		return $this->cacheSet($this->feedContent->serialize());
	}

	/**
	 * Get all categories
	 *
	 * @return array
	 */
	public function getCategories() {
		return $this->feedContent != null ? $this->feedContent->getCategories() : [];
	}

	/**
	 * Get products by category
	 *
	 * @param mixed Category ID
	 * @return array
	 */
	public function getProductsByCategory($category) {
        return $this->feedContent != null ? $this->feedContent->getProductsByCategory($category) : [];
	}

	/**
	 * Load products and categories
	 */
	public function load() {
    $model = self::MODELS[$this->feedType];
		$cached = $this->cacheGet();

		if (empty($cached)) {
      $this->feedContent = $model::parseFromResponse($this->query());
			$this->cacheSet($this->feedContent->serialize());
		} else {
			$this->feedContent = new $model();
		    $this->feedContent->unserialize($cached);
		}
	}

	/**
	 * Query feed
	 *
	 * @param string $url
	 * @return string
	 * @throws Exception
	 */
	protected function query() {
		$ch = curl_init($this->feedUrl);

		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Accept: application/xml",
			"Content-Type: application/xml"
		]);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, self::CURL_R_TIMEOUT_SEC);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CURL_C_TIMEOUT_SEC);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);

		if (curl_error($ch)) {
			throw new Exception("cURL error: " . curl_error($ch));
		}

		curl_close($ch);
		return $response;
	}

	/**
	 * Get file cache content or false
	 *
	 * @return boolean
	 */
	protected function cacheGet() {
		$filename = $this->getCacheFilename();

		if (!file_exists($filename)) {
			return false;
		}

		if (filemtime($filename) < time() - self::CACHE_LIFETIME_MIN * 60) {
			unlink($filename);
			return false;
		}

		return file_get_contents($filename);
	}

	/**
	 * Store to cache file
	 *
	 * @param string $value
	 * @throws Exception
	 *
	 */
	protected function cacheSet($value) {
		$filename = $this->getCacheFilename();
		$file = fopen($filename, "w");
		if (!$file) {
			throw new Exception("Failed to open stream " . $filename);
		}
		fwrite($file, $value);
		fclose($file);
	}

	/**
	 * Cache file name
	 *
	 * @return string
	 */
	protected function getCacheFilename() {
		return __DIR__ . "/" . self::CACHE_PATH . md5($this->feedUrl) . ".json";
	}

	public static function deleteCacheFile($feedUrl) {
		$path = __DIR__ . "/" . self::CACHE_PATH . md5($feedUrl) . ".json";
		if (file_exists($path)) {
			return unlink($path);
		} else {
			return false;
		}

	}
}
