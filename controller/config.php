<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2015
 */

namespace OCA\GalleryPlus\Controller;

use OCP\ILogger;

use OCP\AppFramework\Http;

use OCA\GalleryPlus\Service\ConfigService;

/**
 * Trait Config
 *
 * @package OCA\GalleryPlus\Controller
 */
trait Config {

	/**
	 * @var ConfigService
	 */
	private $configService;
	/**
	 * @var ILogger
	 */
	private $logger;

	/**
	 * @NoAdminRequired
	 *
	 * Returns an app configuration array
	 *
	 * @param bool $extraMediaTypes
	 *
	 * @return array <string,null|array>
	 */
	private function getConfig($extraMediaTypes = false) {
		$features = $this->configService->getFeaturesList();

		//$this->logger->debug("Features: {features}", ['features' => $features]);

		$nativeSvgSupport = $this->isNativeSvgActivated($features);
		$mediaTypes =
			$this->configService->getSupportedMediaTypes($extraMediaTypes, $nativeSvgSupport);

		return ['features' => $features, 'mediatypes' => $mediaTypes];
	}

	/**
	 * Determines if the native SVG feature has been activated
	 *
	 * @param array $features
	 *
	 * @return bool
	 */
	private function isNativeSvgActivated($features) {
		$nativeSvgSupport = false;
		if (!empty($features) && in_array('native_svg', $features)) {
			$nativeSvgSupport = true;
		}

		return $nativeSvgSupport;
	}
}
