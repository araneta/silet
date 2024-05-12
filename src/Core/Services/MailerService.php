<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Core\Services;

/**
 * Description of MailerService
 *
 * @author aldo
 */
use Pimple\Container;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Mailgun\Mailgun;
use Mailjet\Resources;

use Configs\GlobalConfig;
use Core\Entities\Email;


class MailerService
{
	//use Translator;
    private $app;
    private $config;

    public function __construct(Container $app)
    {
        $this->app = $app;
        /*
			array(
			"host"=>"mail.iain-tulungagung.ac.id",
			"username" => "no-reply@iain-tulungagung.ac.id",
			"password" => "norep",
			"smtpauth" => false,
			"smtpsecure" => "tls",
			"port"=>25
		);
		*/
    }
    public function sendEmail(Email $email){        
        
        $config = GlobalConfig::getMailerConfig();
        
        $ret = TRUE;
        try{			
			$ret = $this->sendEmailWithSMTP($email, $config);			
		}catch(\Exception $ex){
			var_dump($ex);
			$ret = FALSE;			
		}
		if(!$ret){
			$ret = $this->sendEMailWithPHP($email, $config);			
		}
		if(!$ret){
			throw new ServiceException('Failed to send email');
		}
		return $ret;
    }
    public function sendEMailWithPHP(Email $email, $config = NULL){
		
		$headers = "From: " . strip_tags($email->fromEmail) . "\r\n";
		$headers .= "Reply-To: ". strip_tags($email->fromEmail) . "\r\n";
		//$headers .= "CC: susan@example.com\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

		return mail($email->toEmail, $email->subject, $email->htmlMessage, $headers);
	}
    public function sendEmailWithMailJet(Email $email, $config = NULL){
        $apikey = '5b29b8a7224b172168d45da9684527b9';
        $apisecret = 'bffaa40a1f86050b5016d5939aacbf0f';
        
        $mj = new \Mailjet\Client($apikey, $apisecret, true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $email->fromEmail,
                        'Name' => $email->fromName
                    ],
                    'To' => [
                        [
                            'Email' => $email->toEmail,
                            'Name' => $email->toName
                        ]
                    ],
                    'Subject' => $email->subject,
                    'TextPart' => $email->plainTextMessage,
                    'HTMLPart' => $email->htmlMessage
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        return $response->success();
        //&& var_dump($response->getData());
        //return true;
            
    }
    public function sendEmailWithSMTP(Email $email, $config = NULL){
	//var_dump($config);exit(0);
		if($config==NULL){
			$config = GlobalConfig::getMailerConfig();
		}
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //Server settings
            //$mail->SMTPDebug = 4;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = $config['host'];  // Specify main and backup SMTP servers
            $mail->SMTPAuth = $config['smtpauth'];                               // Enable SMTP authentication
            $mail->Username = $config['username'];                 // SMTP username
            $mail->Password = $config['password'];                           // SMTP password
            $mail->SMTPSecure = $config['smtpsecure'];                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = $config['port'];                                    // TCP port to connect to
			$mail->SMTPOptions = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				)
			);
            //Recipients
            $mail->setFrom($email->fromEmail, $email->fromName);
            $mail->addAddress($email->toEmail, $email->toName);     // Add a recipient
            //$mail->addReplyTo('aldo@vega10.com', 'Aldo');
            //$mail->addCC('cc@example.com');
            //$mail->addBCC('bcc@example.com');

            //Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $email->subject;
            $mail->Body    = $email->htmlMessage;
            $mail->AltBody = $email->plainTextMessage;


            return $mail->send();
        } catch (Exception $e) {
            throw new ServiceException('Mailer Error: ' . $mail->ErrorInfo);
        }
    }
    public function sendEmailWithMailGun(Email $email, $config=NULL){	
	# Instantiate the client.
	$mgClient = new Mailgun('key-cd4ec1bf0f2472c86c2954ec8a356a33');
	$domain = "mg.aldoapp.com";
	$to = sprintf('%s <%s>',  $email->fromName, $email->fromEmail);
	# Make the call to the client.
	$result = $mgClient->sendMessage("$domain",
		  array('from'    => 'admin <admin@mg.aldoapp.com>',
			'to'      => $to,
			'subject' => $email->subject,
			'text'    => $email->plainTextMessage,
			'html'    => $email->htmlMessage,
		      ));

	# You can see a record of this email in your logs: https://mailgun.com/app/logs .

	# You can send up to 300 emails/day from this sandbox server.
	# Next, you should add your own domain so you can send 10,000 emails/month for free.
	return TRUE;
    }
    public function sendEmailWithSendGrid(Email $email){	
	
    }
}
