<?php

namespace Sintattica\Atk\Errors;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Sintattica\Atk\Core\Atk;
use Sintattica\Atk\Core\Config;
use Sintattica\Atk\Core\Tools;
use Sintattica\Atk\Errors\ErrorHandlerBase;
use Sintattica\Atk\Session\SessionManager;
use Sintattica\Atk\Security\SecurityManager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class MonologErrorHandler extends ErrorHandlerBase
{
    protected function _wordwrap($line)
    {
        return $line;
        //return wordwrap($line, 100, "\n", 1);
    }

    /**
     * Handle the error.
     *
     * @param string $errorMessage
     * @param string $debugMessage
     */
    public function handle($errorMessage, $debugMessage)
    {
        $sessionManager = SessionManager::getInstance();
        $sessionData = &SessionManager::getSession();
        $txt_app_title = Tools::atktext('app_title');
        $atk = Atk::getInstance();

        $body = "APP: '$txt_app_title'.\n";
        $body .= "\nError message:\n\n".implode("\n", is_array($errorMessage) ? $errorMessage : array())."\n";
        $body .= "\nDetailed report:\n";
        $body .= "\nPHP Version: ".phpversion()."\n\n";

        $body .= "\nDEBUGMESSAGES\n".str_repeat('-', 70)."\n";

        $lines = [];
        for ($i = 0, $_ = Tools::count($debugMessage); $i < $_; ++$i) {
            $lines[] = $this->_wordwrap(Tools::atk_html_entity_decode(preg_replace('(\[<a.*</a>\])', '', $debugMessage[$i])));
        }
        $body .= implode("\n", $lines);

        if (is_array($_GET)) {
            $body .= "\n\n_GET\n".str_repeat('-', 70)."\n";
            foreach ($_GET as $key => $value) {
                $body .= $this->_wordwrap($key.str_repeat(' ', max(1, 20 - strlen($key))).' = '.var_export($value, 1))."\n";
            }
        }

        if (function_exists('getallheaders')) {
            $request = getallheaders();
            if (Tools::count($request) > 0) {
                $body .= "\n\nREQUEST INFORMATION\n".str_repeat('-', 70)."\n";
                foreach ($request as $key => $value) {
                    $body .= $this->_wordwrap($key.str_repeat(' ', max(1, 30 - strlen($key))).' = '.var_export($value, 1))."\n";
                }
            }
        }

        if (is_array($_POST)) {
            $body .= "\n\n_POST\n".str_repeat('-', 70)."\n";
            foreach ($_POST as $key => $value) {
                $body .= $this->_wordwrap($key.str_repeat(' ', max(1, 20 - strlen($key))).' = '.var_export($value, 1))."\n";
            }
        }

        if (is_array($_COOKIE)) {
            $body .= "\n\n_COOKIE\n".str_repeat('-', 70)."\n";
            foreach ($_COOKIE as $key => $value) {
                $body .= $this->_wordwrap($key.str_repeat(' ', max(1, 20 - strlen($key))).' = '.var_export($value, 1))."\n";
            }
        }

        /*
        $body .= "\n\nATK CONFIGURATION\n".str_repeat('-', 70)."\n";
        foreach ($GLOBALS as $key => $value) {
            if (substr($key, 0, 7) == 'config_') {
                $body .= $this->_wordwrap($key.str_repeat(' ', max(1, 30 - strlen($key))).' = '.var_export($value, 1))."\n";
            }
        }
        */

        /*
        $body .= "\n\nMODULE CONFIGURATION\n".str_repeat('-', 70)."\n";
        foreach ($atk->g_modules as $modname => $modpath) {
            $modexists = file_exists($modpath) ? ' (path exists)' : ' (PATH DOES NOT EXIST!)';
            $body .= $this->_wordwrap($modname.':'.str_repeat(' ', max(1, 20 - strlen($modname))).var_export($modpath, 1).$modexists)."\n";
        }
        */

        $body .= "\n\nCurrent User:\n".str_repeat('-', 70)."\n";
        $user = SecurityManager::atkGetUser();
        if (is_array($user) && Tools::count($user)) {
            foreach ($user as $key => $value) {
                $body .= $this->_wordwrap($key.str_repeat(' ', max(1, 30 - strlen($key))).' = '.var_export($value, 1))."\n";
            }
        } else {
            $body .= "Not known\n";
        }

        /*
        if (is_object($sessionManager)) {
            $body .= "\n\nATK SESSION\n".str_repeat('-', 70);
            if (isset($sessionData['stack'])) {
                $stack = $sessionData['stack'];
                for ($i = 0; $i < Tools::count($stack); ++$i) {
                    $body .= "\nStack level $i:\n";
                    $item = isset($stack[$i]) ? $stack[$i] : null;
                    if (is_array($item)) {
                        foreach ($item as $key => $value) {
                            $body .= $this->_wordwrap($key.str_repeat(' ', max(1, 30 - strlen($key))).' = '.var_export($value, 1))."\n";
                        }
                    }
                }
            }
            if (isset($sessionData['globals'])) {
                $ns_globals = $sessionData['globals'];
                if (Tools::count($ns_globals) > 0) {
                    $body .= "\nNamespace globals:\n";
                    foreach ($ns_globals as $key => $value) {
                        $body .= $this->_wordwrap($key.str_repeat(' ', max(1, 30 - strlen($key))).' = '.var_export($value, 1))."\n";
                    }
                }
            }
        }
        */

        /*
        $body .= "\n\nSERVER INFORMATION\n".str_repeat('-', 70)."\n";
        foreach ($_SERVER as $key => $value) {
            $body .= $this->_wordwrap($key.str_repeat(' ', max(1, 20 - strlen($key))).' = '.var_export($value, 1))."\n";
        }
        */

        preg_match_all("/\\[(.*?)\\]/", $errorMessage[0], $res);
        $error_type = Logger::ERROR;
        if (isset($res[0][1])) {
            $str = strtolower($res[0][1]);
            if (strpos($str, 'exception')) {
                $error_type = Logger::ERROR;
            }
            if (strpos($str, 'warning')) {
                $error_type = Logger::WARNING;
            }
            if (strpos($str, 'error')) {
                $error_type = Logger::ERROR;
            }

        }

        $log = new Logger('webnetix');

        $formatter = new LineFormatter(
            null, // Format of message in log, default [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n
            null, // Datetime format
            true, // allowInlineLineBreaks option, default false
            true  // ignoreEmptyContextAndExtra option, default false
        );


        if (isset($this->params['log_file_path'])) {
            $file_to_log = $this->params['log_file_path'];
            $sh = new RotatingFileHandler($file_to_log, 31, Logger::DEBUG, true, 0664);

            $env = getenv('APP_ENV');
            if (!$env || in_array($env, ['prod']) && file_exists($file_to_log)) {
                chgrp($file_to_log, 'work');
            }
            $sh->setFormatter($formatter);
            $log->pushHandler($sh);

        }

        if (isset($this->params['log_slack_url']) && !empty($this->params['log_slack_url'])) {
            $sh = new SlackWebhookHandler(
                $this->params['log_slack_url'],
                $this->params['log_slack_channel'],
                $this->params['log_slack_user'],
            );

            $sh->setLevel($this->params['log_slack_level']);

            $log->pushHandler($sh);
        }

        $log->log($error_type, $body);

    }
}
