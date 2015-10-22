<?php

namespace Borg\Controller\Component;

use Cake\Controller\Component;
use Cake\Mailer\Email;

/**
 * Email component
 * Handle debug and normal mail operations
 *
 * Config example:
 * -----------------------------------------------
    'EmailTransport' => [
        'default' => [
            'className' => 'Mail',
            'additionalParameters' => '-fcontact@domain.tld',
        ],
    ],

    'Email' => [
        'default' => [
            'transport' => 'default',
            'emailFormat' => 'html',
            'from' => ['contact@domain.tld' => 'domain.tld'],
            'sender' => ['contact@domain.tld' => 'domain.tld'],
            'to' => ['name1@company.tld', 'name2@company.tld'],
            'bcc' => 'monitor@borg.tld',
            'headers' => ['X-Mailer' => 'domain.tld'],
            'charset' => 'utf-8',
            'headerCharset' => 'utf-8',
        ],
        'debug' => [
            'transport' => 'default',
            'emailFormat' => 'html',
            'from' => ['contact@domain.tld' => 'domain.tld'],
            'to' => 'monitor@borg.tld',
            'log' => true,
            'charset' => 'utf-8',
            'headerCharset' => 'utf-8',
        ],
    ],
 * -----------------------------------------------
 *
 * @author Borg
 * @version 0.1
 */
class EmailComponent extends Component
{
    /**
     * Send debug emails
     *
     * @param string $subject
     * @param string $body
     * @param bool $request
     * @param bool $server
     */
    public function debug($subject = null, $body = null, $request = true, $server = true) {
        // initialize class
        $email = new Email('debug');

        // get controller and method
        $location = [];
        foreach(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $one) {
            // ignore current function
            if($one['function'] == __FUNCTION__)
                continue;

            // check if private or protected
            $check = new \ReflectionMethod($one['class'], $one['function']);
            if($check->isPrivate() || $check->isProtected()) {
                $location[3] = $one['function'];
                continue;
            }

            // set class and function
            $location[1] = str_replace(['Controller', 'App\\\\'], null, $one['class']);
            $location[2] = $one['function'];
            break;
        }

        // sort before using
        ksort($location);

        // brand
        $from = $email->from();
        $brand = reset($from);

        // append brand to subject
        $subject = $brand . ' report: [' . implode('->', $location) . '] ' . $subject;

        // show request
        if($request) {
            $body .= "\n\nPOST\n".var_export($_POST, true);
            $body .= "\n\nGET\n".var_export($_GET, true);
            if (isset($_COOKIE))  $body .= "\n\nCOOKIE\n".var_export($_COOKIE, true);
            if (isset($_SESSION)) $body .= "\n\nSESSION\n".var_export($_SESSION, true);
        }

        // show server
        if($server)
            $body .= "\n\nSERVER\n".var_export($_SERVER, true);

        // send email
        $email->subject($subject)->send(nl2br($body));
    }

    /**
     * Send emails
     *
     * @param string $template
     * @param array $data
     * @param string $config
     * @throws SocketException if mail could not be sent
     */
    public function send($data = [], $template = 'default', $config = 'default') {
        // initialize class
        $email = new Email($config);

        // to?
        if(isset($data['to']))
            $email->to($data['to']);

        // brand
        $from = $email->from();
        $brand = reset($from);

        // subject?
        $subject = isset($data['subject']) ? $data['subject'] : $email->subject();
        $email->subject(trim($config == 'debug' ? $brand . ' report: ' . $subject : $subject . ' - ' . $brand));

        // template?
        $email->template($template);

        // data & send
        $email->viewVars([
            'subject' => $subject,
            'form' => isset($data['form']) ? $data['form'] : [],
            'brand' => $brand,
            'info' => [
                'ip' => $this->request->clientIP(),
                'useragent' => env('HTTP_USER_AGENT'),
                'date' => strftime('%d.%m.%Y %H:%M')
            ]
        ])->send();
    }
}