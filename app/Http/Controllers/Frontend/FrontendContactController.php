<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings;
use App\Models\PageContact;
use App\Models\Contact;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class FrontendContactController extends Controller
{
    public function index() {
        $settings = Settings::find(1);
        $page_contact = PageContact::find(1);
        return view('frontend.pages.contact', compact('settings', 'page_contact'));
    }

    public function StoreMessage(Request $request) {
        require base_path('vendor/autoload.php');
        $settings = Settings::find(1);
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $settings->mail_server;                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $settings->email;                     //SMTP username
            $mail->Password   = $settings->mail_sifre;                               //SMTP password
            $mail->SMTPSecure = $settings->protokol;            //Enable implicit TLS encryption
            $mail->Port       = $settings->mail_port;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($settings->email, $settings->site_name);
            $mail->addAddress($settings->email);     //Add a recipient
            

            //Content

            $name = $request->name;
            $email = $request->email;
            $message = $request->message;
            $phone = $request->phone;

            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Web Site Mail';
            $mail->Body    = "Adı : ". $name."<br> ". "Mail Adresi :  ". $email."<br> "."Mesaj :  ". $message."<br> ". "Telefon :  ". $phone ;

            if( !$mail->send() ) {
                return back()->with("failed", "Email gönderilemedi.")->withErrors($mail->ErrorInfo);
            }
            
            else {
                Contact::insert([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'message' => $request->message
                ]);
                return back()->with("success", "Email başarıyla gönderildi.");
            }
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
        

       
    }
}
