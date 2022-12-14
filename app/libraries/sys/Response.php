<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Response {

    private $_ci;
    protected $data;

    function __construct() {
        $this->_ci = & get_instance();
    }

    public function script($script) {
        $this->data['scripts'][] = $script;
    }

    public function alert($title, $body, $ok_label = 'OK') {
        $this->dialog(array(
            'title' => $title,
            'body' => $body,
            'buttons' => array('ok' => $ok_label)
        ));
    }

    public function reload_page() {
        $this->script("location.reload(true);");
    }

    public function confirm($title, $body, $ok_label = 'OK', $close_label = 'Close') {
        if (!$this->_ci->input->get_post('_dialog_confirmed')) {
            $this->_ci->load->helper('form');

            $dialog_id = 'dialog-' . mt_rand(1000000, 9999999);

            $url = $this->_ci->uri->uri_string();
            if (!empty($_SERVER['QUERY_STRING'])) {
                $url .= '?' . $_SERVER['QUERY_STRING'];
            }

            $content = implode('', array(
                form_open($url, 'rel="async"'),
                form_hidden('_dialog_confirmed', 1),
                form_hidden('_dialog_id', $dialog_id),
                '<div class="modal-body">', $body, '</div>'
            ));

            $this->dialog(array(
                'id' => $dialog_id,
                'title' => $title,
                'content' => $content,
                'buttons' => array(
                    'close' => $close_label,
                    'submit' => $ok_label,
                ),
                'footer' => form_close()
            ));
            return FALSE;
        }

        $dialog_id = $this->_ci->input->get_post('_dialog_id');
        $this->script("$('#{$dialog_id}').modal('hide');");
        return TRUE;
    }

    public function confirm_alert($title, $body, $ok_label = 'OK', $close_label = 'Close') {
        if (!$this->_ci->input->get_post('_dialog_confirmed')) {
            $this->_ci->load->helper('form');

            $dialog_id = 'dialog-' . mt_rand(1000000, 9999999);

            $url = $this->_ci->uri->uri_string();
            if (!empty($_SERVER['QUERY_STRING'])) {
                $url .= '?' . $_SERVER['QUERY_STRING'];
            }

            $content = implode('', array(
                form_open($url, 'rel="async"'),
                form_hidden('_dialog_confirmed', 1),
                form_hidden('_dialog_id', $dialog_id),
                '<div class="modal-body">', $body, '</div>'
            ));

            switch ($title) {
                case 'danger' : $icon = '<i class="fa fa-times-circle"></i>';
                    break;
                case 'success' : $icon = '<i class="fa fa-check-circle"></i>';
                    break;
                case 'warning' : $icon = '<i class="fa fa-warning"></i>';
                    break;
                case 'info' : $icon = '<i class="fa fa-info-circle"></i>';
                    break;
            }

            $this->dialog_alert(array(
                'id' => $dialog_id,
                'title' => array('icon' => $icon, 'alert-class' => $title),
                'content' => $content,
                'buttons' => array(
                    'close' => $close_label,
                    'submit' => $ok_label,
                ),
                'footer' => form_close()
            ));

            return FALSE;
        }

        $dialog_id = $this->_ci->input->get_post('_dialog_id');
        $this->script("$('#{$dialog_id}').modal('hide');");
        return TRUE;
    }

    public function dialog($data) {
        $this->_ci->load->library('sys/dialog');

        $dialog_id = (empty($data['id'])) ?
                'dialog-' . mt_rand(1000000, 9999999) :
                $data['id'];
        $this->_ci->dialog->set_id($dialog_id);

        if (!empty($data['title'])) {
            $this->_ci->dialog->set_title($data['title']);
        }

        if (!empty($data['body'])) {
            $this->_ci->dialog->set_body($data['body']);
        }

        if (!empty($data['content'])) {
            $this->_ci->dialog->set_content($data['content']);
        }

        if (!empty($data['buttons'])) {
            foreach ($data['buttons'] as $type => $label) {
                $this->_ci->dialog->add_button($type, $label);
            }
        }

        if (!empty($data['footer'])) {
            $this->_ci->dialog->set_footer($data['footer']);
        }

        if (!empty($data['size'])) {
            $this->_ci->dialog->set_size($data['size']);
        }

        $html = $this->_ci->dialog->html();
        $json_html = json_encode($html);

        $code = <<< JS
$('body').append({$json_html});
$('#{$dialog_id}')
    .find('form[rel="async"]').data('caller', $(this)).end()
    .modal({
            backdrop: 'static',
            keyboard: false
        }).on('hidden.bs.modal', function(e) {
        if ($('.modal:visible').length) {
            $('.modal-backdrop').first().css('z-index', parseInt($('.modal:visible').last().css('z-index')) - 10);
            $('body').addClass('modal-open');
        }
        setTimeout(function() {
            $(e.target).remove();
        }, 1);
    });
JS;
        $this->script($code);
    }

    public function dialog_full($data) {
        $this->_ci->load->library('sys/dialog');

        $dialog_id = (empty($data['id'])) ?
                'dialog-' . mt_rand(1000000, 9999999) :
                $data['id'];
        $this->_ci->dialog->set_id($dialog_id);

        if (!empty($data['title'])) {
            $this->_ci->dialog->set_title($data['title']);
        }

        if (!empty($data['body'])) {
            $this->_ci->dialog->set_body($data['body']);
        }

        if (!empty($data['content'])) {
            $this->_ci->dialog->set_content($data['content']);
        }

        if (!empty($data['buttons'])) {
            foreach ($data['buttons'] as $type => $label) {
                $this->_ci->dialog->add_button($type, $label);
            }
        }

        if (!empty($data['footer'])) {
            $this->_ci->dialog->set_footer($data['footer']);
        }

        if (!empty($data['size'])) {
            $this->_ci->dialog->set_size($data['size']);
        }

        $html = $this->_ci->dialog->html_full();
        $json_html = json_encode($html);

        $code = <<< JS
$('body').append({$json_html});
$('#{$dialog_id}')
    .find('form[rel="async"]').data('caller', $(this)).end()
    .modal({
            backdrop: 'static',
            keyboard: false
        }).on('hidden.bs.modal', function(e) {
        if ($('.modal:visible').length) {
            $('.modal-backdrop').first().css('z-index', parseInt($('.modal:visible').last().css('z-index')) - 10);
            $('body').addClass('modal-open');
        }
        setTimeout(function() {
            $(e.target).remove();
        }, 1);
    });
JS;
        $this->script($code);
    }

    public function dialog_alert($data) {
        $this->_ci->load->library('sys/dialog');

        $dialog_id = (empty($data['id'])) ?
                'dialog-' . mt_rand(1000000, 9999999) :
                $data['id'];
        $this->_ci->dialog->set_id($dialog_id);

        if (!empty($data['title'])) {
            $this->_ci->dialog->set_title($data['title']);
        }

        if (!empty($data['body'])) {
            $this->_ci->dialog->set_body($data['body']);
        }

        if (!empty($data['content'])) {
            $this->_ci->dialog->set_content($data['content']);
        }

        if (!empty($data['buttons'])) {
            foreach ($data['buttons'] as $type => $label) {
                $this->_ci->dialog->add_button($type, $label, $data['title']['alert-class']);
            }
        }

        if (!empty($data['footer'])) {
            $this->_ci->dialog->set_footer($data['footer']);
        }

        if (!empty($data['size'])) {
            $this->_ci->dialog->set_size($data['size']);
        }

        $html = $this->_ci->dialog->html_alert();
        $json_html = json_encode($html);

        $code = <<< JS
$('body').append({$json_html});
$('#{$dialog_id}')
    .find('form[rel="async"]').data('caller', $(this)).end()
    .modal().on('hidden.bs.modal', function(e) {
        if ($('.modal:visible').length) {
            $('.modal-backdrop').first().css('z-index', parseInt($('.modal:visible').last().css('z-index')) - 10);
            $('body').addClass('modal-open');
        }
        setTimeout(function() {
            $(e.target).remove();
        }, 1);
    });
JS;
        $this->script($code);
    }

    public function send($return = FALSE) {
        if (!empty($this->data)) {
            if ($this->_ci->input->is_ajax_request()) {
                $json_data = json_encode($this->data);
                if ($return) {
                    return $json_data;
                } else {
                    echo $json_data;
                    exit;
                }
            }
        }
    }

}

/* End of file Response.php */
/* Location: ./application/libraries/Response.php */