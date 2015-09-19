<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Bernhard Posselt 2014-2015
 * @copyright Olivier Paroz 2014-2015
 */

namespace OCA\GalleryPlus\Controller;

use Exception;

use OCP\IURLGenerator;
use OCP\ISession;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\RedirectResponse;

use OCA\GalleryPlus\Environment\NotFoundEnvException;
use OCA\GalleryPlus\Service\NotFoundServiceException;
use OCA\GalleryPlus\Service\ForbiddenServiceException;

/**
 * Our classes extend both Controller and ApiController, so we need to use
 * traits to add some common methods
 *
 * @package OCA\GalleryPlus\Controller
 */
trait HttpError {

	/**
	 * @param \Exception $exception
	 *
	 * @return JSONResponse
	 */
	public function jsonError(Exception $exception) {
		$message = $exception->getMessage();
		$code = $this->getHttpStatusCode($exception);

		return new JSONResponse(
			[
				'message' => $message . ' (' . $code . ')',
				'success' => false
			],
			$code
		);
	}

	/**
	 * @param ISession $session
	 * @param IURLGenerator $urlGenerator
	 * @param string $appName
	 * @param \Exception $exception
	 *
	 * @return RedirectResponse
	 */
	public function htmlError($session, $urlGenerator, $appName, Exception $exception) {
		$message = $exception->getMessage();
		$code = $this->getHttpStatusCode($exception);
		$session->set('galleryErrorMessage', $message);
		$url = $urlGenerator->linkToRoute(
			$appName . '.page.error_page', ['code' => $code]
		);

		return new RedirectResponse($url);
	}

	/**
	 * Returns an error array
	 *
	 * @param $exception
	 *
	 * @return array<null|int|string>
	 */
	public function getHttpStatusCode($exception) {
		$code = Http::STATUS_INTERNAL_SERVER_ERROR;
		if ($exception instanceof NotFoundServiceException
			|| $exception instanceof NotFoundEnvException
		) {
			$code = Http::STATUS_NOT_FOUND;
		}
		if ($exception instanceof ForbiddenServiceException) {
			$code = Http::STATUS_FORBIDDEN;
		}

		return $code;
	}
}