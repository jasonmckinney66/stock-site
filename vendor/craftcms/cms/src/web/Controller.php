<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\web;

use Craft;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use craft\web\assets\iframeresizer\ContentWindowAsset;
use yii\base\Action;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\JsonResponseFormatter;
use yii\web\Response as YiiResponse;
use yii\web\UnauthorizedHttpException;

/**
 * Controller is a base class that all controllers in Craft extend.
 * It extends Yii’s [[\yii\web\Controller]], overwriting specific methods as required.
 *
 * @property Request $request
 * @property Response $response
 * @property View $view The view object that can be used to render views or view files
 * @method View getView() Returns the view object that can be used to render views or view files
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.0.0
 */
abstract class Controller extends \yii\web\Controller
{
    const ALLOW_ANONYMOUS_NEVER = 0;
    const ALLOW_ANONYMOUS_LIVE = 1;
    const ALLOW_ANONYMOUS_OFFLINE = 2;

    /**
     * @var int|bool|int[]|string[] Whether this controller’s actions can be accessed anonymously.
     *
     * This can be set to any of the following:
     *
     * - `false` or `self::ALLOW_ANONYMOUS_NEVER` (default) – indicates that all controller actions should never be
     *   accessed anonymously
     * - `true` or `self::ALLOW_ANONYMOUS_LIVE` – indicates that all controller actions can be accessed anonymously when
     *    the system is live
     * - `self::ALLOW_ANONYMOUS_OFFLINE` – indicates that all controller actions can be accessed anonymously when the
     *    system is offline
     * - `self::ALLOW_ANONYMOUS_LIVE | self::ALLOW_ANONYMOUS_OFFLINE` – indicates that all controller actions can be
     *    accessed anonymously when the system is live or offline
     * - An array of action IDs (e.g. `['save-guest-entry', 'edit-guest-entry']`) – indicates that the listed action IDs
     *   can be accessed anonymously when the system is live
     * - An array of action ID/bitwise pairs (e.g. `['save-guest-entry' => self::ALLOW_ANONYMOUS_OFFLINE]` – indicates
     *   that the listed action IDs can be accessed anonymously per the bitwise int assigned to it.
     */
    protected $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    /**
     * @inheritdoc
     * @throws InvalidConfigException if [[$allowAnonymous]] is set to an invalid value
     */
    public function init()
    {
        // Normalize $allowAnonymous
        if (is_bool($this->allowAnonymous)) {
            $this->allowAnonymous = (int)$this->allowAnonymous;
        } elseif (is_array($this->allowAnonymous)) {
            $normalized = [];
            foreach ($this->allowAnonymous as $k => $v) {
                if (
                    (is_int($k) && !is_string($v)) ||
                    (is_string($k) && !is_int($v))
                ) {
                    throw new InvalidArgumentException("Invalid \$allowAnonymous value for key \"{$k}\"");
                }
                if (is_int($k)) {
                    $normalized[$v] = self::ALLOW_ANONYMOUS_LIVE;
                } else {
                    $normalized[$k] = $v;
                }
            }
            $this->allowAnonymous = $normalized;
        } elseif (!is_int($this->allowAnonymous)) {
            throw new InvalidConfigException('Invalid $allowAnonymous value');
        }

        parent::init();
    }

