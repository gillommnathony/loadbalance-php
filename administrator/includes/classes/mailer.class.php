<?php
class mailer
{
    private $error = [];
    private $mail = null;
    private $host;
    private $email;
    private $pwd;
    private $tls;
    private $sender;
    private $recipient_email;
    private $recipient_name;

    function __construct()
    {
        //Server settings
        $this->host = get_option('smtp_host');
        $this->email = get_option('smtp_email');
        $this->pwd = get_option('smtp_password');
        $this->tls = get_option('smtp_tls');
        $this->sender = get_option('smtp_sender');
        $this->recipient_email = get_option('smtp_reply_email');
        $this->recipient_name = get_option('smtp_reply_name');

        $this->mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        $this->mail->isSMTP();
        $this->mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_OFF;
        $this->mail->SMTPAuth   = true;
        if (filter_var($this->tls, FILTER_VALIDATE_BOOLEAN)) {
            $this->mail->SMTPSecure = 'tls';
            $this->mail->Port       = 587;
        } else {
            $this->mail->SMTPSecure = 'ssl';
            $this->mail->Port       = 465;
        }
        $this->mail->Host       = $this->host;
        $this->mail->Username   = $this->email;
        $this->mail->Password   = $this->pwd;
    }

    /*
    example:
    $mail = new mailer();
    $data = [
        'sendto' => [
            'email' => 'name@mail.com',
            'name' => 'John Doe'
        ],
        'subject'       => 'Thank you',
        'message'       => '<p>Hi John Doe, ...</p>',
        'message_plain' => 'Hi, John Doe, ...'
    ];
    $send = $mail->send($data);
    */
    function send($data = [])
    {
        try {
            //Sender
            $this->mail->setFrom($this->email, $this->sender);

            //Recipients
            if (!empty($this->recipient_email) && !empty($this->recipient_name)) {
                $this->mail->addReplyTo($this->recipient_email, $this->recipient_name);
            } else {
                $this->mail->addReplyTo($this->email, $this->sender);
            }

            if (!empty($data['sendto'])) {
                $sendto = $data['sendto'];
                if (!empty($sendto['email'])) {
                    $this->mail->addAddress($sendto['email'], $sendto['name']);
                }

                // Content
                $this->mail->isHTML(true);
                $this->mail->Subject = htmlspecialchars($data['subject']);

                if (!empty($data['message'])) {
                    $this->mail->Body = $data['message'];
                }
                // plain text message
                if (!empty($data['message_plaintext'])) {
                    $this->mail->AltBody = strip_tags($data['message_plaintext']);
                }
            }

            $this->clear_errors();
            // send
            if (!$this->mail->send()) {
                $this->set_error($this->mail->ErrorInfo);
            } else {
                return TRUE;
            }
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            $this->set_error($e->errorMessage());
        } catch (Exception $e) {
            $this->set_error($e->getMessage());
        }
        return FALSE;
    }

    private function set_error($msg = '')
    {
        if (!empty($msg)) {
            $this->error[] = $msg;
        }
    }

    private function clear_errors()
    {
        $this->error = [];
    }

    function get_errors()
    {
        return $this->error;
    }
}
