<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Exceptions extends CI_Exceptions {

    /**
     * 404 Error Handler
     *
     * @uses	CI_Exceptions::show_error()
     *
     * @param	string	$page		Page URI
     * @param 	bool	$log_error	Whether to log the error
     * @return	void
     */
    public function show_404($page = '', $log_error = TRUE) {
        if (isset($_SERVER['HTTP_SYSSERVICE'])) {
            header('HTTP/1.1 404 Not Found');
            header('Content-Type: application/json');
            echo json_encode(array('status' => 404, 'heading' => 'Not Found', 'message' => 'The service / content you requested was not found'));
            exit();
        }

        if (is_cli()) {
            $heading = 'Not Found';
            $message = 'The controller/method pair you requested was not found.';
        } else {
            $heading = '404 Page Not Found';
            $message = 'The page you requested was not found.';
        }

        // By default we log this, but allow a dev to skip it
        if ($log_error) {
            log_message('error', $heading . ': ' . $page);
        }

        echo $this->show_error($heading, $message, 'error_404', 404);
        exit(4); // EXIT_UNKNOWN_FILE
    }

    // --------------------------------------------------------------------

    /**
     * General Error Page
     *
     * Takes an error message as input (either as a string or an array)
     * and displays it using the specified template.
     *
     * @param	string		$heading	Page heading
     * @param	string|string[]	$message	Error message
     * @param	string		$template	Template name
     * @param 	int		$status_code	(default: 500)
     *
     * @return	string	Error page output
     */
    public function show_error($heading, $message, $template = 'error_general', $status_code = 500) {
        if (isset($_SERVER['HTTP_SYSSERVICE'])) {

            if ($status_code === 500) {
                header('HTTP/1.1 500 Internal Server Error');
            } else {
                header("HTTP/1.1 $status_code $heading");
            }

            header('Content-Type: application/json');
            echo json_encode(array('status' => $status_code, 'heading' => $heading, 'message' => $message));
            exit();
        }

        $templates_path = config_item('error_views_path');
        if (empty($templates_path)) {
            $templates_path = VIEWPATH . 'errors' . DIRECTORY_SEPARATOR;
        }

        if (is_cli()) {
            $message = "\t" . (is_array($message) ? implode("\n\t", $message) : $message);
            $template = 'cli' . DIRECTORY_SEPARATOR . $template;
        } else {
            set_status_header($status_code);
            $message = '<p>' . (is_array($message) ? implode('</p><p>', $message) : $message) . '</p>';
            $template = 'html' . DIRECTORY_SEPARATOR . $template;
        }

        if (ob_get_level() > $this->ob_level + 1) {
            ob_end_flush();
        }
        ob_start();
        include($templates_path . $template . '.php');
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }

    // --------------------------------------------------------------------

    public function show_exception($exception) {
        $templates_path = config_item('error_views_path');
        if (empty($templates_path)) {
            $templates_path = VIEWPATH . 'errors' . DIRECTORY_SEPARATOR;
        }

        $message = $exception->getMessage();
        if (empty($message)) {
            $message = '(null)';
        }

        if (is_cli()) {
            $templates_path .= 'cli' . DIRECTORY_SEPARATOR;
        } else {
            $templates_path .= 'html' . DIRECTORY_SEPARATOR;
        }

        if (ob_get_level() > $this->ob_level + 1) {
            ob_end_flush();
        }

        ob_start();
        include($templates_path . 'error_exception.php');
        $buffer = ob_get_contents();
        ob_end_clean();

        if (isset($_SERVER['HTTP_SYSSERVICE'])) {
            header('HTTP/1.1 500 Internal server Error');
            header('Content-Type: application/json');

            echo json_encode(array('status' => 500, 'heading' => 'Error Exception', 'message' => $message));
            exit();
        }
        echo $buffer;
    }

    // --------------------------------------------------------------------

    /**
     * Native PHP error handler
     *
     * @param	int	$severity	Error level
     * @param	string	$message	Error message
     * @param	string	$filepath	File path
     * @param	int	$line		Line number
     * @return	void
     */
    public function show_php_error($severity, $message, $filepath, $line) {
        $templates_path = config_item('error_views_path');
        if (empty($templates_path)) {
            $templates_path = VIEWPATH . 'errors' . DIRECTORY_SEPARATOR;
        }

        $severity = isset($this->levels[$severity]) ? $this->levels[$severity] : $severity;

        // For safety reasons we don't show the full file path in non-CLI requests
        if (!is_cli()) {
            $filepath = str_replace('\\', '/', $filepath);
            if (FALSE !== strpos($filepath, '/')) {
                $x = explode('/', $filepath);
                $filepath = $x[count($x) - 2] . '/' . end($x);
            }

            $template = 'html' . DIRECTORY_SEPARATOR . 'error_php';
        } else {
            $template = 'cli' . DIRECTORY_SEPARATOR . 'error_php';
        }

        if (ob_get_level() > $this->ob_level + 1) {
            ob_end_flush();
        }
        ob_start();
        include($templates_path . $template . '.php');
        $buffer = ob_get_contents();
        ob_end_clean();

        if (isset($_SERVER['HTTP_SYSSERVICE'])) {
            header('HTTP/1.1 500 Internal server Error');
            header('Content-Type: application/json');
            echo json_encode(array('status' => 500, 'heading' => $severity, 'message' => $message));
            exit();
        }
        echo $buffer;
    }

}
?>

