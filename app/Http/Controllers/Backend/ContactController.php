<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ContactController extends Controller
{   public function ContactMessage(){
        $contacts = Contact::latest()->get();
        return view('backend.contact.all_contact',compact('contacts'));
    }

    public function DeleteMessage($id){
        Contact::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Mesajınız Başarıyla Silindi',
            'alert-type' => 'success',
        );

        return redirect()->back()->with($notification);
    }

    
}
