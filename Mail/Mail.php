<?php if (!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Mail Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Mail
 * @author	B.Och-Erdene <ocherdene@veritech.mn>
 * @link	http://www.interactive.mn/PHPframework/Mail
 */

class Mail {

    public function __construct() {
        parent::__construct();
    }

    public function sendPhpMailer($args) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        
        $subject  = $args['subject'];
        $altBody  = $args['altBody'];
        $body     = $args['body'];
        $toMail   = $args['toMail'];
        
        $fromMail = EMAIL_FROM;
        $fromName = EMAIL_FROM_NAME;
        
        includeLib('Mail/PHPMailer/v2/PHPMailerAutoload');
        
        $mail = new PHPMailer();
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        
        if (!defined('SMTP_USER')) {
                
            $mail->SMTPAuth = false;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

        } else {
            $mail->SMTPAuth = (defined('SMTP_AUTH') ? SMTP_AUTH : true);
            
            if ($mail->SMTPAuth) {
                $mail->Username = SMTP_USER; 
                $mail->Password = SMTP_PASS; 
            } else {
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
            }
        }
        
        if (defined('SMTP_SSL_VERIFY') && !SMTP_SSL_VERIFY) {
            
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
        }
        
        $mail->SMTPSecure = (defined('SMTP_SECURE') ? SMTP_SECURE : false);
        $mail->Host = SMTP_HOST;
        $mail->Port = SMTP_PORT;
        if (defined('SMTP_HOSTNAME') && SMTP_HOSTNAME) {
            $mail->Hostname = SMTP_HOSTNAME;
        }
        $mail->setFrom($fromMail, $fromName); 
        $mail->AddReplyTo($fromMail, $fromName);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $body;
        $mail->AltBody = $altBody;
        
        if (is_array($toMail)) {
            
            foreach ($toMail as $mailAddress) {
                $mail->addAddress($mailAddress);
            }
            
        } else {
            $mail->addAddress($toMail);
        }

        if ($mail->send()) {
            $response = array('status' => 'success', 'message' => 'Амжилттай илгээгдлээ');
        } else {
            $response = array('status' => 'error', 'message' => $mail->ErrorInfo);
        }

        return $response;
    }
    
}