    /**
     * This method is invoked right before an action is executed.
     *
     * The method will trigger the [[EVENT_BEFORE_ACTION]] event. The return value of the method
     * will determine whether the action should continue to run.
     *
     * In case the action should not run, the request should be handled inside of the `beforeAction` code
     * by either providing the necessary output or redirecting the request. Otherwise the response will be empty.
     *
     * If you override this method, your code should look like the following:
     *
     * ```php
     * public function beforeAction($action)
     * {
     *     // your custom code here, if you want the code to run before action filters,
     *     // which are triggered on the [[EVENT_BEFORE_ACTION]] event, e.g. PageCache or AccessControl
     *
     *     if (!parent::beforeAction($action)) {
     *         return false;
     *     }
     *
     *     // other custom code here
     *
     *     return true; // or false to not run the action
     * }
     * ```
     *
     * @param Action $action the action to be executed.
     * @return bool whether the action should continue to run.
     * @throws BadRequestHttpException if the request is missing a valid CSRF token
     * @throws ForbiddenHttpException if the user is not logged in or lacks the necessary permissions
     * @throws ServiceUnavailableHttpException if the system is offline and the user isn't allowed to access it
     * @throws UnauthorizedHttpException
     */
    public function beforeAction($action)
    {
        // Don't enable CSRF validation for Live Preview requests
        if ($this->request->getIsLivePreview()) {
            $this->enableCsrfValidation = false;
        }

        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->_enforceAllowAnonymous($action);

        return true;
    }

    private function _enforceAllowAnonymous(Action $action)
    {
        $isCpRequest = $this->request->getIsCpRequest();

        // If a valid site token was passed, grant them access
        if (!$isCpRequest && $this->request->hasValidSiteToken()) {
            return;
        }

        $isLive = Craft::$app->getIsLive();
        $test = $isLive ? self::ALLOW_ANONYMOUS_LIVE : self::ALLOW_ANONYMOUS_OFFLINE;

        if (is_int($this->allowAnonymous)) {
            $allowAnonymous = $this->allowAnonymous;
        } else {
            $allowAnonymous = $this->allowAnonymous[$action->id] ?? self::ALLOW_ANONYMOUS_NEVER;
        }

        if (!($test & $allowAnonymous)) {
            // If this is a CP request, make sure they have access to the CP
            if ($isCpRequest) {
                $this->requireLogin();
                $this->requirePermission('accessCp');
            } elseif (Craft::$app->getUser()->getIsGuest()) {
                if ($isLive) {
                    throw new ForbiddenHttpException();
                } else {
                    $retryDuration = Craft::$app->getProjectConfig()->get('system.retryDuration');
                    if ($retryDuration) {
                        $this->response->getHeaders()->setDefault('Retry-After', $retryDuration);
                    }
                    throw new ServiceUnavailableHttpException();
                }
            }

            // If the system is offline, make sure they have permission to access the CP/site
            if (!$isLive) {
                $permission = $this->request->getIsCpRequest() ? 'accessCpWhenSystemIsOff' : 'accessSiteWhenSystemIsOff';
                if (!Craft::$app->getUser()->checkPermission($permission)) {
                    $error = $this->request->getIsCpRequest()
                        ? Craft::t('app', 'Your account doesn’t have permission to access the control panel when the system is offline.')
                        : Craft::t('app', 'Your account doesn’t have permission to access the site when the system is offline.');
                    throw new ServiceUnavailableHttpException($error);
                }
            }
        }
    }

    /**
     * Renders a template.
     *
     * @param string $template The name of the template to load
     * @param array $variables The variables that should be available to the template
     * @param string $templateMode The template mode to use
     * @return YiiResponse
     * @throws InvalidArgumentException if the view file does not exist.
     */
    public function renderTemplate(string $template, array $variables = [], string $templateMode = null): YiiResponse
    {
        $view = $this->getView();
        $generalConfig = Craft::$app->getConfig()->getGeneral();

        // If this is a preview request and `useIframeResizer` is enabled, register the iframe resizer script
        if (
            $this->request->getQueryParam('x-craft-live-preview') !== null &&
            $generalConfig->useIframeResizer
        ) {
            $view->registerAssetBundle(ContentWindowAsset::class);
        }

        // Prevent a response formatter from overriding the content-type header
        $this->response->format = YiiResponse::FORMAT_RAW;

        // Render and return the template
        $this->response->data = $view->renderPageTemplate($template, $variables, $templateMode);

        $headers = $this->response->getHeaders();

        if ($generalConfig->sendContentLengthHeader) {
            $headers->setDefault('content-length', strlen($this->response->data));
        }

        // Set the MIME type for the request based on the matched template's file extension (unless the
        // Content-Type header was already set, perhaps by the template via the {% header %} tag)
        if (!$headers->has('content-type')) {
            $templateFile = StringHelper::removeRight(strtolower($view->resolveTemplate($template)), '.twig');
            $mimeType = FileHelper::getMimeTypeByExtension($templateFile) ?? 'text/html';
            $headers->set('content-type', $mimeType . '; charset=' . $this->response->charset);
        }

        return $this->response;
    }

