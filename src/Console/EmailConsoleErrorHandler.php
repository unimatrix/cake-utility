<?php

namespace Unimatrix\Utility\Console;

use Unimatrix\Utility\Lib\Misc;
use Unimatrix\Utility\Controller\Component\EmailComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Console\ConsoleErrorHandler;
use Cake\Core\Configure;
use Exception;

/**
 * Email Console Error Handler (for cli)
 * Send a debug email for each fatal error or exception that is not catched.
 * Note: Only works on live environments (debug = false)
 *
 * Usage exmaple (in bootstrap)
 * ----------------------------------------------------------------
 * search for -> (new ConsoleErrorHandler(Configure::read('Error')))->register();
 * replace with -> (new EmailConsoleErrorHandler(Configure::read('Error')))->register();
 *
 * Don't forget about
 * use Unimatrix\Utility\Console\EmailConsoleErrorHandler;
 *
 * @author Flavius
 * @version 0.1
 */
class EmailConsoleErrorHandler extends ConsoleErrorHandler
{
    // debug & email
    private $debug = false;
    private $email = false;

    // skip these errors and exceptions
    protected $_skipErrors = [E_NOTICE, E_WARNING];
    protected $_skipExceptions = [];

    /**
     * Constructor
     *
     * @param array $options The options for error handling.
     */
    public function __construct($options = []) {
        // set debug & email
        $this->debug = Configure::read('debug');
        if(!$this->debug)
            $this->email = new EmailComponent(new ComponentRegistry());

        // run parent
        parent::__construct($options);
    }

    /**
     * Intercept error handling to send a mail before continuing with the default logic
     * @see \Cake\Error\BaseErrorHandler::handleError()
     */
    public function handleError($code, $description, $file = null, $line = null, $context = null) {
        // send a debug mail with the fatal error
        if($this->email && !in_array($code, $this->_skipErrors))
            $this->email->debug('Application error', Misc::dump([
                'code' => $code,
                'description' => $description,
                'file' => $file,
                'line' => $line,
                'context' => $context
            ], $description, true));

        // continue with error handle logic
        parent::handleError($code, $description, $file, $line, $context);
    }

    /**
     * Intercept exception handling to send a mail before continuing with the default logic
     * @see \Cake\Error\BaseErrorHandler::handleException()
     */
    public function handleException(Exception $exception) {
        // send a debug mail with the fatal exception
        if($this->email && !in_array(get_class($exception), $this->_skipExceptions))
            $this->email->debug('Application exception', Misc::dump($exception, $exception->getMessage(), true));

        // continue with exception handle logic
        parent::handleException($exception);
    }
}