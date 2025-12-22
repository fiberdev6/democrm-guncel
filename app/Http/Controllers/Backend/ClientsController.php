<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Models\Clients;
use Image;

class ClientsController extends Controller
{
    public function AllClient()
    {
        $clients = Clients::latest()->get();
        return view('backend.clients.clients_all', compact('clients'));

    }

    public function AddClient()
    {
        return view('backend.clients.clients_add');
    }

    public function StoreClient(Request $request)
    {   if($request->file('image')){
            $image = $request->file('image');
            
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();

            Image::make($image)->save('upload/clients/' . $name_gen);
            $save_url = 'upload/clients/' . $name_gen;

            Clients::insert([
                'name' => $request->name,
                'job' => $request->job,
                'message' => $request->message,
                'image' => $save_url,
            ]);

            $notification = array(
                'message' => 'Müşteri Yorumu Başarıyla Eklendi',
                'alert-type' => 'success'
            );

            return redirect()->route('all.client')->with($notification);
        }
        else {
            Clients::insert([
                'name' => $request->name,
                'job' => $request->job,
                'message' => $request->message,
            ]);

            $notification = array(
                'message' => 'Müşteri Yorumu Başarıyla Eklendi',
                'alert-type' => 'success'
            );

            return redirect()->route('all.client')->with($notification);
        }
    }

    public function EditClient($id)
    {
        $client_id = Clients::findOrFail($id);
        return view('backend.clients.edit_clients', compact('client_id'));
    }

    public function UpdateClient(Request $request)
    {
        $client_id = $request->id;

        if ($request->file('image')) {
            $image = $request->file('image');
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();

            Image::make($image)->save('upload/clients/' . $name_gen);
            $save_url = 'upload/clients/' . $name_gen;

            Clients::findOrFail($client_id)->update([
                'name' => $request->name,
                'job' => $request->job,
                'message' => $request->message,
                'image' => $save_url,
            ]);

            $notification = array(
                'message' => 'Müşteri Yorumu Başarıyla Güncellendi',
                'alert-type' => 'success'
            );

            return redirect()->route('all.client')->with($notification);
        } else {
            Clients::findOrFail($client_id)->update([
                'name' => $request->name,
                'job' => $request->job,
                'message' => $request->message,
            ]);

            $notification = array(
                'message' => 'Müşteri Yorumu Başarıyla Güncellendi',
                'alert-type' => 'success'
            );

            return redirect()->route('all.client')->with($notification);
        }
    }

    public function DeleteClient($id)
    {
        $client_id = Clients::findOrFail($id);
        $img = $client_id->image;
        unlink($img);

        Clients::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Müşteri Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->route('all.client')->with($notification);
    }

    public function Testimonial()
    {
        $testi = Clients::get();
        return view('frontend.testimonial', compact('testi'));
    }



}