    /**
     * Redirects the user to the login template if they're not logged in.
     */
    public function requireLogin()
    {
        $userSession = Craft::$app->getUser();

        if ($userSession->getIsGuest()) {
            $userSession->loginRequired();
            Craft::$app->end();
        }
    }

    /**
     * Redirects the user to the account template if they are logged in.
     *
     * @since 3.4.0
     */
    public function requireGuest()
    {
        $userSession = Craft::$app->getUser();

        if (!$userSession->getIsGuest()) {
            $userSession->guestRequired();
            Craft::$app->end();
        }
    }

    /**
     * Throws a 403 error if the current user is not an admin.
     *
     * @param bool $requireAdminChanges Whether the <config3:allowAdminChanges>
     * config setting must also be enabled.
     * @throws ForbiddenHttpException if the current user is not an admin
     */
    public function requireAdmin(bool $requireAdminChanges = true)
    {
        // First make sure someone's actually logged in
        $this->requireLogin();

        // Make sure they're an admin
        if (!Craft::$app->getUser()->getIsAdmin()) {
            throw new ForbiddenHttpException('User is not permitted to perform this action.');
        }

        // Make sure admin changes are allowed
        if ($requireAdminChanges && !Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            throw new ForbiddenHttpException('Administrative changes are disallowed in this environment.');
        }
    }

    /**
     * Checks whether the current user has a given permission, and ends the request with a 403 error if they don’t.
     *
     * @param string $permissionName The name of the permission.
     * @throws ForbiddenHttpException if the current user doesn’t have the required permission
     */
    public function requirePermission(string $permissionName)
    {
        if (!Craft::$app->getUser()->checkPermission($permissionName)) {
            throw new ForbiddenHttpException('User is not permitted to perform this action');
        }
    }

    /**
     * Checks whether the current user can perform a given action, and ends the request with a 403 error if they don’t.
     *
     * @param string $action The name of the action to check.
     * @throws ForbiddenHttpException if the current user is not authorized
     */
    public function requireAuthorization(string $action)
    {
        if (!Craft::$app->getSession()->checkAuthorization($action)) {
            throw new ForbiddenHttpException('User is not authorized to perform this action');
        }
    }

    /**
     * Requires that the user has an elevated session.
     *
     * @throws ForbiddenHttpException if the current user does not have an elevated session
     */
    public function requireElevatedSession()
    {
        if (!Craft::$app->getUser()->getHasElevatedSession()) {
            throw new ForbiddenHttpException(Craft::t('app', 'This action may only be performed with an elevated session.'));
        }
    }

    /**
     * Throws a 400 error if this isn’t a POST request
     *
     * @throws BadRequestHttpException if the request is not a post request
     */
    public function requirePostRequest()
    {
        if (!$this->request->getIsPost()) {
            throw new BadRequestHttpException('Post request required');
        }
    }

    /**
     * Throws a 400 error if the request doesn't accept JSON.
     *
     * @throws BadRequestHttpException if the request doesn't accept JSON
     */
    public function requireAcceptsJson()
    {
        if (!$this->request->getAcceptsJson() && !$this->request->getIsOptions()) {
            throw new BadRequestHttpException('Request must accept JSON in response');
        }
    }

    /**
     * Throws a 400 error if the current request doesn’t have a valid Craft token.
     *
     * @throws BadRequestHttpException if the request does not have a valid Craft token
     * @see Request::getToken()
     */
    public function requireToken()
    {
        if (!$this->request->getHadToken()) {
            throw new BadRequestHttpException('Valid token required');
        }
    }

