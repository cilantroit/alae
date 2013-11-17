<?php

namespace Alae\Service;

use Zend\Mail,
    Zend\Mime\Part as MimePart,
    Zend\Mime\Message as MimeMessage,
    Alae\Service\Helper as Helper;

class Mailing
{

    private function options()
    {
        $options = new \Zend\Mail\Transport\SmtpOptions(array(
            'name' => 'localhost',
            'host' => Helper::getVarsConfig("mail_host_smtp"),
            'port' => Helper::getVarsConfig("mail_port"),
            'connection_class' => 'login',
            'connection_config' => array(
                'username' => Helper::getVarsConfig("mail_username"),
                'password' => Helper::getVarsConfig("mail_password"),
            ),
        ));

        return $options;
    }

    public function send($emails, $view)
    {
        $html = new MimePart($view);
        $html->type = "text/html";

        $body = new MimeMessage();
        $body->setParts(array($html));

        $message = new \Zend\Mail\Message();
        $message->setBody($body);
        $message->setFrom(Helper::getVarsConfig("mail_admin_email"));
        $message->setSubject('Administrador de ALAE');

        foreach ($emails as $email)
        {
            $message->addTo($email);
        }

        $transport = new \Zend\Mail\Transport\Smtp();
        $transport->setOptions($this->options());
        $transport->send($message);
    }

}

?>
