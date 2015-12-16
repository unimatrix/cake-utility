<?php

namespace Unimatrix\Utility\Error;

use Unimatrix\Utility\Controller\Component\EmailComponent;
use Unimatrix\Utility\Lib\Misc;
use Cake\Controller\ComponentRegistry;
use Cake\Error\ErrorHandler;
use Exception;

/**
 * Email Error Handler
 * Send a debug email for each fatal error or exception that is not catched.
 *
 * Usage exmaple (in bootstrap)
 * ----------------------------------------------------------------
 * search for -> (new ErrorHandler(Configure::read('Error')))->register();
 * replace with -> (new Unimatrix\Utility\Error\EmailErrorHandler(Configure::read('Error')))->register();
 *
 * @author Flavius
 * @version 0.1
 */
class EmailErrorHandler extends ErrorHandler
{
    // skip these errors and exceptions
    static protected $_skipErrors = [E_NOTICE, E_WARNING];
    static protected $_skipExceptions = [
        'Cake\Network\Exception\NotFoundException',
        'Cake\Routing\Exception\MissingRouteException'
    ];

    /**
     * Intercept error handling to send a mail before continuing with the default logic
     * @see \Cake\Error\BaseErrorHandler::handleError()
     */
    public function handleError($code, $description, $file = null, $line = null, $context = null) {
        // send a debug mail with the fatal error
        if(!in_array($code, self::$_skipErrors)) {
            $mail = new EmailComponent(new ComponentRegistry());
            $mail->debug('Application error', Misc::dump($exception, "Error Code {$code}", true));
        }

        // continue with error handle logic
        parent::handleError($code, $description, $file, $line, $context);
    }

    /**
     * Intercept exception handling to send a mail before continuing with the default logic
     * @see \Cake\Error\BaseErrorHandler::handleException()
     */
    public function handleException(Exception $exception) {
        // send a debug mail with the fatal exception
        if(!in_array(get_class($exception), self::$_skipExceptions)) {
            $mail = new EmailComponent(new ComponentRegistry());
            $mail->debug('Application exception', Misc::dump($exception, "Exception -> {$exception->getMessage()}", true));
        }

        // continue with exception handle logic
        parent::handleException($exception);
    }
}