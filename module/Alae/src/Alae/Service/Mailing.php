<?php
/**
 * Envío de correos
 *
 * @author Maria Quiroz
 * Fecha de creación: 20/05/2014
 */
namespace Alae\Service;

use Zend\Mail,
    Zend\Mime\Part as MimePart,
    Zend\Mime\Message as MimeMessage,
    Alae\Service\Helper as Helper;

class Mailing
{

    /*
     * Configuración de opciones de correo
     */
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
		'ssl' => 'tls',
	    ),
	));

	return $options;
    }

    /*
     * función para enviar correos
     */
    public function send($emails, $view, $subject)
    {
        //CREA LOS ENCABEZADOS MIME
	$html = new MimePart($view);
	$html->type = "text/html";

	$body = new MimeMessage();
	$body->setParts(array($html));

        //ZEND MAIL
	$message = new \Zend\Mail\Message();
	$message->setBody($body);
	$message->setFrom(Helper::getVarsConfig("mail_admin_email"));
	$message->setSubject($subject);

	foreach ($emails as $email)
	{
	    $message->addTo($email);
	}

	$transport = new \Zend\Mail\Transport\Smtp();
	$transport->setOptions($this->options());
	$transport->send($message);
    }

}