    /**
     * Throws a 400 error if the current request isn’t a control panel request.
     *
     * @throws BadRequestHttpException if this is not a control panel request
     * @since 3.1.0
     */
    public function requireCpRequest()
    {
        if (!$this->request->getIsCpRequest()) {
            throw new BadRequestHttpException('Request must be a control panel request');
        }
    }

    /**
     * Throws a 400 error if the current request isn’t a site request.
     *
     * @throws BadRequestHttpException if the request is not a site request
     * @since 3.1.0
     */
    public function requireSiteRequest()
    {
        if (!$this->request->getIsSiteRequest()) {
            throw new BadRequestHttpException('Request must be a site request');
        }
    }

    /**
     * Sets a success flash message on the user session.
     *
     * If a hashed `successMessage` param was sent with the request, that will be used instead of the provided default.
     *
     * @param string|null $default
     * @since 3.5.0
     */
    public function setSuccessFlash(string $default = null)
    {
        $message = $this->request->getValidatedBodyParam('successMessage') ?? $default;
        if ($message !== null) {
            Craft::$app->getSession()->setNotice($message);
        }
    }

    /**
     * Sets an error flash message on the user session.
     *
     * If a hashed `failMessage` param was sent with the request, that will be used instead of the provided default.
     *
     * @param string|null $default
     * @since 3.5.0
     */
    public function setFailFlash(string $default = null)
    {
        $message = $this->request->getValidatedBodyParam('failMessage') ?? $default;
        if ($message !== null) {
            Craft::$app->getSession()->setError($message);
        }
    }

    /**
     * Redirects to the URI specified in the POST.
     *
     * @param mixed $object Object containing properties that should be parsed for in the URL.
     * @param string|null $default The default URL to redirect them to, if no 'redirect' parameter exists. If this is left
     * null, then the current request’s path will be used.
     * @return YiiResponse
     * @throws BadRequestHttpException if the redirect param was tampered with
     */
    public function redirectToPostedUrl($object = null, string $default = null): YiiResponse
    {
        $url = $this->request->getValidatedBodyParam('redirect');

        if ($url === null) {
            if ($default !== null) {
                $url = $default;
            } else {
                $url = $this->request->getPathInfo();
            }
        } elseif ($object) {
            $url = $this->getView()->renderObjectTemplate($url, $object);
        }

        return $this->redirect($url);
    }

    /** @noinspection ArrayTypeOfParameterByDefaultValueInspection */
    /**
     * Sets the response format of the given data as JSONP.
     *
     * @param mixed $data The data that should be formatted.
     * @return YiiResponse A response that is configured to send `$data` formatted as JSON.
     * @see YiiResponse::$format
     * @see YiiResponse::FORMAT_JSONP
     * @see JsonResponseFormatter
     */
    public function asJsonP($data): YiiResponse
    {
        $this->response->data = $data;
        $this->response->format = YiiResponse::FORMAT_JSONP;
        return $this->response;
    }

    /** @noinspection ArrayTypeOfParameterByDefaultValueInspection */
    /**
     * Sets the response format of the given data as RAW.
     *
     * @param mixed $data The data that should *not* be formatted.
     * @return YiiResponse A response that is configured to send `$data` without formatting.
     * @see YiiResponse::$format
     * @see YiiResponse::FORMAT_RAW
     */
    public function asRaw($data): YiiResponse
    {
        $this->response->data = $data;
        $this->response->format = YiiResponse::FORMAT_RAW;
        return $this->response;
    }

    /**
     * Responds to the request with a JSON error message.
     *
     * @param string $error The error message.
     * @return YiiResponse
     */
    public function asErrorJson(string $error): YiiResponse
    {
        return $this->asJson(['error' => $error]);
    }

    /**
     * @inheritdoc
     * @return YiiResponse
     */
    public function redirect($url, $statusCode = 302): YiiResponse
    {
        if ($url !== null) {
            return $this->response->redirect($url, $statusCode);
        }

        return $this->goHome();
    }
}
