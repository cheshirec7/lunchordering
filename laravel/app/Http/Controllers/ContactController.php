<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use Validator;

class ContactController extends Controller
{
    public function getContact()
    {
        return view('contact');
    }

    public function postContact(Request $request)
    {
        $inputs = $request->only('name', 'email', 'message', 'sendcopy', 'g-recaptcha-response');
        foreach ($inputs as $key => &$value) {
            if ($key != 'g-recaptcha-response') {
                $value = trim($value);
                $value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_NO_ENCODE_QUOTES);
            }
        }
        $inputs['email'] = filter_var($inputs['email'], FILTER_SANITIZE_EMAIL);

        $validator = Validator::make($inputs, [
            'name' => 'required|min:5',
            'email' => 'required|email',
            'message' => 'required|min:10'//,
            //'g-recaptcha-response' => 'recaptcha'
        ]);

        if ($validator->fails()) {

            return redirect()
                ->back()
                ->withInput()
                ->withErrors($validator->errors());

        } else {

            Mail::send('emails.contact', ['inputs' => $inputs], function ($m) use ($inputs) {
                $m->to('erictotten@cox.net', 'Eric Totten')->subject('CCA Lunch Ordering - Contact Us');
            });

            if ($inputs['sendcopy']) {
                Mail::send('emails.contact', ['inputs' => $inputs], function ($m) use ($inputs) {
                    $m->to($inputs['email'], $inputs['name'])->subject('CCA Lunch Ordering - Contact Us');
                });
            }

            return redirect()
                ->back()
                ->with('success', 'Your message was sent successfully. We will get back to you shortly.');

        }
    }
}


//        $message->from($address, $name = null);
//        $message->sender($address, $name = null);
//        $message->to($address, $name = null);
//        $message->cc($address, $name = null);
//        $message->bcc($address, $name = null);
//        $message->replyTo($address, $name = null);
//        $message->subject($subject);
//        $message->priority($level);
//        $message->attach($pathToFile, array $options = []);
//        $message->attachData($data, $name, array $options = []);
//        $message->getSwiftMessage();

//if ($inputs['sendcopy']) {
//Mail::send('emails.volunteer', ['inputs' => $inputs], function ($m) use ($inputs) {
//    $m->to('erictotten@oox.net', 'Eric Totten')->subject('Tevis Volunteer Signup');
//    if ($inputs['sendcopy'])
//        $m->cc($inputs['email'], $inputs['name'])->subject('Tevis Volunteer Signup');
//});
//} else {
//  Mail::send('emails.volunteer', ['inputs' => $inputs], function ($m) use ($inputs) {
//    $m->to('erictotten@oox.net', 'Eric Totten')->subject('Tevis Volunteer Signup');
//});
// }